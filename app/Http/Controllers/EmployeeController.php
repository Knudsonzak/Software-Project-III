<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('employees.index', [
            'employees' => Employee::with(['department', 'designation'])
                ->orderBy('name')
                ->get(),
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
            'password' => ['required', 'string', 'min:8'],
            'salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'department_id' => ['nullable', 'integer', 'exists:departments,department_id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,designation_id'],
        ]);

        Employee::create($data);

        return Redirect::route('employees.index')
            ->with('status', 'Employee added.');
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
            'password' => ['nullable', 'string', 'min:8'],
            'salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'department_id' => ['nullable', 'integer', 'exists:departments,department_id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,designation_id'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $employee->update($data);

        return Redirect::route('employees.index')
            ->with('status', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return Redirect::route('employees.index')
            ->with('status', 'Employee deleted.');
    }

    public function show(int $id){
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in to view employee details.');
        }

        if ($user->role !== 'admin' && (int) $user->emp_id !== $id) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view that employee.');
        }

        $employee = Employee::with(['department', 'designation'])->findOrFail($id);
        // shorthand for
        /*
        $employee = Employee::query()
            ->leftJoin('departments', 'departments.department_id', '=', 'employees.department_id')
            ->leftJoin('designations', 'designations.designation_id', '=', 'employees.designation_id')
            ->select('employees.*')
            ->where('employees.emp_id', $id)
            ->firstOrFail();
        */

        return view('show', compact('employee'));
    }
}
