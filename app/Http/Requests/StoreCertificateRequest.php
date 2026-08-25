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
        ];
    }
}
