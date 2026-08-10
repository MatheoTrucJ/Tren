<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutDemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user' => ['required', 'integer', 'min:1'],
            'workout_name' => ['required', 'string', 'max:48'],
            'workout_description' => ['nullable', 'string', 'max:1000'],
            'exercises' => ['required', 'array', 'min:1'],
            'exercises.*.exercise_id' => ['required', 'integer', 'min:1'],
            'exercises.*.order_index' => ['required', 'integer', 'min:1'],
            'exercises.*.set_count' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
