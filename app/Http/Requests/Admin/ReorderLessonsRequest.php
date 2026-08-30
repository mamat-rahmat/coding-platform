<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderLessonsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lessons' => ['required', 'array'],
            'lessons.*.id' => ['required', 'integer', 'distinct'],
            'lessons.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
