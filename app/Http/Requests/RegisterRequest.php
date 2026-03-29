<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

/**
 * H5: Validates the register endpoint inputs.
 */
class RegisterRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName'  => ['nullable', 'string', 'max:100'],
            'email'     => ['required', 'email:rfc,dns', 'max:255'],
            'username'  => ['required', 'string', 'alpha_dash', 'min:3', 'max:50'],
            'password'  => ['required', 'string', 'min:6', 'max:72'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'country'   => ['nullable', 'string', 'max:100'],
            'experience' => ['nullable', 'string', 'max:50'],
            'goal'      => ['nullable', 'string', 'max:500'],
            'interest'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.required' => 'الاسم الأول مطلوب.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.email'        => 'البريد الإلكتروني غير صالح.',
            'username.required'  => 'اسم المستخدم مطلوب.',
            'username.alpha_dash' => 'اسم المستخدم يجب أن يحتوي على حروف وأرقام وشرطات فقط.',
            'username.min'       => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل.',
            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.min'       => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            'password.max'       => 'كلمة المرور يجب أن لا تتجاوز 72 حرفاً.',
        ];
    }
}
