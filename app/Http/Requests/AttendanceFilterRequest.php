<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Date range / employee filter of the attendance and salary calculation pages.
 * Authorization is checked in the controller (Attendance policy).
 */
class AttendanceFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|integer|min:0',
            'dfrom' => 'nullable|date_format:Y-m-d|required_with:dto',
            'dto' => 'nullable|date_format:Y-m-d|required_with:dfrom|after_or_equal:dfrom',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $from = $this->input('dfrom');
            $to = $this->input('dto');
            if (! $from || ! $to || $validator->errors()->isNotEmpty()) {
                return;
            }
            $max = (int) config('attendance.max_range_days', 366);
            if (Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1 > $max) {
                $validator->errors()->add('dto', "Please choose a date range of at most {$max} days.");
            }
        });
    }

    public function attributes(): array
    {
        return ['dfrom' => 'Date From', 'dto' => 'Date To'];
    }
}
