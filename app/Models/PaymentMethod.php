<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasUuids;

    protected $table = 'payment_method';

    protected $fillable = [
        'uuid',
        'user_uuid',
        'business_uuid',
        'name',
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
        return $this->hasMany(Transaction::class, 'payment_uuid', 'uuid');
    }
}
