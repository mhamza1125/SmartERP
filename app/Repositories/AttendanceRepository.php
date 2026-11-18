<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\WorkHoliday;
use App\Models\WorkTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

class AttendanceRepository implements GlobalInterface
{
    public function all()
    {
        return WorkTime::orderBy('created_at', 'desc')->get();
    }

    public function workTime()
    {
        return WorkTime::orderBy('created_at', 'desc')->get();
    }

    /**
     * Working hours set on department heads (head type 3), keyed by head_id: work_start_time /
     * work_end_time ('H:i:s' or null). A department without them uses Payroll > Work Hours.
     */
    public function departmentHours()
    {
        return \App\Models\Head::where('head_type_id', HeadRepository::DEPARTMENT_TYPE)
            ->where(fn ($q) => $q->whereNotNull('work_start_time')->orWhereNotNull('work_end_time'))
            ->get(['head_id', 'work_start_time', 'work_end_time'])
            ->keyBy('head_id');
    }

    public function workHoliday()
    {
        return WorkHoliday::orderBy('created_at', 'desc')->get();
    }

    public function get($id)
    {
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        if (isset($data['time_from'])) {
            $store = WorkTime::create($data);
        } else {
            $store = WorkHoliday::create($data);
        }

        return $store->id;
    }

    public function update($id, array $data)
    {
        if (isset($data['time_from'])) {
            $update = WorkTime::findOrFail($id);
        } else {
            $update = WorkHoliday::findOrFail($id);
        }
        $update->update($data);

        return $update->id;
    }

    /**
     * Work hours / non-working days are settings rows: nothing else references them.
     * $data['type'] is 'time' (work_times) or 'holiday' (work_holidays).
     */
    public function delete($id, string $type = 'time')
    {
        $model = $type === 'holiday' ? WorkHoliday::findOrFail($id) : WorkTime::findOrFail($id);

        return $model->delete();
    }

    // =====================================================================
    // Attendance machine database (ZKTeco ATT2000.mdb)
    // =====================================================================

    /**
     * The path stored in Settings > Company, or the default from config/attendance.php.
     */
    public function machineDbSetting(): string
    {
        $saved = trim((string) Company::query()->value('attendance_db_path'));

        return $saved !== '' ? $saved : (string) config('attendance.default_path');
    }

    /**
     * Absolute path for a configured value (relative paths are under the project root).
     */
    public function resolveMachineDbPath(string $path): string
    {
        $path = trim($path);
        $isAbsolute = preg_match('/^([a-zA-Z]:[\\\\\/]|[\\\\\/])/', $path) === 1;

        return $isAbsolute ? $path : base_path(ltrim($path, '\\/'));
    }

    /**
     * Why a path cannot be used as the attendance database, or null when it can.
     * Used by Settings validation and before every read.
     */
    public function machineDbProblem(string $path): ?string
    {
        if (trim($path) === '') {
            return 'No attendance database file is configured.';
        }
        if (str_contains($path, "\0") || preg_match('/(^|[\\\\\/])\.\.([\\\\\/]|$)/', $path)) {
            return 'The attendance database path must not contain "..".';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($extension, config('attendance.allowed_extensions', ['mdb', 'accdb']), true)) {
            return 'The attendance database must be a .mdb or .accdb file.';
        }

        $absolute = $this->resolveMachineDbPath($path);
        if (! is_file($absolute)) {
            return 'The attendance database file was not found at: '.$path;
        }
        if (! is_readable($absolute)) {
            return 'The attendance database file cannot be read (check file permissions): '.$path;
        }

        $real = realpath($absolute);
        if (! $this->insideAllowedDirs($real)) {
            return 'The attendance database must be inside an allowed folder (ATTENDANCE_DB_ALLOWED_DIRS in .env).';
        }

        return null;
    }

    /**
     * Status of the configured database for the attendance pages / settings form.
     *
     * @return array{setting: string, path: string, ok: bool, message: ?string}
     */
    public function machineDbStatus(): array
    {
        $setting = $this->machineDbSetting();
        $problem = $this->machineDbProblem($setting);

        return [
            'setting' => $setting,
            'path' => $this->resolveMachineDbPath($setting),
            'ok' => $problem === null,
            'message' => $problem,
        ];
    }

