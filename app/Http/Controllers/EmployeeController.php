<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\AttendanceMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            'positionEmployees' => $this->positionEmployees(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $positionAfter = $validated['position_after'] ?? '__last';
        unset($validated['position_after']);

        DB::transaction(function () use ($request, $validated, $positionAfter): void {
            $employee = Employee::create($validated + [
                'is_active' => $request->boolean('is_active'),
                'is_pppk' => $request->boolean('is_pppk'),
            ]);

            if ($employee->is_active) {
                $this->placeEmployee($employee, $positionAfter);
            }
        });

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', [
            'employee' => $employee,
            'bidangOptions' => AttendanceMeta::bidang(),
            'positionEmployees' => $this->positionEmployees($employee),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $positionAfter = $validated['position_after'] ?? '__keep';
        $oldBidang = $employee->bidang;
        unset($validated['position_after']);

        DB::transaction(function () use ($request, $employee, $validated, $positionAfter, $oldBidang): void {
            $employee->update($validated + [
                'is_active' => $request->boolean('is_active'),
                'is_pppk' => $request->boolean('is_pppk'),
            ]);

            if (! $employee->is_active) {
                $employee->forceFill(['sort_order' => null])->save();
                $this->resequenceBidang($oldBidang);
                $this->resequenceBidang($employee->bidang);

                return;
            }

            if ($positionAfter === '__keep' && $oldBidang === $employee->bidang && $employee->sort_order !== null) {
                $this->resequenceBidang($employee->bidang);

                return;
            }

            $this->placeEmployee($employee, $positionAfter === '__keep' ? '__last' : $positionAfter);

            if ($oldBidang !== $employee->bidang) {
                $this->resequenceBidang($oldBidang);
            }
        });

        return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function deactivate(Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($employee): void {
            $bidang = $employee->bidang;
            $employee->update([
                'is_active' => false,
                'sort_order' => null,
            ]);
            $this->resequenceBidang($bidang);
        });

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil dinonaktifkan.');
    }

    /**
     * @return array{name: string, nip: string|null, bidang: string, position_after: string|null}
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:50'],
            'bidang' => ['required', Rule::in(AttendanceMeta::bidang())],
            'position_after' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return Collection<string, Collection<int, Employee>>
     */
    private function positionEmployees(?Employee $excludedEmployee = null): Collection
    {
        return Employee::active()
            ->when($excludedEmployee?->exists, fn ($query) => $query->whereKeyNot($excludedEmployee->id))
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('bidang');
    }

    private function placeEmployee(Employee $employee, ?string $positionAfter): void
    {
        $orderedEmployees = Employee::active()
            ->where('bidang', $employee->bidang)
            ->whereKeyNot($employee->id)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->values();

        $insertIndex = $orderedEmployees->count();

        if ($positionAfter === '__first') {
            $insertIndex = 0;
        } elseif ($positionAfter && $positionAfter !== '__last') {
            $afterId = (int) $positionAfter;
            $afterIndex = $orderedEmployees->search(fn (Employee $item) => $item->id === $afterId);

            if ($afterIndex !== false) {
                $insertIndex = $afterIndex + 1;
            }
        }

        $items = $orderedEmployees->all();
        array_splice($items, $insertIndex, 0, [$employee->fresh()]);

        foreach (array_values($items) as $index => $item) {
            $item->forceFill(['sort_order' => $index + 1])->save();
        }
    }

    private function resequenceBidang(string $bidang): void
    {
        Employee::active()
            ->where('bidang', $bidang)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->values()
            ->each(fn (Employee $employee, int $index) => $employee->forceFill(['sort_order' => $index + 1])->save());
    }
}
