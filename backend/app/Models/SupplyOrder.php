<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'branch_id', 'supplier_id', 'user_id',
        'invoice_number', 'status', 'total_amount', 'paid_amount',
        'supply_date', 'note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'supply_date' => 'date',
    ];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(SupplyOrderItem::class); }
}
