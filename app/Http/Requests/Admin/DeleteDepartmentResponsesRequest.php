<?php

namespace App\Http\Requests\Admin;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteDepartmentResponsesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');
        if (! $department instanceof Department) {
            return false;
        }

        return $this->user()?->can('deleteResponses', $department) ?? false;
    }

    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', Rule::in(['DELETE RESPONSES'])],
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
            'confirmation.in' => __('admin.delete_department_responses_confirmation_invalid'),
        ];
    }
}
