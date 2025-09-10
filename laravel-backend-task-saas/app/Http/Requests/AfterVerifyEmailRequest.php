<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AfterVerifyEmailRequest extends FormRequest
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
            'email'    => 'required|email|exists:users,email',
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Customize the error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Please enter your email.',
            'email.email'    => 'Invalid email format.',
            'email.exists'   => 'This email is not registered.',

            'fullname.required' => 'Please enter your full name.',

            'password.required' => 'Please enter a password.',
            'password.min'      => 'Password must be at least 8 characters.',
        ];
    }
}
