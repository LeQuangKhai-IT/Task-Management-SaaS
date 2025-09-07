<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            'content' => 'required|string|max:2000',
            'card_id' => 'required|uuid|exists:cards,id',
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
            'content.required' => 'The comment content is required.',
            'content.string' => 'The comment content must be a string.',
            'content.max' => 'The comment content may not be greater than 2000 characters.',

            'card_id.required' => 'The card ID is required.',
            'card_id.uuid' => 'The card ID must be a valid UUID.',
            'card_id.exists' => 'The specified card does not exist.',
        ];
    }
}
