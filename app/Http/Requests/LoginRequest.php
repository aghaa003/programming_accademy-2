<?php

namespace App\Http\Requests;

/**
 * H4: Validates the login endpoint inputs.
 */
class LoginRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'الرجاء إدخال اسم المستخدم أو البريد الإلكتروني.',
            'password.required'   => 'الرجاء إدخال كلمة المرور.',
        ];
    }
}
