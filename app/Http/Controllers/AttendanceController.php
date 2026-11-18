<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceFilterRequest;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $employeeRepository;

    protected $attendanceRepository;

    protected $transactionRepository;

    public function __construct(
        EmployeeRepository $employeeRepository,
        AttendanceRepository $attendanceRepository,
        TransactionRepository $transactionRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->employeeRepository = $employeeRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(AttendanceFilterRequest $request)
    {
        $this->authorize('access', Attendance::class);
        $filters = $request->validated();
        $dto = $filters['dto'] ?? null;
        $dfrom = $filters['dfrom'] ?? null;
        $eid = (int) ($filters['employee_id'] ?? 0);

        $result = ['rows' => null, 'warnings' => [], 'error' => null];
        if (! empty($dfrom) && ! empty($dto)) {
            $result = $this->attendance($eid, $dfrom, $dto);
        }

        return view('attendance', [
            'eid' => $eid,
            'dto' => $dto,
            'rows' => $result['rows'],
            'warnings' => $result['warnings'],
            'machineError' => $result['error'],
            'machineDb' => $this->attendanceRepository->machineDbStatus(),
            'dfrom' => $dfrom,
            'employee' => $this->employeeRepository->salary(),
        ]);
    }

    public function asummary(AttendanceFilterRequest $request)
    {
        $this->authorize('access', Attendance::class);
        $filters = $request->validated();
        $dto = $filters['dto'] ?? null;
        $dfrom = $filters['dfrom'] ?? null;
        $eid = (int) ($filters['employee_id'] ?? 0);

        $result = ['rows' => [], 'warnings' => [], 'error' => null];
        if (! empty($dfrom) && ! empty($dto)) {
            $result = $this->attendance($eid, $dfrom, $dto);
        }

        return view('asummary', [
            'eid' => $eid,
            'dto' => $dto,
            'dfrom' => $dfrom,
            'summary' => $this->calculateSummary($result['rows'] ?? []),
            'warnings' => $result['warnings'],
            'machineError' => $result['error'],
            'employee' => $this->employeeRepository->salary(),
            'transaction' => $this->transactionRepository->sAdvance($dfrom, $dto),
        ]);
    }

    public function workTime()
    {
        $this->authorize('access', Attendance::class);
        $work = $this->attendanceRepository->workTime();

        return view('workTime', [
            'work' => $work,
        ]);
    }

    public function workHoliday()
    {
        $this->authorize('access', Attendance::class);
        $work = $this->attendanceRepository->workHoliday();

        return view('workHoliday', [
            'work' => $work,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Attendance::class);
        $this->attendanceRepository->store($request->input());
        if (isset($request->time_from)) {
            return redirect()->route('workTime')->with('success', 'Record Inserted Successfully');
        } else {
            return redirect()->route('workHoliday')->with('success', 'Record Inserted Successfully');
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Attendance::class);
        $this->attendanceRepository->update($id, $request->input());
        if (isset($request->time_from)) {
            return redirect()->route('workTime')->with('success', 'Record Updated Successfully');
        } else {
            return redirect()->route('workHoliday')->with('success', 'Record Updated Successfully');
        }
    }

    /**
     * Delete a Work Hours row ($type = time) or a Non Working Day ($type = holiday).
     * Nothing references these rows; attendance pages simply stop using them.
     */
    public function destroy($type, $id)
    {
        $this->authorize('delete', Attendance::class);
        $type = $type === 'holiday' ? 'holiday' : 'time';
        $this->attendanceRepository->delete($id, $type);

        return redirect()->route($type === 'holiday' ? 'workHoliday' : 'workTime')->with('success', 'Record Deleted Successfully');
    }

    /**
     * Totals per employee (keyed by employee, so two employees sharing a machine number stay separate).
     */
    private function calculateSummary(array $rows): array
    {
        $summary = [];

        foreach ($rows as $row) {
            $key = $row['EID'];
            if (! isset($summary[$key])) {
                $summary[$key] = [
                    'NAME' => $row['NAME'],
                    'ENO' => $row['ENO'],
                    'EID' => $row['EID'],
                    'SALARY' => $row['SALARY'],
                    'TOTAL_LATE_MINUTES' => 0,
                    'TOTAL_OVERTIME_MINUTES' => 0,
                    'TOTAL_ABSENTS' => 0,
                    'TOTAL_SINGLE_ATTENDANCE' => 0,
                ];
            }

            if ($row['DETAIL'] === 'Absent') {
                $summary[$key]['TOTAL_ABSENTS']++;
            } elseif ($row['DETAIL'] === 'Single Attendance') {
                $summary[$key]['TOTAL_SINGLE_ATTENDANCE']++;
            }

            $summary[$key]['TOTAL_LATE_MINUTES'] += (int) ($row['LATE_VALUE'] ?? 0);
            $summary[$key]['TOTAL_OVERTIME_MINUTES'] += (int) ($row['OVERTIME_VALUE'] ?? 0);
        }

        // Late time and overtime in hours (2 decimals), as entered on the payroll sheet
        foreach ($summary as &$employeeSummary) {
            $employeeSummary['TOTAL_LATE'] = $this->attendanceRepository->hours($employeeSummary['TOTAL_LATE_MINUTES']);
            $employeeSummary['TOTAL_OVERTIME'] = $this->attendanceRepository->hours($employeeSummary['TOTAL_OVERTIME_MINUTES']);
            unset($employeeSummary['TOTAL_LATE_MINUTES'], $employeeSummary['TOTAL_OVERTIME_MINUTES']);
        }

        return $summary;
    }

    /**
     * Daily attendance rows of salaried employees (all, or one) for a date range; the
     * calculation lives in AttendanceRepository::dailyAttendance() (also used by the payroll).
     *
     * @return array{rows: array, warnings: array<int, string>, error: ?string}
     */
    private function attendance(int $employeeId, string $dfrom, string $dto): array
    {
        $employees = $this->employeeRepository->salary()
            ->when($employeeId > 0, fn ($list) => $list->where('employee_id', $employeeId))
            ->sortBy('employee_no')
            ->values();

        return $this->attendanceRepository->dailyAttendance($employees, $dfrom, $dto);
    }
}
