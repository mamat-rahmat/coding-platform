<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:courses,slug'],
            'description' => ['nullable', 'string'],
            'language' => ['required', 'string', 'max:50'],
            'level' => ['required', 'string', 'max:50'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'xp_reward' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_published' => ['boolean'],
        ];
    }
}
