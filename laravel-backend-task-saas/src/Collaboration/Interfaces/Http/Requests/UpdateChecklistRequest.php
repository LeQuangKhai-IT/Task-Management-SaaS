<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateChecklistRequest extends FormRequest
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
        $checklist = $this->route('checklist');
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('checklists', 'name')->where('card_id', $checklist->card_id)->ignore($checklist->id),
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
            'name.string' => 'The checklist name must be a string.',
            'name.max' => 'The checklist name may not be greater than 255 characters.',
            'name.unique' => 'The checklist name already exists for this card.',

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
