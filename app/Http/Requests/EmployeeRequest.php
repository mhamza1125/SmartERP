<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
        return [
            'employee_no' => 'required|max:255',
            'department_id' => 'required',
            'employee_type_id' => 'required',
            'attendance_id' => '',
            'joining_date' => 'required',
            'employee_status' => 'required',
            'city_id' => 'required',
            'name' => 'required|max:255',
            'fname' => 'required|max:255',
            'sname' => '',
            'cnic' => 'required|max:255',
            'phone1' => 'required|max:255',
            'phone2' => '',
            'salary' => '',
            'address' => 'required|max:255',
            'designation' => '',
            'description' => '',
        ];
    }
}
