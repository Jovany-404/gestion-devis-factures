<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'number' => $this->number,
            'status' => $this->status,
            'client' => $this->client_snapshot,
            'issue_date' => $this->issue_date->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'valid_until' => $this->valid_until?->toDateString(),
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line) => [
                'description' => $line->description,
                'unit' => $line->unit,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'tax_rate' => $line->tax_rate,
                'subtotal' => $line->subtotal,
                'tax_amount' => $line->tax_amount,
                'total' => $line->total,
            ])),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
