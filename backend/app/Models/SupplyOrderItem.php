<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_order_id', 'product_id', 'quantity',
        'purchase_price', 'selling_price', 'total_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplyOrder() { return $this->belongsTo(SupplyOrder::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
