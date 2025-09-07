<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'board_id' => 'required|uuid|exists:boards,id',
            'position' => 'nullable|numeric|min:0',
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
            'title.required' => 'The list title is required.',
            'title.string' => 'The list title must be a string.',
            'title.max' => 'The list title may not be greater than 255 characters.',

            'board_id.required' => 'The board ID is required.',
            'board_id.uuid' => 'The board ID must be a valid UUID.',
            'board_id.exists' => 'The specified board does not exist.',

            'position.numeric' => 'The position must be an double.',
            'position.min' => 'The position must be at least 0.',

            'archived.boolean' => 'The archived field must be a boolean.',
        ];
    }
}
