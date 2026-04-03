<?php

namespace App\Http\Requests\Unit;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
            'floor_no' => [
                'required',
                'string',
                'min:1',
            ],
            'unit_number' => [
                'required',
                'integer',
                Rule::unique('units', 'unit_number')
                    ->where('company_id', $this->company_id)
                    ->ignore($slotId), // CRITICAL: Ignore the current record
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
            'floor_no.required' => 'Floor number is required',
            'floor_no.string' => 'Floor number must be a string',
        ];
    }
}
