<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    public const TYPE_QUOTE = 'quote';

    public const TYPE_INVOICE = 'invoice';

    protected $fillable = [
        'type',
        'number',
        'status',
        'client_id',
        'created_by',
        'quote_id',
        'issue_date',
        'due_date',
        'valid_until',
        'currency',
        'client_snapshot',
        'company_snapshot',
        'notes',
        'terms',
        'subtotal',
        'tax_total',
        'total',
        'sent_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'valid_until' => 'date',
            'client_snapshot' => 'array',
            'company_snapshot' => 'array',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class)->orderBy('id');
    }

    public function sourceQuote(): BelongsTo
    {
        return $this->belongsTo(self::class, 'quote_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(self::class, 'quote_id');
    }

    public function pdfPath(): string
    {
        return "documents/{$this->id}/{$this->number}.pdf";
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function displayStatus(): string
    {
        if ($this->type === self::TYPE_INVOICE
            && $this->status === 'sent'
            && $this->due_date?->lt(today())) {
            return 'overdue';
        }

        return $this->status;
    }

    public function statusLabel(): string
    {
        return match ($this->displayStatus()) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'accepted' => 'Accepté',
            'rejected' => 'Refusé',
            'paid' => 'Payé',
            'overdue' => 'En retard',
            default => 'Annulé',
        };
    }
}
