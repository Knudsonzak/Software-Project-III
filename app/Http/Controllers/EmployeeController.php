<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        Employee::create($data);

        return Redirect::route('dashboard')
            ->with('success', 'Employee added.');
    }

    public function show(Employee $employee): View
    {
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

        $employee->update($data);

        return Redirect::route('dashboard')
            ->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return Redirect::route('dashboard')
            ->with('success', 'Employee deleted.');
    }
}