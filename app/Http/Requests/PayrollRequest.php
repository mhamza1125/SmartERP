<?php

namespace App\Http\Requests;

use App\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Payroll sheet: the period header (working days, hours per day, overtime / late multipliers,
 * payment date and account) and the selected employees' own total days, attendance, paid leaves,
 * overtime hours, late hours and advances. Days, hours, rates and amounts take up to 2 decimals.
 * Authorization is done by the controller (Transaction policy).
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
            'total_working_days' => 'required|numeric|min:1|max:31|decimal:0,2',
            'hours_per_day' => 'required|numeric|min:1|max:24|decimal:0,2',
            'overtime_multiplier' => 'required|numeric|min:0|max:10|decimal:0,2',
            'late_multiplier' => 'required|numeric|min:0|max:10|decimal:0,2',
            'payment_date' => 'required|date',
            'bank_id' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,employee_id',
            'employees' => 'required|array',
            'employees.*.total_working_days' => 'nullable|numeric|min:0|max:31|decimal:0,2',
            'employees.*.actual_working_days' => 'nullable|numeric|min:0|max:31|decimal:0,2',
            'employees.*.paid_leaves' => 'nullable|numeric|min:0|max:31|decimal:0,2',
            'employees.*.overtime_hours' => 'nullable|numeric|min:0|decimal:0,2',
            'employees.*.late_hours' => 'nullable|numeric|min:0|decimal:0,2',
            'employees.*.temp_advance' => 'nullable|numeric|min:0|decimal:0,2',
            'employees.*.fixed_advance' => 'nullable|numeric|min:0|decimal:0,2',
            'employees.*.fixed_advance_deduction' => 'nullable|numeric|min:0|decimal:0,2',
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

            $headerDays = (float) $this->input('total_working_days');
            foreach ((array) $this->input('employee_ids', []) as $employeeId) {
                $row = $this->input("employees.{$employeeId}", []);
                if (($row['actual_working_days'] ?? '') === '' || ! isset($row['actual_working_days'])) {
                    $validator->errors()->add("employees.{$employeeId}.actual_working_days", 'Enter the actual working days of every selected employee.');
                }
                // The employee's own total days (default: the header's) bound worked days + paid leaves
                $ownDays = ($row['total_working_days'] ?? '') === '' ? $headerDays : (float) $row['total_working_days'];
                if ((float) ($row['actual_working_days'] ?? 0) + (float) ($row['paid_leaves'] ?? 0) > $ownDays + 0.001) {
                    $validator->errors()->add("employees.{$employeeId}.actual_working_days", 'Worked days plus paid leaves cannot be more than the employee\'s total days ('.$ownDays.').');
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
