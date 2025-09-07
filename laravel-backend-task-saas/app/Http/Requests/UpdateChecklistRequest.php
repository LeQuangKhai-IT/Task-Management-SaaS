<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChecklistRequest extends FormRequest
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
        $checklist = $this->route('checklist');
        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('checklists', 'title')->where('card_id', $checklist->card_id)->ignore($checklist->id),
            ],
            'position' => 'sometimes|numeric|min:0',
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
            'title.string' => 'The checklist title must be a string.',
            'title.max' => 'The checklist title may not be greater than 255 characters.',
            'title.unique' => 'The checklist title already exists for this card.',

            'position.numeric' => 'The position must be a number.',
            'position.min' => 'The position must be at least 0.',
        ];
    }
}
