<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'customer', 'items.product'])
            ->where('organization_id', $request->user()->organization_id);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $shift = Shift::where('id', $request->shift_id)
            ->where('status', 'open')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $discountTotal = 0;
            $vatTotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('organization_id', $user->organization_id)
                    ->firstOrFail();

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discountPercent = $item['discount_percent'] ?? 0;

                $lineTotal = $quantity * $unitPrice;
                $discountAmount = $lineTotal * ($discountPercent / 100);
                $lineAfterDiscount = $lineTotal - $discountAmount;
                $vatAmount = $lineAfterDiscount * ($product->vat_percent / 100);
                $totalAmount = $lineAfterDiscount + $vatAmount;

                $subtotal += $lineTotal;
                $discountTotal += $discountAmount;
                $vatTotal += $vatAmount;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'vat_percent' => $product->vat_percent,
                    'vat_amount' => $vatAmount,
                    'total_amount' => $totalAmount,
                ];

                // Update stock
                if ($product->track_stock) {
                    Stock::where('product_id', $product->id)
                        ->where('branch_id', $shift->branch_id)
                        ->decrement('quantity', $quantity);
                }
            }

            $totalAmount = $subtotal - $discountTotal + $vatTotal;
            $paidAmount = $request->paid_amount;
            $changeAmount = max(0, $paidAmount - $totalAmount);

            // Get next receipt sequence
            $receiptSequence = Order::where('shift_id', $shift->id)->count() + 1;
            $receiptNumber = Order::generateReceiptNumber($user->organization_id, $shift->branch_id);

            $order = Order::create([
                'organization_id' => $user->organization_id,
                'branch_id' => $shift->branch_id,
                'terminal_id' => $request->terminal_id,
                'shift_id' => $shift->id,
                'user_id' => $user->id,
                'customer_id' => $request->customer_id,
                'receipt_number' => $receiptNumber,
                'receipt_sequence' => $receiptSequence,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => $discountTotal,
                'vat_amount' => $vatTotal,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'payment_details' => $request->payment_details,
                'note' => $request->note,
            ]);

            // Create order items
            foreach ($itemsData as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product']->id,
                    'product_name' => $itemData['product']->name,
                    'product_sku' => $itemData['product']->sku,
                    'product_barcode' => $itemData['product']->barcode,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_percent' => $itemData['discount_percent'],
                    'discount_amount' => $itemData['discount_amount'],
                    'vat_percent' => $itemData['vat_percent'],
                    'vat_amount' => $itemData['vat_amount'],
                    'total_amount' => $itemData['total_amount'],
                ]);
            }

            DB::commit();

            return response()->json($order->load('items.product', 'user', 'customer'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Xatolik: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, Order $order)
    {
        if ($order->organization_id !== $request->user()->organization_id) {
            abort(403);
        }
        return response()->json($order->load('items.product', 'user', 'customer', 'terminal', 'branch'));
    }

    public function returnOrder(Request $request, Order $order)
    {
        if ($order->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Bu chekni qaytarib bo\'lmaydi.'], 422);
        }

        DB::beginTransaction();
        try {
            // Return stock
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product && $product->track_stock) {
                    Stock::where('product_id', $product->id)
                        ->where('branch_id', $order->branch_id)
                        ->increment('quantity', $item->quantity);
                }
            }

            $order->update(['status' => 'returned']);

            DB::commit();

            return response()->json(['message' => 'Chek qaytarildi.', 'order' => $order]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Xatolik: ' . $e->getMessage()], 500);
        }
    }

    public function receipt(Request $request, Order $order)
    {
        if ($order->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $order->load('items.product', 'user', 'customer', 'terminal', 'branch.organization');
        $order->update(['is_printed' => true]);

        return response()->json($order);
    }
}
