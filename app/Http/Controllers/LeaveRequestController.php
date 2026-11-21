<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveForm;
use App\Models\LeaveType;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $employee = $this->resolveEmployee();
        $leaveTypes = LeaveType::orderBy('name')->get();

        $leaveRequests = $employee
            ? LeaveForm::with('leaveType')
                ->where('emp_id', $employee->emp_id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('leaves.index', [
            'employee' => $employee,
            'leaveTypes' => $leaveTypes,
            'leaveRequests' => $leaveRequests,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->resolveEmployee();

        if (! $employee) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'No employee profile is linked to your account.');
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'integer', Rule::exists('leave_types', 'leave_type_id')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            LeaveForm::create([
                'emp_id' => $employee->emp_id,
                'leave_type_id' => (int) $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'] ?? null,
                'status' => 'pending',
            ]);
        } catch (QueryException | Throwable $e) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'Unable to submit your leave request. Please try again.');
        }

        return Redirect::back()
            ->with('success', 'Leave request submitted.');
    }

    private function resolveEmployee(): ?Employee
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if (isset($user->emp_id)) {
            return Employee::where('emp_id', $user->emp_id)->first();
        }

        if (! empty($user->email)) {
            return Employee::where('email', $user->email)->first();
        }

        return null;
    }
}
