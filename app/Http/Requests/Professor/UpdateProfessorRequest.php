<?php

namespace App\Http\Requests\Professor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $professor = $this->route('professor');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('professors', 'email')->ignore($professor),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'department_id' => ['sometimes', 'required', 'integer', 'exists:departments,id'],
        ];
    }
}
