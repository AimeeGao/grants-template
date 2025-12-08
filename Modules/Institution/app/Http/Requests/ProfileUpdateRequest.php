<?php

namespace Modules\Institution\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only INSTITUTION_ADMIN can update institution profile
        return Auth::user()->hasRole(Role::INSTITUTION_ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'primary_contact' => 'nullable|string|max:100',
            'primary_email' => 'nullable|email|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:50',
            'postal_code' => ['nullable', 'string', 'max:7', 'regex:/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Normalize postal code format (remove spaces, uppercase)
        if ($this->postal_code) {
            $this->merge([
                'postal_code' => Str::upper(str_replace(' ', '', $this->postal_code)),
            ]);
        }

        // Capitalize city name
        if ($this->city) {
            $this->merge([
                'city' => Str::title($this->city),
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'postal_code.regex' => 'The Postal Code format is invalid. Example: V8V 1X4',
            'primary_email.email' => 'Please enter a valid email address.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'postal_code' => 'Postal Code',
            'primary_contact' => 'Primary Contact Name',
            'primary_email' => 'Primary Contact Email',
            'address1' => 'Address Line 1',
            'address2' => 'Address Line 2',
        ];
    }
}