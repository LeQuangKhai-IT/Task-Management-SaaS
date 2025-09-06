<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:8|current_password:api',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
            'new_password_confirmation' => 'required|string|min:8',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'current_password.required' => 'The current password is required.',
            'current_password.string' => 'The current password must be a string.',
            'current_password.min' => 'The current password must be at least 8 characters.',
            'current_password.current_password' => 'The current password is incorrect.',

            'new_password.required' => 'The new password is required.',
            'new_password.string' => 'The new password must be a string.',
            'new_password.min' => 'The new password must be at least 8 characters.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.different' => 'The new password must be different from the current password.',

            'new_password_confirmation.required' => 'The new password confirmation is required.',
            'new_password_confirmation.string' => 'The new password confirmation must be a string.',
            'new_password_confirmation.min' => 'The new password confirmation must be at least 8 characters.',
        ];
    }
}
