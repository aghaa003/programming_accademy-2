<?php

namespace App\Http\Requests;

class ReviewRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'rating'      => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required'      => 'التقييم مطلوب.',
            'rating.integer'       => 'التقييم يجب أن يكون رقماً.',
            'rating.min'           => 'التقييم يجب أن يكون بين 1 و 5.',
            'rating.max'           => 'التقييم يجب أن يكون بين 1 و 5.',
            'review_text.required' => 'نص التقييم مطلوب.',
            'review_text.min'      => 'نص التقييم يجب أن يكون 5 أحرف على الأقل.',
            'review_text.max'      => 'نص التقييم لا يجب أن يتجاوز 500 حرف.',
        ];
    }
}
