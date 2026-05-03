<?php

namespace App\Http\Requests\Admin;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteDepartmentStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');
        if (! $department instanceof Department) {
            return false;
        }

        return $this->user()?->can('deleteStudents', $department) ?? false;
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
