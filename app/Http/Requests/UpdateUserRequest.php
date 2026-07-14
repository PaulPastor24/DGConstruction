<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
     * Normalize input before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name'     => is_string($this->first_name) ? trim(preg_replace('/\s+/', ' ', strip_tags($this->first_name))) : $this->first_name,
            'last_name'      => is_string($this->last_name) ? trim(preg_replace('/\s+/', ' ', strip_tags($this->last_name))) : $this->last_name,
            'email'          => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
            'contact_number' => is_string($this->contact_number) ? trim($this->contact_number) : $this->contact_number,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\s\'\-\.]+$/u',
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\s\'\-\.]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->route('user')?->user_id, 'user_id'),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', Rule::in(['engineer', 'supervisor', 'client'])],
            'contact_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(?:\+63|0)9\d{9}$/',
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid, deliverable email address.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Invalid role selected.',
            'contact_number.regex' => 'Enter a valid Philippine mobile number, e.g. 09171234567 or +639171234567.',
            'contact_number.max' => 'Contact number may not exceed 20 characters.',
            'is_active.boolean' => 'Invalid status value.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'contact_number' => 'contact number',
        ];
    }
}