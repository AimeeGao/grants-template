<?php

namespace Modules\Student\Http\Requests;

use App\Rules\ValidSin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProfileUpdateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'sin' => ['nullable', new ValidSin],
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'zip_code' => ['nullable', 'string', 'max:7', 'regex:/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/'],

            // Demographics validation
            'demographics' => 'array',
            'demographics.*' => 'array',
            'demographics.*.demographic_id' => 'required|exists:demographics,id',
            'demographics.*.answers' => 'required|array',
            'demographics.*.answers.*' => 'required|string',
        ];
    }

     /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'zip_code' => Str::upper(str_replace(' ', '', $this->zip_code)),
            'city' => Str::title($this->city),
        ]);
    }
    
    public function messages(): array
    {
        return [
            'zip_code.regex' => 'The Postal Code format is invalid. Example: V8V 1X4',
            'sin.regex' => 'The SIN format is invalid.',
        ];
    }

    
    public function attributes(): array
    {
        return [
            'dob' => 'Birth Date',
            'sin' => 'Social Insurance Number',
            'zip_code' => 'Postal Code',
        ];
    }

}
