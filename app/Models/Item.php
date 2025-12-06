<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasUuids;

    protected $table = 'item';

    protected $fillable = [
        'uuid',
        'user_uuid',
        'business_uuid',
        'discount_uuid',
        'tax_uuid',
        'name',
        'sku',
        'description',
        'base_price',
        'image_size_bytes',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_uuid', 'uuid');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(ItemTax::class, 'tax_uuid', 'uuid');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(ItemDiscount::class, 'discount_uuid', 'uuid');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'item_uuid', 'uuid');
    }
}
