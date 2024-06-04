<?php

namespace App\Http\Controllers;

use PDO;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\EmployeeRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\TransactionRepository;

class AttendanceController extends Controller
{
    protected $employeeRepository;
    protected $attendanceRepository;
    protected $transactionRepository;

    public function __construct(
        EmployeeRepository $employeeRepository,
        AttendanceRepository $attendanceRepository,
        TransactionRepository $transactionRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->employeeRepository = $employeeRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->transactionRepository = $transactionRepository;
    }
    
    public function index(Request $request) {
        $dto = $request->input('dto');
        $dfrom = $request->input('dfrom');
        $eid = $request->input('employee_id');
        
        if (!empty($dfrom) && !empty($dto)) {
            $data = $this->attendance($eid, $dfrom, $dto);
        } else {
            $data  = null;
        }
    
        $employee = $this->employeeRepository->salary();
        return view('attendance', [
            'eid' => $eid,
            'dto' => $dto,
            'rows' => $data,
            'dfrom' => $dfrom,
            'employee' => $employee,
        ]);
    }

    public function asummary(Request $request) {
        $dto = $request->input('dto');
        $dfrom = $request->input('dfrom');
        $eid = $request->input('employee_id');
        $employee = $this->employeeRepository->salary();
        $transaction = $this->transactionRepository->sAdvance($dfrom, $dto);
        $summary = $this->calculateSummary($eid, $dfrom, $dto);
        return view('asummary', [
            'eid' => $eid,
            'dto' => $dto,
            'dfrom' => $dfrom,
            'summary' => $summary,
            'employee' => $employee,
            'transaction' => $transaction,
        ]);
    }    

    public function workTime(){
        $work = $this->attendanceRepository->workTime();
        return view('workTime', [
            'work' => $work,
        ]);
    }
    
    public function workHoliday(){
        $work = $this->attendanceRepository->workHoliday();
        return view('workHoliday', [
            'work' => $work,
        ]);
    }

    public function store(Request $request){
        $this->attendanceRepository->store($request->input());
        if(isset($request->time_from)){
            return redirect()->route('workTime')->with('success', 'Record Inserted Successfully');
        }else{
            return redirect()->route('workHoliday')->with('success', 'Record Inserted Successfully');
        }
    }

    public function update(Request $request, $id){
        $this->attendanceRepository->update($id, $request->input());
        if(isset($request->time_from)){
            return redirect()->route('workTime')->with('success', 'Record Updated Successfully');
        }else{
            return redirect()->route('workHoliday')->with('success', 'Record Updated Successfully');
        }
    }

    public function create(){}

    public function show(Attendance $attendance){}

    public function edit(Attendance $attendance){}

    public function destroy(Attendance $attendance){}
        
    private function calculateSummary($eid, $dfrom, $dto) {
        $data = $this->attendance($eid, $dfrom, $dto);
        $summary = [];
    
        foreach ($data as $row) {
            if (!isset($summary[$row['USERID']])) {
                $summary[$row['USERID']] = [
                    'NAME' => $row['NAME'],
                    'ENO' => $row['ENO'],
                    'EID' => $row['EID'],
                    'SALARY' => $row['SALARY'], // Include salary
                    'TOTAL_LATE_MINUTES' => 0, // Initialize total late minutes
                    'TOTAL_ABSENTS' => 0,
                    'TOTAL_SINGLE_ATTENDANCE' => 0,
                ];
            }
    
            if ($row['DETAIL'] === 'Absent') {
                $summary[$row['USERID']]['TOTAL_ABSENTS']++;
            } elseif ($row['DETAIL'] === 'Single Attendance') {
                $summary[$row['USERID']]['TOTAL_SINGLE_ATTENDANCE']++;
            }
    
            // Calculate and accumulate total late minutes
            if (strpos($row['LATE_MINUTES'], 'hours') !== false) {
                preg_match('/(\d+) hours (\d+) mins/', $row['LATE_MINUTES'], $matches);
                $totalLateMinutes = $matches[1] * 60 + $matches[2];
            } elseif (strpos($row['LATE_MINUTES'], 'mins') !== false) {
                preg_match('/(\d+) mins/', $row['LATE_MINUTES'], $matches);
                $totalLateMinutes = $matches[1];
            } else {
                $totalLateMinutes = 0;
            }
    
            // Update total late minutes
            $summary[$row['USERID']]['TOTAL_LATE_MINUTES'] += $totalLateMinutes;
        }
    
        // Convert total late minutes to hours and minutes format
        foreach ($summary as &$employeeSummary) {
            $totalLateMinutes = $employeeSummary['TOTAL_LATE_MINUTES'];
    
            // Convert total late minutes to hours and minutes
            $hours = floor($totalLateMinutes / 60);
            $minutes = $totalLateMinutes % 60;
    
            if ($hours > 0 || $minutes > 0) {
                $employeeSummary['TOTAL_LATE'] = sprintf("%d hours %d mins", $hours, $minutes);
            } else {
                $employeeSummary['TOTAL_LATE'] = '0 mins';
            }
    
            // Remove the intermediate total late minutes
            unset($employeeSummary['TOTAL_LATE_MINUTES']);
        }
    
        return $summary;
    }
    
