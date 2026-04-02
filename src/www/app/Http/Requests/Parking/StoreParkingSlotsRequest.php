<?php

namespace App\Http\Requests\Parking;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParkingSlotsRequest extends FormRequest
{
    use failedValidationWithName;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'company_id' => 'required|integer|min:1',
            'floor_no' => [
                'required',
                'string',
                'min:1',
                'max:50',
                // This checks for duplicates ONLY within the same company
                Rule::unique('parking_slots', 'floor_no')->where(function ($query) {
                    return $query->where('company_id', $this->company_id);
                }),
            ],

            'start_number' => [
                'required',
                'integer',
                'min:1',
                // This checks for duplicates ONLY within the same company
                Rule::unique('parking_slots', 'slot_number')->where(function ($query) {
                    return $query->where('company_id', $this->company_id);
                }),
            ],

            'end_number' => [
                'required',
                'integer',
                'min:1',
                'gte:start_number',
                // This checks for duplicates ONLY within the same company
                Rule::unique('parking_slots', 'slot_number')->where(function ($query) {
                    return $query->where('company_id', $this->company_id);
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'company_id.required' => 'Company ID is required',
            'company_id.integer' => 'Company ID must be an integer',
            'floor_no.required' => 'Floor number is required',
            'floor_no.string' => 'Floor number must be a string',
            'start_number.required' => 'Start number is required',
            'start_number.integer' => 'Start number must be an integer',
            'end_number.required' => 'End number is required',
            'end_number.integer' => 'End number must be an integer',
        ];
    }
}
