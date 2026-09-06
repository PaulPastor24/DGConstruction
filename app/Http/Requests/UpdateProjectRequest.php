<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $this->user() && $user && in_array($user->role, ['engineer', 'admin', 'administrator'], true);
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
                    ->ignore($this->resolveProjectId(), 'project_id')
            ],
            'project_location' => ['required', 'string'],
            'client_id' => ['required', 'exists:clients,client_id'],
            'supervisor_id' => ['nullable', 'exists:users,user_id'],
            'start_date' => ['required', 'date'],
            'target_end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'actual_end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:target_end_date'],
            'status' => ['required', 'in:planning,ongoing,completed,on_hold'],
            'hold_reason' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string'],
            'project_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_image' => ['nullable', 'in:0,1'],
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
            'start_date.date' => 'Start date must be a valid date.',
            'target_end_date.required' => 'Target end date is required.',
            'target_end_date.date' => 'Target end date must be a valid date.',
            'target_end_date.after_or_equal' => 'Target end date must be after or equal to start date.',
            'actual_end_date.date' => 'Actual end date must be a valid date.',
            'actual_end_date.after_or_equal' => 'Actual end date must be after or equal to start date.',
            'actual_end_date.before_or_equal' => 'Actual end date cannot be after the planned end date.',
            'status.required' => 'Status is required.',
            'hold_reason.required' => 'Please provide a reason for placing the project on hold.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $status = Project::normalizeStatus($this->input('status'));
            $reason = trim((string) $this->input('hold_reason', ''));

            if ($status === Project::STATUS_COMPLETED && !$this->filled('actual_end_date')) {
                $validator->errors()->add('actual_end_date', 'An actual end date is required when marking a project completed.');
            }

            if ($status === Project::STATUS_ON_HOLD && $reason === '') {
                $validator->errors()->add('hold_reason', 'Please provide a reason for placing the project on hold.');
            }
        });
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
            'hold_reason' => trim($this->input('hold_reason')),
            'project_id' => $this->resolveProjectId(),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        $projectId = $this->resolveProjectId();
        $firstError = $validator->errors()->first();

        throw new HttpResponseException(
            redirect()
                ->back()
                ->withInput()
                ->with('edit_project_id', $projectId)
                ->with('show_edit_project_modal', true)
                ->with('error', $firstError ?: 'Please review the project details and try again.')
                ->with('success_title', 'Project update failed')
        );
    }

    private function resolveProjectId(): ?int
    {
        $routeProject = $this->route('project');

        if ($routeProject instanceof Project) {
            return (int) $routeProject->project_id;
        }

        $projectId = $this->input('project_id');

        return is_numeric($projectId) ? (int) $projectId : null;
    }
}