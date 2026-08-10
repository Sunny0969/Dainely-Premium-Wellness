<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'status', 'locale', 'currency', 'exchange_rate',
        'subtotal_usd', 'discount_amount_usd', 'shipping_usd', 'tax_usd', 'total_usd',
        'customer_email', 'customer_first_name', 'customer_last_name', 'customer_phone',
        'shipping_address1', 'shipping_address2', 'shipping_city', 'shipping_state',
        'shipping_zip', 'shipping_country',
        'square_payment_id', 'shopify_order_id', 'shopify_order_number',
        'discount_code', 'meta',
        'gdpr_consent', 'gdpr_consented_at',
    ];

    protected $casts = [
        'meta'               => 'array',
        'gdpr_consent'       => 'boolean',
        'gdpr_consented_at'  => 'datetime',
        'subtotal_usd'       => 'decimal:2',
        'discount_amount_usd'=> 'decimal:2',
        'shipping_usd'       => 'decimal:2',
        'tax_usd'            => 'decimal:2',
        'total_usd'          => 'decimal:2',
        'exchange_rate'      => 'decimal:6',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate the next sequential order number.
     */
    public static function generateOrderNumber(): string
    {
        $year  = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'DN-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public function getCustomerFullNameAttribute(): string
    {
        return trim($this->customer_first_name . ' ' . $this->customer_last_name);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'fulfilled']);
    }
}
