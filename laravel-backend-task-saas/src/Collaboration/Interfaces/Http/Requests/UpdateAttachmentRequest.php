<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAttachmentRequest extends FormRequest
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
            'file' => 'nullable|file|mimes:jpeg,png,pdf,doc,docx|max:10240', // 10MB max
            'url' => 'nullable|url|max:255',
            'type' => [
                'sometimes',
                'string',
                'max:50',
                Rule::in(['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
            ],
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
            'name.string' => 'The attachment name must be a string.',
            'name.max' => 'The attachment name may not be greater than 255 characters.',

            'file.file' => 'The attachment must be a valid file.',
            'file.mimes' => 'The file must be a JPEG, PNG, PDF, DOC, or DOCX.',
            'file.max' => 'The file size must not exceed 10MB.',

            'url.url' => 'The URL must be a valid URL.',
            'url.max' => 'The URL may not be greater than 255 characters.',

            'type.string' => 'The file type must be a string.',
            'type.max' => 'The file type may not be greater than 50 characters.',
            'type.in' => 'The file type must be one of: image/jpeg, image/png, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document.',
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
