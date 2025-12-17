<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptSettings extends Model
{
    use HasUuids;

    protected $table = 'receipt_settings';

    protected $fillable = [
        'uuid',
        'business_uuid',
        'image_template_id',
        'print_template_id',
        'qrcode_data',
        'footer_message',
        'include_image',
        'transaction_prefix',
        'transaction_next_number',
    ];

    protected $casts = [
        'include_image' => 'boolean',
        'image_template_id' => 'integer',
        'print_template_id' => 'integer',
        'transaction_next_number' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_uuid', 'uuid');
    }
}
