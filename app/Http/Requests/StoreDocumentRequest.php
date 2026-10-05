<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageDocuments();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['issue_date' => $this->input('issue_date', today()->toDateString())]);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'lines' => ['required', 'array', 'min:1', 'max:50'],
            'lines.*.article_id' => [
                'required',
                'integer',
                Rule::exists('articles', 'id')->where('is_active', true),
            ],
            'lines.*.quantity' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:999999'],
        ];
    }
}
