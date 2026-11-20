<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'employees' => Employee::with(['department', 'designation'])
                ->orderBy('name')
                ->get(),
            'departments' => Department::orderBy('department_name')->get(),
            'designations' => Designation::orderBy('designation_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('create', [
            'employee' => new Employee(),
            'departments' => Department::orderBy('department_name')->get(),
            'designations' => Designation::orderBy('designation_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeNullableIds($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['employee', 'admin'])],
            'password' => ['required', 'string', 'min:8'],
            'salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'department_id' => ['nullable', 'integer', 'exists:departments,department_id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,designation_id'],
        ]);

        try {
            Employee::create($data);
        } catch (QueryException | Throwable $e) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'Unable to create employee record. Please verify your inputs and try again.');
        }

        return Redirect::route('dashboard')
            ->with('success', 'Employee added.');
    }

    public function show(Employee $employee): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return Redirect::route('login')
                ->with('error', 'Please log in to view employee details.');
        }

        if ($user->role !== 'admin' && (int) $user->emp_id !== (int) $employee->emp_id) {
            return Redirect::route('dashboard')
                ->with('error', 'You are not authorized to view that employee.');
        }

        $employee->load(['department', 'designation']);

        return view('show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('edit', [
            'employee' => $employee,
            'departments' => Department::orderBy('department_name')->get(),
            'designations' => Designation::orderBy('designation_name')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->normalizeNullableIds($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee->emp_id, 'emp_id'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['employee', 'admin'])],
            'password' => ['nullable', 'string', 'min:8'],
            'salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'department_id' => ['nullable', 'integer', 'exists:departments,department_id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,designation_id'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        try {
            $employee->update($data);
        } catch (QueryException | Throwable $e) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'Unable to update employee record. Please verify your inputs and try again.');
        }

        return Redirect::route('dashboard')
            ->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        try {
            $employee->delete();
        } catch (QueryException | Throwable $e) {
            return Redirect::route('dashboard')
                ->with('error', 'Unable to delete employee record because it may be referenced by other records.');
        }

        return Redirect::route('dashboard')
            ->with('success', 'Employee deleted.');
    }

    private function normalizeNullableIds(Request $request): void
    {
        foreach (['department_id', 'designation_id'] as $field) {
            if ($request->has($field) && $request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }
    }
}