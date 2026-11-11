<?php

namespace App\Http\Requests;

use App\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Payroll sheet: the period header (working days, hours per day, overtime / late multipliers,
 * payment date and account) and the selected employees' attendance, overtime, late minutes
 * and advances. Authorization is done by the controller (Transaction policy).
 */
class PayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => 'required|date_format:Y-m',
            'total_working_days' => 'required|numeric|min:1|max:31',
            'hours_per_day' => 'required|numeric|min:1|max:24',
            'overtime_multiplier' => 'required|numeric|min:0|max:10',
            'late_multiplier' => 'required|numeric|min:0|max:10',
            'payment_date' => 'required|date',
            'bank_id' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,employee_id',
            'employees' => 'required|array',
            'employees.*.actual_working_days' => 'nullable|numeric|min:0',
            'employees.*.overtime_hours' => 'nullable|numeric|min:0',
            'employees.*.late_minutes' => 'nullable|integer|min:0',
            'employees.*.temp_advance' => 'nullable|numeric|min:0',
            'employees.*.fixed_advance' => 'nullable|numeric|min:0',
            'employees.*.fixed_advance_deduction' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Select at least one employee.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $bankId = (int) $this->input('bank_id');
            if ($bankId !== 0 && ! Bank::where('bank_id', $bankId)->exists()) {
                $validator->errors()->add('bank_id', 'The selected bank / cash account is invalid.');
            }

            $totalDays = (float) $this->input('total_working_days');
            foreach ((array) $this->input('employee_ids', []) as $employeeId) {
                $row = $this->input("employees.{$employeeId}", []);
                if (($row['actual_working_days'] ?? '') === '' || ! isset($row['actual_working_days'])) {
                    $validator->errors()->add("employees.{$employeeId}.actual_working_days", 'Enter the actual working days of every selected employee.');
                }
                if ((float) ($row['actual_working_days'] ?? 0) > $totalDays) {
                    $validator->errors()->add("employees.{$employeeId}.actual_working_days", 'Actual working days cannot be more than the total working days.');
                }
                if ((float) ($row['fixed_advance_deduction'] ?? 0) > (float) ($row['fixed_advance'] ?? 0)) {
                    $validator->errors()->add("employees.{$employeeId}.fixed_advance_deduction", 'Fixed advance deduction cannot be more than the fixed advance.');
                }
            }
        });
    }

    /**
     * Header values plus the selected employees' rows, in the shape PayrollRepository::store() takes.
     */
    public function payroll(): array
    {
        $data = $this->validated();
        $selected = array_map('intval', $data['employee_ids']);
        $data['employees'] = array_intersect_key($data['employees'], array_flip($selected));
        unset($data['employee_ids']);

        return $data;
    }
}
