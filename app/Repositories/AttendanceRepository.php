<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\WorkHoliday;
use App\Models\WorkTime;
use Carbon\Carbon;
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
}
