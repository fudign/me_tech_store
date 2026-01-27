<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'payment_method',
        'status',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'total' => 'integer',
    ];

    // Relationship to order items
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Status constants
    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DELIVERING = 'delivering';
    public const STATUS_COMPLETED = 'completed';

    // Status labels for admin UI (Russian per CONTEXT)
    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_PROCESSING => 'В обработке',
            self::STATUS_DELIVERING => 'Доставляется',
            self::STATUS_COMPLETED => 'Выполнен',
        ];
    }

    // Format price for display (cents to сом)
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total / 100, 0, ',', ' ') . ' сом';
    }

    // Scope for admin order list
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
