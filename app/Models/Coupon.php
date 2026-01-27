<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENTAGE = 'percentage';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'integer',
        'min_order_amount' => 'integer',
        'max_discount_amount' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if coupon is valid
     */
    public function isValid(): bool
    {
        // Check if active
        if (!$this->is_active) {
            return false;
        }

        // Check if started
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        // Check if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon can be applied to order
     */
    public function canApplyToOrder(int $orderTotal): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check minimum order amount
        if ($orderTotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for order total
     */
    public function calculateDiscount(int $orderTotal): int
    {
        if (!$this->canApplyToOrder($orderTotal)) {
            return 0;
        }

        if ($this->type === self::TYPE_FIXED) {
            // Fixed amount discount
            return min($this->value, $orderTotal);
        }

        if ($this->type === self::TYPE_PERCENTAGE) {
            // Percentage discount
            $discount = (int) ($orderTotal * ($this->value / 100));

            // Apply max discount limit if set
            if ($this->max_discount_amount) {
                $discount = min($discount, $this->max_discount_amount);
            }

            return $discount;
        }

        return 0;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Get formatted discount value
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->type === self::TYPE_FIXED) {
            return number_format($this->value / 100, 0, ',', ' ') . ' сом';
        }

        return $this->value . '%';
    }
}
