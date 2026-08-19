<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderLessonBlocksRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'blocks' => ['required', 'array'],
            'blocks.*.id' => ['required', 'integer', 'distinct'],
            'blocks.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
