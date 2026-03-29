<?php

namespace App\Http\Requests;

/**
 * A1: Validates the profile update endpoint.
 * Business-logic uniqueness checks (phone/email) remain in the controller
 * since they require excluding the current user's own values.
 */
class ProfileUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'firstName'         => ['sometimes', 'string', 'max:100'],
            'lastName'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'email'             => ['sometimes', 'email:rfc', 'max:255'],
            'phone'             => ['sometimes', 'nullable', 'string', 'max:20'],
            'country'           => ['sometimes', 'nullable', 'string', 'max:100'],
            'experience'        => ['sometimes', 'nullable', 'string', 'max:50'],
            'goal'              => ['sometimes', 'nullable', 'string', 'max:500'],
            'interest'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'preferred_language' => ['sometimes', 'nullable', 'string', 'max:50'],
            'newPassword'       => ['sometimes', 'nullable', 'string', 'min:6', 'max:72'],
            'currentPassword'   => ['required_with:newPassword', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'              => 'البريد الإلكتروني غير صالح.',
            'newPassword.min'          => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            'newPassword.max'          => 'كلمة المرور يجب أن لا تتجاوز 72 حرفاً.',
            'currentPassword.required_with' => 'يرجى إدخال كلمة المرور الحالية.',
        ];
    }
}