    /**
     * Every punch in [$dfrom 00:00, $dto 23:59:59], optionally only for some machine numbers.
     * The machine number is USERINFO.Badgenumber (the number enrolled on the device, which is
     * what is entered as the employee's Attendance No); CHECKINOUT.USERID is the device's
     * internal key, so punches are joined through USERINFO.
     *
     * @param  array<int, string>|null  $badges
     * @return array<int, array{badge: string, time: string, type: string}>
     *
     * @throws RuntimeException when the database cannot be opened or read (message is user-safe)
     */
    public function machinePunches(string $dfrom, string $dto, ?array $badges = null): array
    {
        $status = $this->machineDbStatus();
        if (! $status['ok']) {
            throw new RuntimeException($status['message']);
        }
        if (! in_array('odbc', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException('The PHP PDO ODBC extension is not enabled on the server, so the attendance database cannot be read.');
        }

        $from = Carbon::parse($dfrom)->startOfDay()->format('Y-m-d H:i:s');
        $to = Carbon::parse($dto)->addDay()->startOfDay()->format('Y-m-d H:i:s');

        try {
            $path = realpath($status['path']);
            $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)}; DBQ={$path}; Uid=; Pwd=;");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = 'SELECT USERINFO.Badgenumber AS badge, CHECKINOUT.CHECKTIME AS checktime, CHECKINOUT.CHECKTYPE AS checktype
                    FROM CHECKINOUT INNER JOIN USERINFO ON CHECKINOUT.USERID = USERINFO.USERID
                    WHERE CHECKINOUT.CHECKTIME >= ? AND CHECKINOUT.CHECKTIME < ?
                    ORDER BY CHECKINOUT.CHECKTIME';
            $stmt = $db->prepare($sql);
            $stmt->execute([$from, $to]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            Log::error('Attendance database read failed: '.$e->getMessage(), ['path' => $status['path']]);

            throw new RuntimeException('The attendance database could not be opened. Check that the file is a valid ATT2000 database and that the Microsoft Access ODBC driver is installed.');
        }

        $wanted = $badges === null ? null : array_flip(array_map([$this, 'normalizeBadge'], $badges));
        $punches = [];
        foreach ($rows as $row) {
            $badge = $this->normalizeBadge($row['badge'] ?? '');
            $time = $row['checktime'] ?? null;
            if ($badge === '' || empty($time) || strtotime($time) === false) {
                continue; // invalid record
            }
            if ($wanted !== null && ! isset($wanted[$badge])) {
                continue;
            }
            $punches[] = [
                'badge' => $badge,
                'time' => Carbon::parse($time)->format('Y-m-d H:i:s'),
                'type' => strtoupper(trim((string) ($row['checktype'] ?? ''))),
            ];
        }

        return $punches;
    }

    /**
     * Machine numbers are compared as trimmed text without leading zeros ("007" = "7").
     */
    public function normalizeBadge($badge): string
    {
        $badge = trim((string) $badge);
        $stripped = ltrim($badge, '0');

        return $stripped === '' && $badge !== '' ? '0' : $stripped;
    }

