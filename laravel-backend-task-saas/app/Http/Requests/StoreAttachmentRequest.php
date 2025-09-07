<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'file' => 'nullable|file|mimes:jpeg,png,pdf,doc,docx|max:10240', // 10MB max
            'url' => 'nullable|url|max:255',
            'type' => [
                'required',
                'string',
                'max:50',
                Rule::in(['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
            ],
            'card_id' => 'required|uuid|exists:cards,id',
            Rule::requiredIf(function () {
                return !$this->hasFile('file') && !$this->filled('url');
            }) => ['file', 'url'], // Ensure at least one of file or url is provided
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
            'name.required' => 'The attachment name is required.',
            'name.string' => 'The attachment name must be a string.',
            'name.max' => 'The attachment name may not be greater than 255 characters.',

            'file.file' => 'The attachment must be a valid file.',
            'file.mimes' => 'The file must be a JPEG, PNG, PDF, DOC, or DOCX.',
            'file.max' => 'The file size must not exceed 10MB.',

            'url.url' => 'The URL must be a valid URL.',
            'url.max' => 'The URL may not be greater than 255 characters.',

            'type.required' => 'The file type is required.',
            'type.string' => 'The file type must be a string.',
            'type.max' => 'The file type may not be greater than 50 characters.',
            'type.in' => 'The file type must be one of: image/jpeg, image/png, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document.',

            'card_id.required' => 'The card ID is required.',
            'card_id.uuid' => 'The card ID must be a valid UUID.',
            'card_id.exists' => 'The specified card does not exist.',

            '*.required_if' => 'Either a file or a URL must be provided.',
        ];
    }
}
