<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasUuids;

    protected $table = 'transaction';

    protected $fillable = [
        'uuid',
        'business_uuid',
        'payment_method_uuid',
        'customer_uuid',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'final_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_uuid', 'uuid');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'transaction_uuid', 'uuid');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_uuid', 'uuid');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_uuid', 'uuid');
    }
}
