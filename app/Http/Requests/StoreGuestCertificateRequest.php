<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGuestCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->businesses()->firstOrFail()->id;

        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'programme_id' => [
                'required',
                Rule::exists('programmes', 'id')->where('business_id', $businessId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'builtin_template_key' => ['nullable', 'string'],
            'certificate_template_id' => [
                'nullable',
                Rule::exists('certificate_templates', 'id')
                    ->where('business_id', $businessId)
                    ->where('status', 'active'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasBuiltin = (bool) $this->input('builtin_template_key');
            $hasCustom = (bool) $this->input('certificate_template_id');

            if ($hasBuiltin === $hasCustom) {
                $validator->errors()->add('template', 'Choose exactly one template — built-in or custom.');
            }
        });
    }
}
