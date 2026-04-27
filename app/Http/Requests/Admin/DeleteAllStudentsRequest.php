<?php

namespace App\Http\Requests\Admin;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteAllStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('deleteAll', Student::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', Rule::in(['DELETE ALL'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'confirmation' => __('admin.delete_all_confirmation_field'),
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation.in' => __('admin.delete_all_confirmation_invalid'),
        ];
    }
}
