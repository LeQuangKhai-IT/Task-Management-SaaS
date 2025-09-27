<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateLabelRequest extends FormRequest
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
        $label = $this->route('label');
        return [
            'name' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('labels', 'name')->where('board_id', $label->board_id)->ignore($label->id),
            ],
            'color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
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
            'name.string' => 'The label name must be a string.',
            'name.max' => 'The label name may not be greater than 50 characters.',
            'name.unique' => 'The label name already exists for this board.',

            'color.string' => 'The label color must be a string.',
            'color.regex' => 'The label color must be a valid hex color code (e.g., #FF0000 or #FFF).',
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
