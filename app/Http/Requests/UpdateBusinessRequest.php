<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cert_prefix')) {
            $this->merge([
                'cert_prefix' => $this->cert_prefix ? strtoupper(trim($this->cert_prefix)) : null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'is_publicly_visible' => ['required', 'boolean'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
            'address' => ['nullable', 'string', 'max:255'],
            'cert_prefix' => [
                'nullable',
                'string',
                'min:2',
                'max:16',
                'regex:/^[a-zA-Z0-9-]+$/',
                Rule::unique('businesses', 'cert_prefix')
                    ->ignore($this->user()->businesses()->first()?->id),
            ],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }


public function messages(): array
{
    return [
        'cert_prefix.regex' => 'Prefix can only contain letters, numbers, and hyphens — no slashes or other symbols.',
    ];
}
}
