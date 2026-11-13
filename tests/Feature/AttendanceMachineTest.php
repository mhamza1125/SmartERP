<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Attendance machine database (public/resources/att2000.mdb) + Settings path.
 * Uses DatabaseTransactions: nothing is kept in the database.
 */
class AttendanceMachineTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role_id', User::ROLE_ADMIN)->firstOrFail();
    }

    private function useDefaultPath(): void
    {
        Company::query()->update(['attendance_db_path' => null]);
    }

    public function test_default_database_is_found_and_read(): void
    {
        $this->useDefaultPath();
        $repo = app(AttendanceRepository::class);
        $this->assertTrue($repo->machineDbStatus()['ok'], (string) $repo->machineDbStatus()['message']);

        $punches = $repo->machinePunches('2024-03-04', '2024-03-07', ['24']);
        // user 24: 8 punches between 04-03 and 07-03 (the last day must be included)
        $this->assertCount(8, $punches);
        $this->assertSame('2024-03-07 18:37:18', end($punches)['time']);
    }

    public function test_first_and_last_punch_are_check_in_and_out(): void
    {
        $this->useDefaultPath();
        $employee = DB::table('employees')->where('employee_type_id', 39)->first();
        DB::table('employees')->where('employee_id', $employee->employee_id)->update(['attendance_id' => '24']);

        $response = $this->actingAs($this->admin())->post(route('attendance.filter'), [
            'employee_id' => $employee->employee_id, 'dfrom' => '2024-03-05', 'dto' => '2024-03-05',
        ]);
        $response->assertOk();
        $rows = $response->viewData('rows');
        $this->assertCount(1, $rows);
        // 08:21 IN and 18:28 IN (wrong key pressed in the evening) => present, in 08:21, out 18:28
        $this->assertSame('2024-03-05 08:21:25', $rows[0]['CHECKIN']);
        $this->assertSame('2024-03-05 18:28:25', $rows[0]['CHECKOUT']);
        $this->assertSame('Present', $rows[0]['DETAIL']);
    }

    public function test_duplicate_and_missing_machine_ids_are_reported_not_dropped(): void
    {
        $this->useDefaultPath();
        $ids = DB::table('employees')->where('employee_type_id', 39)->orderBy('employee_id')->limit(3)->pluck('employee_id');
        DB::table('employees')->whereIn('employee_id', [$ids[0], $ids[1]])->update(['attendance_id' => '24']);
        DB::table('employees')->where('employee_id', $ids[2])->update(['attendance_id' => null]);

        $response = $this->actingAs($this->admin())->post(route('attendance.filter'), [
            'employee_id' => 0, 'dfrom' => '2024-03-05', 'dto' => '2024-03-05',
        ]);
        $response->assertOk();
        $rows = collect($response->viewData('rows'));
        $this->assertSame(2, $rows->whereIn('EID', [$ids[0], $ids[1]])->count(), 'both employees sharing ID 24 are listed');
        $this->assertSame(0, $rows->where('EID', $ids[2])->count());
        $warnings = implode(' ', $response->viewData('warnings'));
        $this->assertStringContainsString('Attendance No 24 is entered on more than one employee', $warnings);
        $this->assertStringContainsString('No Attendance No', $warnings);
    }

    public function test_missing_file_is_reported_gracefully(): void
    {
        Company::query()->update(['attendance_db_path' => 'public/resources/does-not-exist.mdb']);

        $response = $this->actingAs($this->admin())->post(route('attendance.filter'), [
            'employee_id' => 0, 'dfrom' => '2024-03-05', 'dto' => '2024-03-05',
        ]);
        $response->assertOk();
        $this->assertStringContainsString('was not found', (string) $response->viewData('machineError'));
        $response->assertSee('Attendance records could not be read');
    }

    public function test_settings_validates_and_saves_the_path(): void
    {
        $company = Company::firstOrFail();
        $base = collect($company->only(['name', 'ceo', 'phone', 'email']))->all();

        $this->actingAs($this->admin())
            ->from(route('company.edit', $company->id))
            ->post(route('company.update', $company->id), $base + ['attendance_db_path' => 'C:\Windows\win.ini'])
            ->assertSessionHasErrors('attendance_db_path');

        $this->actingAs($this->admin())
            ->post(route('company.update', $company->id), $base + ['attendance_db_path' => 'public/resources/../resources/att2000.mdb'])
            ->assertSessionHasErrors('attendance_db_path');

        $this->actingAs($this->admin())
            ->post(route('company.update', $company->id), $base + ['attendance_db_path' => public_path('resources/att2000.mdb')])
            ->assertSessionHasNoErrors();
        $this->assertSame(public_path('resources/att2000.mdb'), $company->fresh()->attendance_db_path);
        $this->assertTrue(app(AttendanceRepository::class)->machineDbStatus()['ok']);
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('attendance.filter'), ['employee_id' => 0, 'dfrom' => '2024-03-10', 'dto' => '2024-03-01'])
            ->assertSessionHasErrors('dto');
    }
}
