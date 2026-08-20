<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'is_publicly_visible' => ['required', 'boolean'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ];
    }
}