    private function insideAllowedDirs(string $realFile): bool
    {
        $dirs = config('attendance.allowed_dirs', []);
        if (empty($dirs)) {
            return true;
        }
        // The shipped default location always stays usable
        $dirs[] = public_path('resources');

        $file = strtolower(str_replace('\\', '/', $realFile));
        foreach ($dirs as $dir) {
            $realDir = realpath($this->resolveMachineDbPath($dir));
            if ($realDir === false) {
                continue;
            }
            $prefix = rtrim(strtolower(str_replace('\\', '/', $realDir)), '/').'/';
            if (str_starts_with($file, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Daily attendance rows of the given (salaried) employees for a date range, read from the
     * attendance machine database configured in Settings > Company. Used by the attendance pages
     * and the payroll sheet (monthTotals).
     *
     * @param  \Illuminate\Support\Collection  $employees  rows with employee_id, employee_no, name, salary, attendance_id, department_id
     * @return array{rows: array, warnings: array<int, string>, error: ?string}
     */
    public function dailyAttendance($employees, string $dfrom, string $dto): array
    {
        $warnings = [];

        // Employees without a machine number cannot be matched with the device
        $unmapped = $employees->filter(fn ($e) => trim((string) $e->attendance_id) === '');
        if ($unmapped->isNotEmpty()) {
            $warnings[] = 'No Attendance No (machine ID) is set for: '
                .$unmapped->map(fn ($e) => $e->employee_no.' - '.$e->name)->implode(', ')
                .'. Add it on the employee\'s edit page to show their attendance.';
        }
        $mapped = $employees->reject(fn ($e) => trim((string) $e->attendance_id) === '')->values();

        // One machine number entered on several employees: every one of them gets the same punches
        $duplicates = $mapped->groupBy(fn ($e) => $this->normalizeBadge($e->attendance_id))
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
            $punches = $this->machinePunches($dfrom, $dto, $badges);
        } catch (RuntimeException $e) {
            return ['rows' => [], 'warnings' => $warnings, 'error' => $e->getMessage()];
        }

        $rows = $this->processAttendanceData($punches, $mapped, $dfrom, $dto, $warnings);

        return ['rows' => $rows, 'warnings' => $warnings, 'error' => null];
    }

    /**
     * A month's late time and overtime per employee from the attendance machine, in hours
     * (2 decimals), with each employee's department working hours (dailyAttendance rules).
     * Used to fill the payroll sheet; worked days, leaves and advances are not read.
     *
     * @return array{totals: array<int, array{late_hours: float, overtime_hours: float}>, warnings: array<int, string>, error: ?string}
     */
    public function monthTotals($employees, string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $result = $this->dailyAttendance($employees, $start->toDateString(), $start->copy()->endOfMonth()->toDateString());

        $minutes = [];
        foreach ($result['rows'] as $row) {
            $minutes[$row['EID']]['late'] = ($minutes[$row['EID']]['late'] ?? 0) + (int) ($row['LATE_VALUE'] ?? 0);
            $minutes[$row['EID']]['overtime'] = ($minutes[$row['EID']]['overtime'] ?? 0) + (int) ($row['OVERTIME_VALUE'] ?? 0);
        }
        $totals = array_map(fn ($m) => [
            'late_hours' => round($m['late'] / 60, 2),
            'overtime_hours' => round($m['overtime'] / 60, 2),
        ], $minutes);

        return ['totals' => $totals, 'warnings' => $result['warnings'], 'error' => $result['error']];
    }

    /**
     * One row per employee per day. Check-in = the day's first punch, check-out = its last punch.
     * The device's IN / OUT key (CHECKTYPE) is not used for pairing because staff often press the
     * wrong key (e.g. an evening punch recorded as IN); it only decides whether a lone punch is
     * shown as a check-in or a check-out. Repeated punches within a few seconds count once.
     *
     * Working hours per employee: their department head's start / end time (Settings > Heads >
     * Departments), else the day's Payroll > Work Hours. Late time = check-in after start + grace
     * time; overtime = check-out after the end (an end at or before the start is the next day).
     * Both only on days with a check-in and a check-out (existing rule for late time).
     */
    private function processAttendanceData(array $punches, $employees, string $dfrom, string $dto, array &$warnings): array
    {
        $rows = [];
        $dateRange = $this->getDateRange($dfrom, $dto);
        $holidays = $this->getHolidays($dfrom, $dto);
        $gap = (int) config('attendance.duplicate_punch_seconds', 120);
        $departmentHours = $this->departmentHours();

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
                $badge = $this->normalizeBadge($employee->attendance_id);
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
                    'OVERTIME' => 'N/A',
                    'OVERTIME_VALUE' => 0,
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

                // Late time and overtime: only on days with both punches (existing rule)
                $lateMinutes = 0;
                $overtimeMinutes = 0;
                if ($checkin && $checkout) {
                    $department = $departmentHours->get($employee->department_id);
                    $startTime = $department->work_start_time ?? $workTime->time_from ?? null;
                    $endTime = $department->work_end_time ?? $workTime->time_to ?? null;
                    if ($startTime) {
                        // Grace time comes from Work Hours (none when only the department has hours)
                        $start = Carbon::parse($date.' '.$startTime)->addMinutes((int) ($workTime->grace_time ?? 0));
                        $lateMinutes = max(0, intdiv(Carbon::parse($checkin)->getTimestamp() - $start->getTimestamp(), 60));
                    }
                    if ($endTime) {
                        $end = Carbon::parse($date.' '.$endTime);
                        if ($startTime && $end->lte(Carbon::parse($date.' '.$startTime))) {
                            $end->addDay(); // night shift ends the next day
                        }
                        $overtimeMinutes = max(0, intdiv(Carbon::parse($checkout)->getTimestamp() - $end->getTimestamp(), 60));
                    }
                    if (! $startTime || ! $endTime) {
                        $missingWorkTime[$date] = true;
                    }
                }

                $rows[] = array_merge($base, [
                    'CHECKIN' => $checkin ?? 'N/A',
                    'CHECKOUT' => $checkout ?? 'N/A',
                    'LATE_MINUTES' => $this->hours($lateMinutes),
                    'LATE_VALUE' => $lateMinutes,
                    'OVERTIME' => $this->hours($overtimeMinutes),
                    'OVERTIME_VALUE' => $overtimeMinutes,
                    'DETAIL' => $checkin && $checkout ? 'Present' : 'Single Attendance',
                    'HOLIDAY' => '0',
                ]);
            }
        }

        if ($missingWorkTime) {
            $warnings[] = 'No Work Hours are defined for '.count($missingWorkTime).' working day(s) in this range ('
                .collect(array_keys($missingWorkTime))->take(5)->map(fn ($d) => formatDate($d))->implode(', ')
                .(count($missingWorkTime) > 5 ? ', ...' : '').'), so late time / overtime was not calculated for them. Add them under Payroll > Work Hours, or set the department\'s working hours under Settings > Heads > Departments.';
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

    /** Minutes shown as hours with 2 decimals (75 -> "1.25"), the unit used on the payroll sheet. */
    public function hours($minutes): string
    {
        return number_format((int) $minutes / 60, 2);
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
