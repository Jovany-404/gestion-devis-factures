<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit',
        'unit_price',
        'tax_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function documentLines(): HasMany
    {
        return $this->hasMany(DocumentLine::class);
    }
}
