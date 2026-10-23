<?php

namespace App\Infrastructure\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($this->route('client')),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'store_id' => ['nullable', 'integer', Rule::exists('stores', 'id')],
        ];
    }
}
