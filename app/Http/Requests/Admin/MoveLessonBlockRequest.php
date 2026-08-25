<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoveLessonBlockRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_lesson_id' => ['required', 'integer', 'exists:lessons,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_lesson_id.required' => 'Lesson tujuan wajib dipilih.',
            'target_lesson_id.exists' => 'Lesson tujuan tidak ditemukan.',
        ];
    }
}
