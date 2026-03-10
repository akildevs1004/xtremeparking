<?php

namespace App\Http\Requests\Device;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        $deviceId = $this->device_id;
        $companyId = $this->company_id;
        return [

            'device_id' => [
                'required',
                Rule::unique('devices')->where(function ($query) use ($deviceId, $companyId) {
                    return $query->where('device_id', $deviceId)
                        ->where('company_id', $companyId);
                }),

            ],

            'name' => ['required', 'min:2', 'max:50'],
            'short_name' => ['nullable', 'nullable', 'min:3', 'max:50'],
            // 'device_id' => ['required', 'min:3', 'max:100', 'unique:devices'],
            'location' => ['required', 'min:2', 'max:300'],
            'company_id' => ['required', 'min:1', 'integer'],
            // 'status_id' => ['required', 'min:1', 'integer'],
            'device_id' => ['nullable'],

            'serial_number' => ['required'],
            'model_number' => ['required'],

            'threshold_temperature' => ['nullable'],



            'device_type' => ['required'],

            'mode' => ['nullable'],

            'ip' => 'required|ip',
            'function' => 'required',
            'utc_time_zone' => 'required',
            'branch_id' => 'nullable',
            'alarm_delay_minutes' => 'required',
            'customer_id' => 'nullable'

        ];
    }
}
