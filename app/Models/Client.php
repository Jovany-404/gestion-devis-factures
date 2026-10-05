<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'postal_code',
        'city',
        'notes',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
