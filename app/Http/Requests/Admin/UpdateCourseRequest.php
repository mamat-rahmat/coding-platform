<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $course = $this->route('course');
        $courseId = $course instanceof Course ? $course->id : (is_string($course) ? $course : null);

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($courseId),
            ],
            'description' => ['nullable', 'string'],
            'language' => ['required', 'string', 'max:50'],
            'level' => ['required', 'string', 'max:50'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'xp_reward' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_published' => ['boolean'],
        ];
    }
}
