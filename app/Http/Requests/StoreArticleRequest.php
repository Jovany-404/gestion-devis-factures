<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageDocuments();
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'sku' => ['required', 'string', 'max:40', Rule::unique('articles', 'sku')->ignore($article)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'unit' => ['required', 'string', 'max:30'],
            'unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999'],
            'tax_rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
