<?php

namespace App\Http\Requests;

use App\Repositories\AttendanceRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CompanyRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'ceo' => 'required|string|max:255',
            'ntn' => 'nullable|string|max:255',
            'rex_no' => 'nullable|string|max:255',
            'fi_no' => 'nullable|string|max:255',
            // Payroll defaults (Settings > Company)
            'overtime_multiplier' => 'nullable|numeric|min:0|max:10',
            'late_multiplier' => 'nullable|numeric|min:0|max:10',
            'work_hours_per_day' => 'nullable|numeric|min:1|max:24',
            // Attendance machine database (ATT2000.mdb); checked in withValidator()
            'attendance_db_path' => 'nullable|string|max:500',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_path' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'statement_of_origin' => 'nullable|string',
        ];
    }

    /**
     * The attendance database must be an existing, readable .mdb / .accdb file in an allowed folder.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $path = trim((string) $this->input('attendance_db_path'));
            if ($path === '' || $validator->errors()->has('attendance_db_path')) {
                return;
            }
            $problem = app(AttendanceRepository::class)->machineDbProblem($path);
            if ($problem !== null) {
                $validator->errors()->add('attendance_db_path', $problem);
            }
        });
    }
}
