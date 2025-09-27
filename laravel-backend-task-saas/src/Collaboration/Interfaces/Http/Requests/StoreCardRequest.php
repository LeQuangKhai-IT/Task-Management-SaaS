<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCardRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'list_id' => 'required|uuid|exists:lists,id',
            'position' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date|after_or_equal:today',
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
            'name.required' => 'The card name is required.',
            'name.string' => 'The card name must be a string.',
            'name.max' => 'The card name may not be greater than 255 characters.',

            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 2000 characters.',

            'list_id.required' => 'The list ID is required.',
            'list_id.uuid' => 'The list ID must be a valid UUID.',
            'list_id.exists' => 'The specified list does not exist.',

            'position.numeric' => 'The position must be an double.',
            'position.min' => 'The position must be at least 0.',

            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after_or_equal' => 'The due date must be today or in the future.',

            'archived.boolean' => 'The archived field must be a boolean.',
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
