<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'branch_id', 'terminal_id', 'user_id',
        'shift_number', 'status', 'opening_cash', 'closing_cash',
        'expected_cash', 'difference', 'opened_at', 'closed_at', 'closing_note',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public static function generateShiftNumber(): string
    {
        return 'SHF-' . now()->format('Ymd') . '-' . str_pad(
            static::whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );
    }

    public function getTotalSalesAttribute(): float
    {
        return $this->orders()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    public function getTotalOrdersCountAttribute(): int
    {
        return $this->orders()
            ->where('status', 'completed')
            ->count();
    }
}
