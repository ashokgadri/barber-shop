<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'email' => 'email|required|max:250',
            'first_name' => 'string|required|max:50',
            'last_name' => 'string|required:max:50',
            'slot_time' => 'date_format:H:i:s|required',
            'schedule_id' => 'integer|required|exists:schedules,id'
        ];
    }
}
