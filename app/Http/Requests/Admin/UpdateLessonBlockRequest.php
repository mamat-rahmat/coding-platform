<?php

namespace App\Http\Requests\Admin;

use App\LessonBlockType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonBlockRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in(array_map(fn ($t) => $t->value, LessonBlockType::cases())),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'array'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
