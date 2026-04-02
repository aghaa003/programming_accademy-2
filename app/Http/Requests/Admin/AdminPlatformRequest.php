<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminPlatformRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        $base     = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'name'          => array_merge($base, ['string', 'max:255']),
            'description'   => ['nullable', 'string'],
            'url'           => ['nullable', 'url'],
            'category'      => ['nullable', 'string', 'max:50'],
            'level'         => ['nullable', 'string', 'max:50'],
            'language'      => ['nullable', 'string', 'max:50'],
            'user_count'    => ['nullable', 'integer', 'min:0'],
            'problem_count' => ['nullable', 'integer', 'min:0'],
            'features'      => ['nullable', 'array'],
            'logo_url'      => ['nullable', function ($attr, $value, $fail) {
                if (! empty($value)
                    && ! preg_match('/^https?:\/\//i', $value)
                    && ! str_starts_with($value, '/uploads/')
                ) {
                    $fail('رابط الشعار يجب أن يبدأ بـ http:// أو https://');
                }
            }],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المنصة مطلوب.',
            'url.url'            => 'رابط المنصة يجب أن يبدأ بـ http:// أو https://',
            'user_count.integer' => 'عدد المستخدمين يجب أن يكون رقماً.',
            'user_count.min'     => 'عدد المستخدمين لا يمكن أن يكون سالباً.',
        ];
    }
}
