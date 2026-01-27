<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_slug',
        'price',
        'quantity',
        'subtotal',
        'attributes',
    ];

    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
        'subtotal' => 'integer',
        'attributes' => 'array',
    ];

    // Relationship to order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relationship to product (for admin reference only)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
