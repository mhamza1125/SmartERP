<?php

namespace App\Http\Requests;

use App\Repositories\HeadRepository;
use Illuminate\Foundation\Http\FormRequest;

class HeadRequest extends FormRequest
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
            'head_type_id' => 'required',
            'name' => 'required|max:255',
            // Department heads: working hours (late time / overtime of the department's employees)
            'work_start_time' => 'nullable|date_format:H:i,H:i:s',
            'work_end_time' => 'nullable|date_format:H:i,H:i:s',
            // Department break, entered as hours + minutes (stored as heads.break_minutes)
            'break_hours' => 'nullable|integer|min:0|max:23',
            'break_mins' => 'nullable|integer|min:0|max:59',
            'head_status' => 'nullable|in:0,1',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty() || (int) $this->input('head_type_id') !== HeadRepository::DEPARTMENT_TYPE) {
                return;
            }
            $break = (int) $this->input('break_hours') * 60 + (int) $this->input('break_mins');
            $start = $this->input('work_start_time');
            $end = $this->input('work_end_time');
            if ((bool) $start !== (bool) $end) {
                $v->errors()->add('work_end_time', 'Enter both the work start and end time (or neither).');
            } elseif ($break > 0 && ! $start) {
                $v->errors()->add('break_hours', 'Enter the work start and end time before the break.');
            } elseif ($start && HeadRepository::workingHours($start, $end, $break) === null) {
                $v->errors()->add('break_hours', 'The break must be shorter than the time from work start to work end.');
            }
        });
    }

    public function attributes(): array
    {
        return ['break_hours' => 'break hours', 'break_mins' => 'break minutes'];
    }
}
