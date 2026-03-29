<?php

namespace App\Http\Requests;

/**
 * M6: Validates the forgot-password endpoint.
 */
class ForgotPasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'البريد الإلكتروني غير صالح.',
        ];
    }
}
