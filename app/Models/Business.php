<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasUuids;

    protected $table = 'business';

    protected $fillable = [
        'uuid',
        'user_uuid',
        'name',
        'address',
        'phone',
        'image_size_bytes',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'business_uuid', 'uuid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'business_uuid', 'uuid');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(ItemTax::class, 'business_uuid', 'uuid');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ItemDiscount::class, 'business_uuid', 'uuid');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'business_uuid', 'uuid');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'business_uuid', 'uuid');
    }
}
