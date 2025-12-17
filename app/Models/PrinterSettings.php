<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrinterSettings extends Model
{
    use HasUuids;

    protected $table = 'printer_settings';

    protected $fillable = [
        'uuid',
        'business_uuid',
        'paper_width_mm',
        'chars_per_line',
        'encoding',
        'feed_lines',
        'cut_enabled',
    ];

    protected $casts = [
        'cut_enabled' => 'boolean',
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
