<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCardRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'list_id' => 'sometimes|uuid|exists:lists,id',
            'position' => 'sometimes|numeric|min:0',
            'archived' => 'sometimes|boolean',
            'due_date' => 'nullable|date|after_or_equal:today',
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
            'name.string' => 'The card name must be a string.',
            'name.max' => 'The card name may not be greater than 255 characters.',

            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 2000 characters.',

            'list_id.uuid' => 'The list ID must be a valid UUID.',
            'list_id.exists' => 'The specified list does not exist.',

            'position.numeric' => 'The position must be an double.',
            'position.min' => 'The position must be at least 0.',

            'archived.boolean' => 'The archived field must be a boolean.',

            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after_or_equal' => 'The due date must be today or in the future.',
        ];
    }

    /**
     * Response with json
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
