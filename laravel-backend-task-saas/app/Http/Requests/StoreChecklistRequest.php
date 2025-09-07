<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('checklists', 'title')->where('card_id', $this->card_id),
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
            'title.required' => 'The checklist title is required.',
            'title.string' => 'The checklist title must be a string.',
            'title.max' => 'The checklist title may not be greater than 255 characters.',
            'title.unique' => 'The checklist title already exists for this card.',

            'card_id.required' => 'The card ID is required.',
            'card_id.uuid' => 'The card ID must be a valid UUID.',
            'card_id.exists' => 'The specified card does not exist.',

            'position.numeric' => 'The position must be a number.',
            'position.min' => 'The position must be at least 0.',
        ];
    }
}
