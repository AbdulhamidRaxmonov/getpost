<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'category_id', 'unit_id', 'sku', 'barcode',
        'name', 'description', 'image', 'purchase_price', 'selling_price',
        'min_price', 'vat_percent', 'is_active', 'track_stock',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStockForBranch(int $branchId): float
    {
        $stock = $this->stocks()->where('branch_id', $branchId)->first();
        return $stock ? $stock->quantity : 0;
    }
}
