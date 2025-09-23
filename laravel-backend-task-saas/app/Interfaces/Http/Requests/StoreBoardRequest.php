<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBoardRequest extends FormRequest
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
            'description' => 'nullable|string|max:1000',
            'workspace_id' => 'required|uuid|exists:workspaces,id',
            'background' => 'nullable|string|max:255',
            'visibility' => 'sometimes|in:public,private,workspace',
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
            'title.required' => 'The board title is required.',
            'title.string' => 'The board title must be a string.',
            'title.max' => 'The board title may not be greater than 255 characters.',

            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',

            'workspace_id.required' => 'The workspace ID is required.',
            'workspace_id.uuid' => 'The workspace ID must be a valid UUID.',
            'workspace_id.exists' => 'The specified workspace does not exist.',

            'visibility.in' => 'The visibility must be one of: public, private, workspace.',

            'background.string' => 'The board background must be a string.',
            'background.max' => 'The board background may not be greater than 255 characters.',
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
