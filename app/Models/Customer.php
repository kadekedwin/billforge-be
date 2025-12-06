<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasUuids;

    protected $table = 'customer';

    protected $fillable = [
        'uuid',
        'user_uuid',
        'business_uuid',
        'name',
        'email',
        'address',
        'phone',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_uuid', 'uuid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_uuid', 'uuid');
    }
}
