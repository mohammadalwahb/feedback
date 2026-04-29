<?php

namespace App\Http\Requests\Admin;

use App\Models\FeedbackForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteFormResponsesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $form = $this->route('form');
        if (! $form instanceof FeedbackForm) {
            return false;
        }

        return $this->user()?->can('delete', $form) ?? false;
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
            'confirmation.in' => __('admin.delete_form_responses_confirmation_invalid'),
        ];
    }
}
