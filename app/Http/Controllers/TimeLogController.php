<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimeLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'employee_id' => [
                'nullable',
                'integer',
                Rule::exists('employees', 'emp_id')->where('role', 'employee'),
            ],
        ]);

        $startDate = $filters['start_date'] ?? today()->toDateString();
        $endDate = $filters['end_date'] ?? today()->toDateString();

        $employees = DB::table('employees')
            ->where('role', 'employee')
            ->orderBy('name')
            ->get(['emp_id', 'name']);

        $logsQuery = DB::table('time_logs')
            ->join('employees', 'employees.emp_id', '=', 'time_logs.emp_id')
            ->where('employees.role', 'employee')
            ->whereDate('time_logs.clock_in', '>=', $startDate)
            ->whereDate('time_logs.clock_in', '<=', $endDate)
            ->select([
                'time_logs.*',
                'employees.name as employee_name',
                'employees.email as employee_email',
            ]);

        if (! empty($filters['employee_id'])) {
            $logsQuery->where('time_logs.emp_id', $filters['employee_id']);
        }

        $matchingLogs = clone $logsQuery;
        $totalHours = (float) (clone $logsQuery)->sum('time_logs.hours');
        $totalEmployees = (clone $logsQuery)
            ->distinct()
            ->count('time_logs.emp_id');

        $unrecordedLogs = (clone $logsQuery)
            ->whereNull('time_logs.hours')
            ->get(['time_logs.clock_in', 'time_logs.clock_out', 'time_logs.break_minutes']);

        foreach ($unrecordedLogs as $log) {
            $clockIn = Carbon::parse($log->clock_in);
            $clockOut = $log->clock_out ? Carbon::parse($log->clock_out) : now();
            $elapsedSeconds = max(0, $clockOut->getTimestamp() - $clockIn->getTimestamp());
            $totalHours += max(0, $elapsedSeconds - ((int) $log->break_minutes * 60)) / 3600;
        }

        $attendanceLogs = $matchingLogs
            ->orderByDesc('time_logs.clock_in')
            ->paginate(25)
            ->withQueryString();

        $attendanceLogs->getCollection()->transform(function ($log): object {
            if ($log->hours !== null) {
                $log->hours_worked = (float) $log->hours;

                return $log;
            }

            $clockIn = Carbon::parse($log->clock_in);
            $clockOut = $log->clock_out ? Carbon::parse($log->clock_out) : now();
            $elapsedSeconds = max(0, $clockOut->getTimestamp() - $clockIn->getTimestamp());
            $log->hours_worked = max(0, $elapsedSeconds - ((int) $log->break_minutes * 60)) / 3600;

            return $log;
        });

        return view('admin.attendance.index', [
            'attendanceLogs' => $attendanceLogs,
            'employees' => $employees,
            'selectedEmployee' => $filters['employee_id'] ?? '',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalHours' => $totalHours,
            'totalEmployees' => $totalEmployees,
        ]);
    }

    public function clockIn(): RedirectResponse
    {
        $employee = Auth::user();
        abort_unless($employee->role === 'employee', 403);

        $alreadyClockedIn = DB::transaction(function () use ($employee): bool {
            DB::table('employees')
                ->where('emp_id', $employee->emp_id)
                ->lockForUpdate()
                ->first();

            $openLog = DB::table('time_logs')
                ->where('emp_id', $employee->emp_id)
                ->whereNull('clock_out')
                ->exists();

            if ($openLog) {
                return true;
            }

            DB::table('time_logs')->insert([
                'emp_id' => $employee->emp_id,
                'clock_in' => now(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return false;
        });

        if ($alreadyClockedIn) {
            return back()->with('error', 'You are already clocked in.');
        }

        return redirect()->route('dashboard')->with('success', 'You are clocked in.');
    }

    public function clockOut(): RedirectResponse
    {
        $employee = Auth::user();
        abort_unless($employee->role === 'employee', 403);

        $clockedOut = DB::transaction(function () use ($employee): bool {
            DB::table('employees')
                ->where('emp_id', $employee->emp_id)
                ->lockForUpdate()
                ->first();

            $openLog = DB::table('time_logs')
                ->whereNull('clock_out')
                ->where('emp_id', $employee->emp_id)
                ->orderByDesc('clock_in')
                ->lockForUpdate()
                ->first();

            if (! $openLog) {
                return false;
            }

            $clockOut = now();
            $clockIn = Carbon::parse($openLog->clock_in);
            $elapsedSeconds = max(0, $clockOut->getTimestamp() - $clockIn->getTimestamp());
            $workedSeconds = max(0, $elapsedSeconds - ((int) $openLog->break_minutes * 60));

            DB::table('time_logs')
                ->where('id', $openLog->id)
                ->where('emp_id', $employee->emp_id)
                ->whereNull('clock_out')
                ->update([
                    'clock_out' => $clockOut,
                    'hours' => round($workedSeconds / 3600, 2),
                    'updated_at' => now(),
                ]);

            return true;
        });

        if (! $clockedOut) {
            return back()->with('error', 'You are not currently clocked in.');
        }

        return redirect()->route('dashboard')->with('success', 'You are clocked out.');
    }
}
