<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DiscountCode extends Model
{
    protected $fillable = [
        'code','type','value','usage_limit','usage_count',
        'is_active','minimum_order_usd','expires_at',
    ];
    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_usd' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];
    public function isValid(float $subtotal = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        if ($this->minimum_order_usd && $subtotal < $this->minimum_order_usd) return false;
        return true;
    }
    public function calculateDiscount(float $subtotal): float
    {
        return match($this->type) {
            'percentage'    => round($subtotal * ($this->value / 100), 2),
            'fixed'         => min((float)$this->value, $subtotal),
            'free_shipping' => 0,
            default         => 0,
        };
    }
}
