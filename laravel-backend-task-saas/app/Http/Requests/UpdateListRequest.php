<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'position' => 'sometimes|numeric|min:0',
            'archived' => 'sometimes|boolean',
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
            'title.string' => 'The list title must be a string.',
            'title.max' => 'The list title may not be greater than 255 characters.',

            'position.numeric' => 'The position must be an double.',
            'position.min' => 'The position must be at least 0.',

            'archived.boolean' => 'The archived field must be a boolean.',
        ];
    }
}
