<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceFilterRequest;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
        }

        foreach ($summary as &$employeeSummary) {
            $employeeSummary['TOTAL_LATE'] = $this->formatLateMinutes($employeeSummary['TOTAL_LATE_MINUTES']);
            unset($employeeSummary['TOTAL_LATE_MINUTES']);
        }

        return $summary;
    }

    /**
     * Daily attendance rows of salaried employees for a date range, read from the attendance
     * machine database configured in Settings > Company.
     *
     * @return array{rows: array, warnings: array<int, string>, error: ?string}
     */
    private function attendance(int $employeeId, string $dfrom, string $dto): array
    {
        $warnings = [];

        $employees = $this->employeeRepository->salary()
            ->when($employeeId > 0, fn ($list) => $list->where('employee_id', $employeeId))
            ->sortBy('employee_no')
            ->values();

        // Employees without a machine number cannot be matched with the device
        $unmapped = $employees->filter(fn ($e) => trim((string) $e->attendance_id) === '');
        if ($unmapped->isNotEmpty()) {
            $warnings[] = 'No Attendance No (machine ID) is set for: '
                .$unmapped->map(fn ($e) => $e->employee_no.' - '.$e->name)->implode(', ')
                .'. Add it on the employee\'s edit page to show their attendance.';
        }
        $mapped = $employees->reject(fn ($e) => trim((string) $e->attendance_id) === '')->values();

        // One machine number entered on several employees: every one of them gets the same punches
        $duplicates = $mapped->groupBy(fn ($e) => $this->attendanceRepository->normalizeBadge($e->attendance_id))
            ->filter(fn ($group) => $group->count() > 1);
        foreach ($duplicates as $badge => $group) {
            $warnings[] = 'Attendance No '.$badge.' is entered on more than one employee ('
                .$group->map(fn ($e) => $e->employee_no.' - '.$e->name)->implode(', ')
                .'). Each machine ID should belong to one employee.';
        }

        if ($mapped->isEmpty()) {
            return ['rows' => [], 'warnings' => $warnings, 'error' => null];
        }

        try {
            $badges = $mapped->map(fn ($e) => (string) $e->attendance_id)->all();
            $punches = $this->attendanceRepository->machinePunches($dfrom, $dto, $badges);
        } catch (RuntimeException $e) {
            return ['rows' => [], 'warnings' => $warnings, 'error' => $e->getMessage()];
        }

        $rows = $this->processAttendanceData($punches, $mapped, $dfrom, $dto, $warnings);

        return ['rows' => $rows, 'warnings' => $warnings, 'error' => null];
    }

    /**
     * One row per employee per day. Check-in = the day's first punch, check-out = its last punch.
     * The device's IN / OUT key (CHECKTYPE) is not used for pairing because staff often press the
     * wrong key (e.g. an evening punch recorded as IN); it only decides whether a lone punch is
     * shown as a check-in or a check-out. Repeated punches within a few seconds count once.
     */
    private function processAttendanceData(array $punches, $employees, string $dfrom, string $dto, array &$warnings): array
    {
        $rows = [];
        $dateRange = $this->getDateRange($dfrom, $dto);
        $holidays = $this->getHolidays($dfrom, $dto);
        $gap = (int) config('attendance.duplicate_punch_seconds', 120);

        // [badge][date] => punches of that day, in time order
        $byDay = [];
        foreach ($punches as $punch) {
            $byDay[$punch['badge']][substr($punch['time'], 0, 10)][] = $punch;
        }

        $missingWorkTime = [];

        foreach ($dateRange as $date) {
            $holidayDetail = $this->isHoliday($date, $holidays);
            $isSunday = $this->isSunday($date);
            $workTime = ($holidayDetail || $isSunday) ? null : $this->getWorkTimes($date);

            foreach ($employees as $employee) {
                $badge = $this->attendanceRepository->normalizeBadge($employee->attendance_id);
                $base = [
                    'USERID' => $badge,
                    'ENO' => $employee->employee_no,
                    'EID' => $employee->employee_id,
                    'NAME' => $employee->name,
                    'SALARY' => $employee->salary,
                    'DATE' => $date,
                    'CHECKIN' => 'N/A',
                    'CHECKOUT' => 'N/A',
                    'LATE_MINUTES' => 'N/A',
                    'LATE_VALUE' => 0,
                ];

                if ($holidayDetail || $isSunday) {
                    $rows[] = $base + ['DETAIL' => $holidayDetail ?: 'Sunday', 'HOLIDAY' => '1'];

                    continue;
                }

                $dayPunches = $this->withoutRepeats($byDay[$badge][$date] ?? [], $gap);
                if (empty($dayPunches)) {
                    $rows[] = $base + ['DETAIL' => 'Absent', 'HOLIDAY' => '0'];

                    continue;
                }

                $first = $dayPunches[0];
                $last = $dayPunches[count($dayPunches) - 1];
                if (count($dayPunches) >= 2) {
                    $checkin = $first['time'];
                    $checkout = $last['time'];
                } elseif ($first['type'] === 'O') {
                    $checkin = null;
                    $checkout = $first['time'];
                } else {
                    $checkin = $first['time'];
                    $checkout = null;
                }

                // Late time: only on days with both punches (existing rule), after the grace time
                $lateMinutes = 0;
                if ($checkin && $checkout) {
                    if ($workTime) {
                        $start = Carbon::parse($date.' '.$workTime->time_from)->addMinutes((int) $workTime->grace_time);
                        $lateMinutes = max(0, intdiv(Carbon::parse($checkin)->getTimestamp() - $start->getTimestamp(), 60));
                    } else {
                        $missingWorkTime[$date] = true;
                    }
                }

                $rows[] = array_merge($base, [
                    'CHECKIN' => $checkin ?? 'N/A',
                    'CHECKOUT' => $checkout ?? 'N/A',
                    'LATE_MINUTES' => $this->formatLateMinutes($lateMinutes),
                    'LATE_VALUE' => $lateMinutes,
                    'DETAIL' => $checkin && $checkout ? 'Present' : 'Single Attendance',
                    'HOLIDAY' => '0',
                ]);
            }
        }

        if ($missingWorkTime) {
            $warnings[] = 'No Work Hours are defined for '.count($missingWorkTime).' working day(s) in this range ('
                .collect(array_keys($missingWorkTime))->take(5)->map(fn ($d) => formatDate($d))->implode(', ')
                .(count($missingWorkTime) > 5 ? ', ...' : '').'), so late time was not calculated for them. Add them under Payroll > Work Hours.';
        }

        return $rows;
    }

    /**
     * Drops punches made within $gap seconds of the previous kept punch (double presses).
     */
    private function withoutRepeats(array $punches, int $gap): array
    {
        usort($punches, fn ($a, $b) => strcmp($a['time'], $b['time']));
        $kept = [];
        $lastTs = null;
        foreach ($punches as $punch) {
            $ts = strtotime($punch['time']);
            if ($lastTs !== null && $ts - $lastTs < $gap) {
                continue;
            }
            $kept[] = $punch;
            $lastTs = $ts;
        }

        return $kept;
    }

    private function formatLateMinutes($lateMinutes)
    {
        $lateMinutes = intval($lateMinutes);
        $hours = floor($lateMinutes / 60);
        $minutes = $lateMinutes % 60;

        if ($hours > 0) {
            return sprintf('%d hours %d mins', $hours, $minutes);
        }

        return sprintf('%d mins', $minutes);
    }

    private function getWorkTimes($date)
    {
        return DB::table('work_times')
            ->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->orderByDesc('work_time_id')
            ->first();
    }

    private function getHolidays($dfrom, $dto)
    {
        try {
            return DB::table('work_holidays')
                ->whereDate('date_from', '<=', $dto)
                ->whereDate('date_to', '>=', $dfrom)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error retrieving holidays: '.$e->getMessage());

            return collect([]);
        }
    }

    private function isHoliday($date, $holidays)
    {
        foreach ($holidays as $holiday) {
            if ($holiday->date_from <= $date && $holiday->date_to >= $date) {
                return $holiday->description ?: 'Holiday';
            }
        }

        return false;
    }

    private function isSunday($date)
    {
        return Carbon::parse($date)->isSunday();
    }

    private function getDateRange($start, $end)
    {
        $dateRange = [];
        $current = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->startOfDay();

        while ($current <= $end) {
            $dateRange[] = $current->toDateString();
            $current->addDay();
        }

        return $dateRange;
    }
}
