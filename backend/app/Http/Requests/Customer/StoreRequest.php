<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
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
            'company_id' => "required",
            "building_type_id" => "required",
            'building_name' => "required",
            'house_number' => 'nullable',
            'street_number' => "nullable",
            'city' => "required",
            'state' => "required",
            'country' => "required",
            'landmark' => "required",
            'latitude' => "required",
            'longitude' => "required",
            'start_date' => "required",
            'end_date' => "required",
            'email' => "required",
            'password' => "required",
            'area' => "nullable",





        ];
    }
}
