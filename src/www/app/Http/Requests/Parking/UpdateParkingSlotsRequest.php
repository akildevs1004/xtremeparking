<?php

namespace App\Http\Requests\Parking;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParkingSlotsRequest extends FormRequest
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
        // Get the ID from the route (e.g., /parking-slots/{id})
        $slotId = $this->route('id');

        return [
            'floor_no'   => 'required|string|max:50',
            'slot_number' => [
                'required',
                'integer',
                Rule::unique('parking_slots', 'slot_number')
                    ->where('company_id', $this->company_id)
                    ->ignore($slotId), // CRITICAL: Ignore the current record
            ],
            'status' => 'nullable|string'
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
