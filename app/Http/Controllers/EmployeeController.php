<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\AttendanceMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'bidang' => ['nullable', Rule::in(AttendanceMeta::bidang())],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
        ]);

        $status = $validated['status'] ?? 'active';
        $employees = Employee::query()
            ->when($validated['bidang'] ?? null, fn ($query, $bidang) => $query->where('bidang', $bidang))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('employees.index', [
            'employees' => $employees,
            'bidangOptions' => AttendanceMeta::bidang(),
            'selectedBidang' => $validated['bidang'] ?? '',
            'selectedStatus' => $status,
        ]);
    }

    public function create(): View
    {
        return view('employees.create', [
            'employee' => new Employee(['is_active' => true, 'is_pppk' => false]),
            'bidangOptions' => AttendanceMeta::bidang(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Employee::create($this->validatedData($request) + [
            'is_active' => $request->boolean('is_active'),
            'is_pppk' => $request->boolean('is_pppk'),
        ]);

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', [
            'employee' => $employee,
            'bidangOptions' => AttendanceMeta::bidang(),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validatedData($request) + [
            'is_active' => $request->boolean('is_active'),
            'is_pppk' => $request->boolean('is_pppk'),
        ]);

        return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function deactivate(Employee $employee): RedirectResponse
    {
        $employee->update(['is_active' => false]);

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil dinonaktifkan.');
    }

    /**
     * @return array{name: string, nip: string|null, bidang: string}
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:50'],
            'bidang' => ['required', Rule::in(AttendanceMeta::bidang())],
        ]);
    }
}
