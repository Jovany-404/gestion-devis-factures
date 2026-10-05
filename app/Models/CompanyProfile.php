<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'postal_code',
        'city',
        'country',
        'registration_number',
        'vat_number',
        'iban',
        'default_tax_rate',
        'quote_validity_days',
        'invoice_due_days',
    ];

    protected function casts(): array
    {
        return ['default_tax_rate' => 'decimal:2'];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            ['name' => config('app.name', 'Mon entreprise')]
        );
    }

    public function isReadyForDocuments(): bool
    {
        return filled($this->name)
            && filled($this->email)
            && filled($this->address)
            && filled($this->postal_code)
            && filled($this->city);
    }
}
