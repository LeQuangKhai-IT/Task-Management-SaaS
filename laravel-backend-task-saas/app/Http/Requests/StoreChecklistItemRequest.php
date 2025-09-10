<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreChecklistItemRequest extends FormRequest
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
            'content' => 'required|string|max:1000',
            'checklist_id' => 'required|uuid|exists:checklists,id',
            'completed' => 'sometimes|boolean',
            'position' => 'nullable|numeric|min:0',
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
            'content.required' => 'The checklist item content is required.',
            'content.string' => 'The checklist item content must be a string.',
            'content.max' => 'The checklist item content may not be greater than 1000 characters.',

            'checklist_id.required' => 'The checklist ID is required.',
            'checklist_id.uuid' => 'The checklist ID must be a valid UUID.',
            'checklist_id.exists' => 'The specified checklist does not exist.',

            'completed.boolean' => 'The completed field must be a boolean.',

            'position.numeric' => 'The position must be a number.',
            'position.min' => 'The position must be at least 0.',
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
