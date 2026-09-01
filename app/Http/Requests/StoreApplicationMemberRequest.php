<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $application = $this->route('application');

        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('application_members', 'user_id')->where(fn ($query) => $query
                    ->where('application_id', $application->id)
                    ->where('status', 'active')),
            ],
            'status' => ['nullable', 'string', 'in:active,ended'],
        ];
    }
}
