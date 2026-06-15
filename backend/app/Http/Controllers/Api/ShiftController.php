<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\CashTransaction;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function openShift(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $user = $request->user();

        // Check if there's already an open shift for this terminal
        $existingShift = Shift::where('terminal_id', $request->terminal_id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'message' => 'Bu terminal uchun smena allaqachon ochiq.',
                'shift' => $existingShift,
            ], 422);
        }

        $shift = Shift::create([
            'organization_id' => $user->organization_id,
            'branch_id' => $request->branch_id,
            'terminal_id' => $request->terminal_id,
            'user_id' => $user->id,
            'shift_number' => Shift::generateShiftNumber(),
            'status' => 'open',
            'opening_cash' => $request->opening_cash,
            'opened_at' => now(),
        ]);

        return response()->json([
            'message' => 'Smena muvaffaqiyatli ochildi.',
            'shift' => $shift->load('user', 'terminal', 'branch'),
        ], 201);
    }

    public function closeShift(Request $request, Shift $shift)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'closing_note' => 'nullable|string',
        ]);

        if (!$shift->isOpen()) {
            return response()->json(['message' => 'Smena allaqachon yopilgan.'], 422);
        }

        // Calculate expected cash
        $cashSales = $shift->orders()
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $cashIn = $shift->cashTransactions()->where('type', 'in')->sum('amount');
        $cashOut = $shift->cashTransactions()->where('type', 'out')->sum('amount');

        $expectedCash = $shift->opening_cash + $cashSales + $cashIn - $cashOut;
        $difference = $request->closing_cash - $expectedCash;

        $shift->update([
            'status' => 'closed',
            'closing_cash' => $request->closing_cash,
            'expected_cash' => $expectedCash,
            'difference' => $difference,
            'closed_at' => now(),
            'closing_note' => $request->closing_note,
        ]);

        return response()->json([
            'message' => 'Smena muvaffaqiyatli yopildi.',
            'shift' => $shift->load('user', 'terminal', 'branch'),
        ]);
    }

    public function currentShift(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|integer',
        ]);

        $shift = Shift::where('terminal_id', $request->terminal_id)
            ->where('status', 'open')
            ->with(['user', 'terminal', 'branch', 'orders'])
            ->latest()
            ->first();

        if (!$shift) {
            return response()->json(['shift' => null]);
        }

        // Get shift statistics
        $stats = [
            'total_orders' => $shift->orders()->where('status', 'completed')->count(),
            'total_sales' => $shift->orders()->where('status', 'completed')->sum('total_amount'),
            'cash_sales' => $shift->orders()->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $shift->orders()->where('status', 'completed')->where('payment_method', 'card')->sum('total_amount'),
            'click_sales' => $shift->orders()->where('status', 'completed')->where('payment_method', 'click')->sum('total_amount'),
            'payme_sales' => $shift->orders()->where('status', 'completed')->where('payment_method', 'payme')->sum('total_amount'),
            'humo_sales' => $shift->orders()->where('status', 'completed')->where('payment_method', 'humo')->sum('total_amount'),
            'uzcard_sales' => $shift->orders()->where('status', 'completed')->where('payment_method', 'uzcard')->sum('total_amount'),
            'returns' => $shift->orders()->where('status', 'returned')->count(),
        ];

        return response()->json([
            'shift' => $shift,
            'stats' => $stats,
        ]);
    }

    public function cashIn(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $shift = Shift::where('id', $request->shift_id)
            ->where('status', 'open')
            ->firstOrFail();

        $transaction = CashTransaction::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $shift->branch_id,
            'shift_id' => $shift->id,
            'user_id' => $request->user()->id,
            'type' => 'in',
            'amount' => $request->amount,
            'reason' => $request->reason,
            'note' => $request->note,
        ]);

        return response()->json(['message' => 'Kirim amalga oshirildi.', 'transaction' => $transaction], 201);
    }

    public function cashOut(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $shift = Shift::where('id', $request->shift_id)
            ->where('status', 'open')
            ->firstOrFail();

        $transaction = CashTransaction::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $shift->branch_id,
            'shift_id' => $shift->id,
            'user_id' => $request->user()->id,
            'type' => 'out',
            'amount' => $request->amount,
            'reason' => $request->reason,
            'note' => $request->note,
        ]);

        return response()->json(['message' => 'Chiqim amalga oshirildi.', 'transaction' => $transaction], 201);
    }

    public function shiftReport(Request $request, Shift $shift)
    {
        $shift->load('user', 'terminal', 'branch.organization', 'orders.items');

        $stats = [
            'total_orders' => $shift->orders()->where('status', 'completed')->count(),
            'total_returns' => $shift->orders()->where('status', 'returned')->count(),
            'total_sales' => $shift->orders()->where('status', 'completed')->sum('total_amount'),
            'total_discount' => $shift->orders()->where('status', 'completed')->sum('discount_amount'),
            'by_payment' => [
                'cash' => $shift->orders()->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount'),
                'card' => $shift->orders()->where('status', 'completed')->where('payment_method', 'card')->sum('total_amount'),
                'click' => $shift->orders()->where('status', 'completed')->where('payment_method', 'click')->sum('total_amount'),
                'payme' => $shift->orders()->where('status', 'completed')->where('payment_method', 'payme')->sum('total_amount'),
                'humo' => $shift->orders()->where('status', 'completed')->where('payment_method', 'humo')->sum('total_amount'),
                'uzcard' => $shift->orders()->where('status', 'completed')->where('payment_method', 'uzcard')->sum('total_amount'),
                'debt' => $shift->orders()->where('status', 'completed')->where('payment_method', 'debt')->sum('total_amount'),
            ],
            'cash_in' => $shift->cashTransactions()->where('type', 'in')->sum('amount'),
            'cash_out' => $shift->cashTransactions()->where('type', 'out')->sum('amount'),
        ];

        return response()->json(['shift' => $shift, 'stats' => $stats]);
    }
}
