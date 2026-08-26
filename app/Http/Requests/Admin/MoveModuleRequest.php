<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoveModuleRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_course_id' => ['required', 'integer', 'exists:courses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_course_id.required' => 'Course tujuan wajib dipilih.',
            'target_course_id.exists' => 'Course tujuan tidak ditemukan.',
        ];
    }
}
