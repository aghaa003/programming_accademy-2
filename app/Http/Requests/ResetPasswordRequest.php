<?php

namespace App\Http\Requests;

/**
 * M6: Validates the reset-password endpoint.
 */
class ResetPasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'token'    => ['required', 'string', 'size:64'],   // hex(32) = 64 chars
            'password' => ['required', 'string', 'min:6', 'max:72'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'    => 'رمز إعادة التعيين مطلوب.',
            'token.size'        => 'رمز إعادة التعيين غير صالح.',
            'password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'password.min'      => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            'password.max'      => 'كلمة المرور يجب أن لا تتجاوز 72 حرفاً.',
        ];
    }
}
