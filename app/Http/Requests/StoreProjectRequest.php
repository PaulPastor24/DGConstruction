<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $this->user() && $user && $user->role === 'engineer';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_name' => [
                'required', 
                'string', 
                'max:200',
                Rule::unique('projects', 'project_name')
            ],
            'project_location' => ['required', 'string'],
            'client_id' => ['required', 'exists:clients,client_id'],
            'supervisor_id' => ['nullable', 'exists:users,user_id'],
            'start_date' => ['required', 'date'],
            'target_end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:planning,ongoing,completed,on_hold'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_name.required' => 'Project name is required.',
            'project_name.unique' => 'A project with this name already exists. Please use a different name.',
            'project_location.required' => 'Project location is required.',
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'Selected client does not exist.',
            'supervisor_id.exists' => 'Selected supervisor does not exist.',
            'start_date.required' => 'Start date is required.',
            'target_end_date.required' => 'Target end date is required.',
            'target_end_date.after_or_equal' => 'Target end date must be after or equal to start date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'project_name' => trim($this->input('project_name')),
            'project_location' => trim($this->input('project_location')),
            'description' => trim($this->input('description')),
        ]);
    }
}
