<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->businesses()->firstOrFail()->id;

        return [
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('business_id', $businessId),
            ],
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
        // Exactly one of the two template fields must be set
        $validator->after(function (Validator $validator) {
            $hasBuiltin = (bool) $this->input('builtin_template_key');
            $hasCustom = (bool) $this->input('certificate_template_id');

            if ($hasBuiltin === $hasCustom) {
                $validator->errors()->add('template', 'Choose exactly one template — built-in or custom.');
            }
        });
    }
}
