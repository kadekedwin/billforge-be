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
        'qrcode_data',
        'footer_message',
        'include_image',
        'transaction_prefix',
        'transaction_next_number',
        'receipt_style_id',
        'printer_font',
        'line_character',
        'item_layout',
        'label_receipt_id',
        'label_receipt_id_enabled',
        'label_transaction_id',
        'label_transaction_id_enabled',
        'label_date',
        'label_date_enabled',
        'label_time',
        'label_time_enabled',
        'label_cashier',
        'label_cashier_enabled',
        'label_customer',
        'label_customer_enabled',
        'label_items',
        'label_items_enabled',
        'label_subtotal',
        'label_subtotal_enabled',
        'label_discount',
        'label_discount_enabled',
        'label_tax',
        'label_tax_enabled',
        'label_total',
        'label_total_enabled',
        'label_payment_method',
        'label_payment_method_enabled',
        'label_amount_paid',
        'label_amount_paid_enabled',
        'label_change',
        'label_change_enabled',
    ];

    protected $casts = [
        'include_image' => 'boolean',
        'receipt_style_id' => 'integer',
        'transaction_next_number' => 'integer',
        'label_receipt_id_enabled' => 'boolean',
        'label_transaction_id_enabled' => 'boolean',
        'label_date_enabled' => 'boolean',
        'label_time_enabled' => 'boolean',
        'label_cashier_enabled' => 'boolean',
        'label_customer_enabled' => 'boolean',
        'label_items_enabled' => 'boolean',
        'label_subtotal_enabled' => 'boolean',
        'label_discount_enabled' => 'boolean',
        'label_tax_enabled' => 'boolean',
        'label_total_enabled' => 'boolean',
        'label_payment_method_enabled' => 'boolean',
        'label_amount_paid_enabled' => 'boolean',
        'label_change_enabled' => 'boolean',
        'item_layout' => 'integer',
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