    private function attendance($eid, $dfrom, $dto) {
        // $dbPath = public_path('resources/att2000.mdb');
        $dbPath = 'D:\ZKTeco\att2000.mdb';
    
        if (!file_exists($dbPath)) {
            return [];
        }
    
        try {
            $dsn = "odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)}; DBQ=$dbPath; Uid=; Pwd=;";
            $db = new PDO($dsn);
    
            $sql = "SELECT checkinout.USERID, userinfo.Name, checkinout.CHECKTIME, checkinout.CHECKTYPE
                    FROM checkinout
                    LEFT JOIN userinfo ON checkinout.USERID = userinfo.USERID
                    WHERE checkinout.CHECKTIME BETWEEN ? AND ?";
    
            $params = [$dfrom, $dto];
            if ($eid != 0) {
                $sql .= " AND checkinout.USERID = ?";
                $params[] = $eid;
            }
    
            $sql .= " ORDER BY checkinout.CHECKTIME";
    
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $accessData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($eid == 0) {
                $employees = DB::table('employees')->select('attendance_id', 'name', 'salary', 'employee_id', 'employee_no')->where('employee_type_id', '39')->get();
            } else {
                $employees = DB::table('employees')->select('attendance_id', 'name', 'salary', 'employee_id', 'employee_no')->where('attendance_id', $eid)->where('employee_type_id', '39')->get();
            }
    
            $employeeMap = $employees->keyBy('attendance_id');
            $rows = $this->processAttendanceData($accessData, $employeeMap, $dfrom, $dto);
    
            return $rows;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function processAttendanceData($accessData, $employeeMap, $dfrom = null, $dto = null) {
        $rows = [];
        $dateRange = $this->getDateRange($dfrom, $dto);
        $holidays = $this->getHolidays($dfrom, $dto);
    
        foreach ($dateRange as $date) {
            $holidayDetail = $this->isHoliday($date, $holidays);
    
            foreach ($employeeMap as $userId => $employee) {
                $dayName = Carbon::parse($date)->format('l');
    
                if ($holidayDetail) {
                    $rows[] = [
                        'USERID' => $userId,
                        'ENO' => $employee->employee_no,
                        'EID' => $employee->employee_id,
                        'NAME' => $employee->name,
                        'SALARY' => $employee->salary,
                        'DATE' => $date,
                        'CHECKIN' => 'N/A',
                        'CHECKOUT' => 'N/A',
                        'LATE_MINUTES' => 'N/A',
                        'DETAIL' => $holidayDetail,
                        'HOLIDAY' => '1',
                    ];
                    continue;
                }
    
                if ($this->isSunday($date)) {
                    $rows[] = [
                        'USERID' => $userId,
                        'ENO' => $employee->employee_no,
                        'EID' => $employee->employee_id,
                        'NAME' => $employee->name,
                        'SALARY' => $employee->salary,
                        'DATE' => $date,
                        'CHECKIN' => 'N/A',
                        'CHECKOUT' => 'N/A',
                        'LATE_MINUTES' => 'N/A',
                        'DETAIL' => 'Sunday',
                        'HOLIDAY' => '1',
                    ];
                    continue;
                }
    
                $attendanceRecords = array_filter($accessData, function ($row) use ($userId, $date) {
                    return $row['USERID'] == $userId && date('Y-m-d', strtotime($row['CHECKTIME'])) == $date;
                });
    
                if (empty($attendanceRecords)) {
                    $rows[] = [
                        'USERID' => $userId,
                        'ENO' => $employee->employee_no,
                        'EID' => $employee->employee_id,
                        'NAME' => $employee->name,
                        'SALARY' => $employee->salary,
                        'DATE' => $date,
                        'CHECKIN' => 'N/A',
                        'CHECKOUT' => 'N/A',
                        'LATE_MINUTES' => 'N/A',
                        'DETAIL' => 'Absent',
                        'HOLIDAY' => '0',
                    ];
                    continue;
                }
    
                $checkinTime = 'N/A';
                $checkoutTime = 'N/A';
                $hasCheckIn = false;
                $hasCheckOut = false;
                foreach ($attendanceRecords as $row) {
                    if ($row['CHECKTYPE'] === 'I') {
                        $checkinTime = $row['CHECKTIME'];
                        $hasCheckIn = true;
                    } elseif ($row['CHECKTYPE'] === 'O') {
                        $checkoutTime = $row['CHECKTIME'];
                        $hasCheckOut = true;
                    }
                }
    
                $workTime = $this->getWorkTimes($date);
                $startTime = strtotime($date . ' ' . $workTime->time_from);
                $endTime = strtotime($date . ' ' . $workTime->time_to);
                $graceTime = $workTime->grace_time;
    
                $lateMinutes = 0;
                if ($hasCheckIn) {
                    $lateMinutes = max(0, (strtotime($checkinTime) - $startTime - $graceTime * 60) / 60);
                }
    
                if (!$hasCheckIn || !$hasCheckOut) {
                    $lateMinutes = 0;
                }
    
                $rows[] = [
                    'USERID' => $userId,
                    'ENO' => $employee->employee_no,
                    'EID' => $employee->employee_id,
                    'NAME' => $employee->name,
                    'SALARY' => $employee->salary,
                    'DATE' => $date,
                    'CHECKIN' => $checkinTime,
                    'CHECKOUT' => $checkoutTime,
                    'LATE_MINUTES' => $this->formatLateMinutes($lateMinutes),
                    'DETAIL' => $hasCheckIn && $hasCheckOut ? 'Present' : 'Single Attendance',
                    'HOLIDAY' => '0',
                ];
            }
        }
    
        return $rows;
    }
     
    private function formatLateMinutes($lateMinutes) {
        $lateMinutes = intval($lateMinutes);
        $hours = floor($lateMinutes / 60);
        $minutes = $lateMinutes % 60;
    
        if ($hours > 0) {
            return sprintf("%d hours %d mins", $hours, $minutes);
        } else {
            return sprintf("%d mins", $minutes);
        }
    }
    
    private function getWorkTimes($date) {
        return DB::table('work_times')
            ->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->first();
    }
    
    private function getHolidaysOld($dfrom, $dto) {
        return DB::table('work_holidays')
            ->whereDate('date_from', '<=', $dto)
            ->whereDate('date_to', '>=', $dfrom)
            ->get();
    }

    private function getHolidays($dfrom, $dto) {
        try {
            return DB::table('work_holidays')
                ->whereDate('date_from', '<=', $dto)
                ->whereDate('date_to', '>=', $dfrom)
                ->get();
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Error retrieving holidays: ' . $e->getMessage());
            // Return an empty collection or handle the error appropriately
            return collect([]);
        }
    }
    
    
    private function isHoliday($date, $holidays) {
        foreach ($holidays as $holiday) {
            if ($holiday->date_from <= $date && $holiday->date_to >= $date) {
                return $holiday->description;
            }
        }
        return false;
    }
    
    private function isSunday($date) {
        return Carbon::parse($date)->isSunday();
    }
    
    private function getDateRange($start, $end) {
        $dateRange = [];
        $current = Carbon::parse($start);
        $end = Carbon::parse($end);
    
        while ($current <= $end) {
            $dateRange[] = $current->toDateString();
            $current->addDay();
        }
    
        return $dateRange;
    }
}