<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'branch_id', 'terminal_id', 'shift_id', 'user_id', 'customer_id',
        'receipt_number', 'receipt_sequence', 'status',
        'subtotal', 'discount_amount', 'vat_amount', 'total_amount',
        'paid_amount', 'change_amount', 'payment_method', 'payment_details',
        'note', 'is_printed', 'returned_order_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_details' => 'array',
        'is_printed' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function terminal()
    {
        return $this->belongsTo(Terminal::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function returnedOrder()
    {
        return $this->belongsTo(Order::class, 'returned_order_id');
    }

    // Generate unique receipt number
    public static function generateReceiptNumber(int $organizationId, int $branchId): string
    {
        $prefix = 'CHK';
        $date = now()->format('Ymd');
        $sequence = static::where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('%s-%s-%05d', $prefix, $date, $sequence);
    }
}
