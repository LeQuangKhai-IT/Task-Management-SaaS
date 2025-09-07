<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabelRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('labels', 'name')->where('board_id', $this->board_id),
            ],
            'color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'board_id' => 'required|uuid|exists:boards,id',
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
            'name.required' => 'The label name is required.',
            'name.string' => 'The label name must be a string.',
            'name.max' => 'The label name may not be greater than 50 characters.',
            'name.unique' => 'The label name already exists for this board.',

            'color.required' => 'The label color is required.',
            'color.string' => 'The label color must be a string.',
            'color.regex' => 'The label color must be a valid hex color code (e.g., #FF0000 or #FFF).',

            'board_id.required' => 'The board ID is required.',
            'board_id.uuid' => 'The board ID must be a valid UUID.',
            'board_id.exists' => 'The specified board does not exist.',
        ];
    }
}
