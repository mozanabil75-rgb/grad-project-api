<?php

namespace App\Http\Requests\Course;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'professor_ids' => ['nullable', 'array'],
            'professor_ids.*' => ['integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', UserRole::Professor->value))],
        ];
    }
}
