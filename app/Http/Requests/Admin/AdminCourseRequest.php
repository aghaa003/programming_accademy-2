<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminCourseRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        $base = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'title' => array_merge($base, ['string', 'max:255']),
            'category' => array_merge($base, ['string', 'max:100']),
            'description' => ['nullable', 'string'],
            'main_points' => ['nullable', 'string'],
            'level' => ['nullable', 'string', 'in:أساسي,متوسط,متقدم,مبتدئ-متوسط,أساسي-متوسط,متوسط-متقدم,Beginner,Intermediate,Advanced'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الكورس مطلوب.',
            'category.required' => 'تصنيف الكورس مطلوب.',
            'level.in' => 'المستوى غير صالح.',
        ];
    }
}
