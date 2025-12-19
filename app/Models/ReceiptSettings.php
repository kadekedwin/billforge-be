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
        'qrcode_data',
        'footer_message',
        'include_image',
        'transaction_prefix',
        'transaction_next_number',
        'label_receipt_id',
        'label_transaction_id',
        'label_date',
        'label_time',
        'label_cashier',
        'label_customer',
        'label_items',
        'label_subtotal',
        'label_discount',
        'label_tax',
        'label_total',
        'label_payment_method',
        'label_amount_paid',
        'label_change',
    ];

    protected $casts = [
        'include_image' => 'boolean',
        'image_template_id' => 'integer',
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
