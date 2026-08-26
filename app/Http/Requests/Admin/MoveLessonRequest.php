<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoveLessonRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_module_id' => ['required', 'integer', 'exists:course_modules,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_module_id.required' => 'Module tujuan wajib dipilih.',
            'target_module_id.exists' => 'Module tujuan tidak ditemukan.',
        ];
    }
}
