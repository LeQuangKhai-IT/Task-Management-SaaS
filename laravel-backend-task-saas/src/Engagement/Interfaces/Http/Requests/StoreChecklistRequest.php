<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreChecklistRequest extends FormRequest
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
                'max:255',
                Rule::unique('checklists', 'name')->where('card_id', $this->card_id),
            ],
            'card_id' => 'required|uuid|exists:cards,id',
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
            'name.required' => 'The checklist name is required.',
            'name.string' => 'The checklist name must be a string.',
            'name.max' => 'The checklist name may not be greater than 255 characters.',
            'name.unique' => 'The checklist name already exists for this card.',

            'card_id.required' => 'The card ID is required.',
            'card_id.uuid' => 'The card ID must be a valid UUID.',
            'card_id.exists' => 'The specified card does not exist.',

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
