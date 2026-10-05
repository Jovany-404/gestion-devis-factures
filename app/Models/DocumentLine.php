<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLine extends Model
{
    protected $fillable = [
        'article_id',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'tax_rate',
        'subtotal',
        'tax_amount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
