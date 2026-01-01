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
            // New personal and professional fields
            'marital_status' => 'nullable|in:married,single,divorced,widower',
            'siblings_count' => 'nullable|integer|min:0',
            'additional_skills' => 'nullable|string',
            // Children details validation
            'children_details.*.name' => 'nullable|string|max:255',
            'children_details.*.gender' => 'nullable|in:male,female',
            'children_details.*.age' => 'nullable|integer|min:0|max:100',
            // Education validation
            'education.*.institution_name' => 'nullable|string|max:255',
            'education.*.degree' => 'nullable|string|max:255',
            'education.*.year_of_passing' => 'nullable|integer|min:1900|max:2100',
            'education.*.percentage' => 'nullable|numeric|min:0|max:100',
            // Employment history validation
            'employment_history.*.company_name' => 'nullable|string|max:255',
            'employment_history.*.designation' => 'nullable|string|max:255',
            'employment_history.*.from_date' => 'nullable|date',
            'employment_history.*.to_date' => 'nullable|date',
            'employment_history.*.salary' => 'nullable|numeric|min:0',
            'employment_history.*.reason_for_leaving' => 'nullable|string|max:500',
        ];
    }
}
