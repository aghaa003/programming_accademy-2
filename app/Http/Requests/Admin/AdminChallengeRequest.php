<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminChallengeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        $base     = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'title'             => array_merge($base, ['string', 'max:500']),
            'description'       => array_merge($base, ['string']),
            'category'          => array_merge($base, ['string', 'in:algorithms,data-structures,web,database']),
            'difficulty'        => array_merge($base, ['string', 'in:easy,medium,hard']),
            'points'            => ['sometimes', 'integer', 'min:0'],
            'starter_code'      => ['nullable', 'string'],
            'code_language'     => ['nullable', 'string', 'max:50'],
            'test_cases'        => ['nullable', 'string'],
            'solution_template' => ['nullable', 'string'],
            'is_active'         => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'عنوان التحدي مطلوب.',
            'description.required' => 'وصف التحدي مطلوب.',
            'category.required'    => 'الفئة مطلوبة.',
            'category.in'          => 'الفئة غير صالحة.',
            'difficulty.required'  => 'مستوى الصعوبة مطلوب.',
            'difficulty.in'        => 'مستوى الصعوبة غير صالح.',
            'points.integer'       => 'النقاط يجب أن تكون عدداً صحيحاً.',
            'points.min'           => 'النقاط لا يمكن أن تكون سالبة.',
        ];
    }
}
