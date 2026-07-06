<?php

namespace Tests\Feature;

use App\Models\AttendanceSubmission;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_usage_guide_is_available_from_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Panduan Penggunaan');

        $this->get(route('guide'))
            ->assertOk()
            ->assertSee('Langkah Cepat dari Login sampai Selesai')
            ->assertSee('Cara Pakai Nomor Urut Cepat')
            ->assertSee('Pegawai Sebut Nomor')
            ->assertSee('Ke Halaman Login');
    }

    public function test_authenticated_admin_can_view_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        Employee::create([
            'name' => 'Pegawai Test',
            'bidang' => 'SEKRETARIAT',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Panduan');
    }

    public function test_default_admin_seed_can_login(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->assertTrue(Auth::attempt([
            'username' => 'bpad',
            'password' => 'bpad1',
        ]));
        $this->assertTrue(Auth::attempt([
            'username' => 'sekretariat',
            'password' => 'sekretariat1',
        ]));
    }

    public function test_authenticated_admin_can_submit_attendance_for_bidang(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $employeeA = Employee::create([
            'name' => 'Pegawai A',
            'bidang' => 'SEKRETARIAT',
            'is_active' => true,
        ]);
        $employeeB = Employee::create([
            'name' => 'Pegawai B',
            'bidang' => 'SEKRETARIAT',
            'is_active' => true,
        ]);
        $date = '2026-07-04';

        $this->actingAs($user)
            ->post(route('attendance.store'), [
                'attendance_date' => $date,
                'bidang' => 'SEKRETARIAT',
                'status' => [
                    $employeeA->id => 'HADIR',
                    $employeeB->id => 'SAKIT',
                ],
                'note' => [
                    $employeeB->id => 'Surat dokter',
                ],
            ])
            ->assertRedirect(route('attendance.index', ['bidang' => 'SEKRETARIAT']));

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employeeA->id,
            'attendance_date' => $date,
            'status' => 'HADIR',
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employeeB->id,
            'attendance_date' => $date,
            'status' => 'SAKIT',
            'note' => 'Surat dokter',
        ]);
        $this->assertDatabaseHas('attendance_submissions', [
            'bidang' => 'SEKRETARIAT',
            'attendance_date' => $date,
            'submitted_by' => $user->id,
        ]);
    }

    public function test_status_submit_and_daily_recap_show_saved_attendance(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $date = '2026-07-04';

        foreach (['SEKRETARIAT', 'PENDAPATAN 1', 'PENDAPATAN 2', 'ASET 1', 'ASET 2'] as $bidang) {
            $employee = Employee::create([
                'name' => 'Pegawai '.$bidang,
                'bidang' => $bidang,
                'is_active' => true,
            ]);

            $employee->attendanceRecords()->create([
                'attendance_date' => $date,
                'status' => $bidang === 'ASET 2' ? 'CUTI' : 'HADIR',
            ]);

            AttendanceSubmission::create([
                'bidang' => $bidang,
                'attendance_date' => $date,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get(route('submissions.index', ['date' => $date]))
            ->assertOk()
            ->assertSee('Status Submit 5 Bidang')
            ->assertSee('Sudah Submit');

        $this->actingAs($user)
            ->get(route('recap.index', ['date' => $date]))
            ->assertOk()
            ->assertSee('Rekapitulasi Absensi Apel Pagi')
            ->assertSee('Cuti: 1')
            ->assertSee('Daftar Nama Keterangan')
            ->assertSee('Pegawai ASET 2')
            ->assertSee('Export PDF / Print');
    }

    public function test_bidang_user_can_only_submit_own_bidang(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'bidang' => 'PENDAPATAN 1',
        ]);
        $ownEmployee = Employee::create([
            'name' => 'Pegawai Pendapatan',
            'bidang' => 'PENDAPATAN 1',
            'is_active' => true,
        ]);
        $otherEmployee = Employee::create([
            'name' => 'Pegawai Sekretariat',
            'bidang' => 'SEKRETARIAT',
            'is_active' => true,
        ]);
        $date = '2026-07-04';

        $this->actingAs($user)
            ->get(route('attendance.index', ['date' => $date, 'bidang' => 'SEKRETARIAT']))
            ->assertOk()
            ->assertSee('PENDAPATAN 1')
            ->assertDontSee('Pegawai Sekretariat');

        $this->actingAs($user)
            ->post(route('attendance.store'), [
                'attendance_date' => $date,
                'bidang' => 'PENDAPATAN 1',
                'status' => [$ownEmployee->id => 'HADIR'],
            ])
            ->assertRedirect(route('attendance.index', ['bidang' => 'PENDAPATAN 1']));

        $this->actingAs($user)
            ->post(route('attendance.store'), [
                'attendance_date' => $date,
                'bidang' => 'PENDAPATAN 1',
                'status' => [$ownEmployee->id => 'SAKIT'],
            ])
            ->assertSessionHasErrors('bidang');

        $this->actingAs($user)
            ->post(route('attendance.store'), [
                'attendance_date' => $date,
                'bidang' => 'SEKRETARIAT',
                'status' => [$otherEmployee->id => 'HADIR'],
            ])
            ->assertForbidden();
    }

    public function test_bidang_user_cannot_open_admin_pages(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'bidang' => 'ASET 1',
        ]);

        $this->actingAs($user)->get(route('employees.index'))->assertForbidden();
        $this->actingAs($user)->get(route('recap.index'))->assertForbidden();
    }

    public function test_attendance_page_uses_seeded_sort_order(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        Employee::create([
            'name' => 'Pegawai Urutan Kedua',
            'bidang' => 'SEKRETARIAT',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        Employee::create([
            'name' => 'Pegawai Urutan Pertama',
            'bidang' => 'SEKRETARIAT',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('attendance.index', ['bidang' => 'SEKRETARIAT']))
            ->assertOk()
            ->assertSeeInOrder(['Pegawai Urutan Pertama', 'Pegawai Urutan Kedua']);
    }

    public function test_attendance_page_shows_quick_actions_and_confirmation_modal(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        Employee::create([
            'name' => 'Pegawai UX',
            'bidang' => 'SEKRETARIAT',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('attendance.index', ['bidang' => 'SEKRETARIAT']))
            ->assertOk()
            ->assertSee('Set Semua Hadir')
            ->assertSee('Reset Data')
            ->assertSee('Konfirmasi Submit')
            ->assertSee('Status: Hadir')
            ->assertSee('No. 01')
            ->assertSee('attendanceSummary', false)
            ->assertDontSee('NIP/NIK')
            ->assertDontSee('Catatan');
    }

    public function test_dashboard_shows_quick_name_search_attendance_form(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        Employee::create([
            'name' => 'Nama Cepat Dicari',
            'bidang' => 'SEKRETARIAT',
            'sort_order' => 1,
            'is_pppk' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard', ['bidang' => 'SEKRETARIAT']))
            ->assertOk()
            ->assertSee('Rekap Harian')
            ->assertSee('data-live-hadir', false)
            ->assertSee('Input Cepat Absensi')
            ->assertSee('Nomor Urut Cepat')
            ->assertSee('Minimize')
            ->assertSee('employee-number-jump', false)
            ->assertSee('Cari nomor urut atau nama pegawai')
            ->assertSee('Nama Cepat Dicari (PPPK)')
            ->assertSee('No. 01')
            ->assertSee('Status: Hadir')
            ->assertSee('quickEmployeeSearch', false)
            ->assertSee('redirect_to', false);
    }

    public function test_dashboard_quick_submit_returns_to_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $employee = Employee::create([
            'name' => 'Pegawai Dashboard',
            'bidang' => 'SEKRETARIAT',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $date = '2026-07-04';

        $this->actingAs($user)
            ->post(route('attendance.store'), [
                'attendance_date' => $date,
                'bidang' => 'SEKRETARIAT',
                'status' => [$employee->id => 'HADIR'],
                'redirect_to' => 'dashboard',
            ])
            ->assertRedirect(route('dashboard', ['bidang' => 'SEKRETARIAT']));
    }
}
