<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'employee', 403);

        $employeeId = $request->user()->emp_id;
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $weekStart = $now->copy()->startOfDay()->subDays(6);
        $logs = DB::table('time_logs')
            ->where('emp_id', $employeeId)
            ->whereBetween('clock_in', [$monthStart->min($weekStart), $now])
            ->get();

        $monthHours = $logs
            ->filter(fn ($log) => Carbon::parse($log->clock_in)->greaterThanOrEqualTo($monthStart))
            ->sum(fn ($log) => $this->hours($log, $now));

        $weekly = collect(range(6, 0))->map(function ($daysAgo) use ($logs, $now) {
            $date = $now->copy()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'hours' => round($logs->filter(fn ($log) => Carbon::parse($log->clock_in)->isSameDay($date))
                    ->sum(fn ($log) => $this->hours($log, $now)), 1),
            ];
        });

        $payroll = DB::table('payrolls')
            ->where('emp_id', $employeeId)
            ->orderByDesc('created_at')
            ->orderByDesc('payroll_id')
            ->limit(6)
            ->get();
        $leaves = DB::table('leave_forms')
            ->join('leave_types', 'leave_forms.leave_type_id', '=', 'leave_types.leave_type_id')
            ->where('leave_forms.emp_id', $employeeId)
            ->select('leave_forms.*', 'leave_types.name as leave_type')
            ->orderByDesc('leave_forms.created_at')
            ->orderByDesc('leave_forms.form_id')
            ->limit(5)
            ->get();
        $activeLog = DB::table('time_logs')
            ->where('emp_id', $employeeId)
            ->whereNull('clock_out')
            ->orderByDesc('clock_in')
            ->first();

        return view('employee-dashboard', [
            'monthHours' => $monthHours,
            'weekly' => $weekly,
            'payroll' => $payroll,
            'leaves' => $leaves,
            'activeLog' => $activeLog,
            'activeHours' => $activeLog ? $this->hours($activeLog, $now) : 0,
            'pendingLeaves' => DB::table('leave_forms')->where('emp_id', $employeeId)->where('status', 'pending')->count(),
        ]);
    }

    private function hours(object $log, Carbon $now): float
    {
        $start = Carbon::parse($log->clock_in);
        $end = $log->clock_out ? Carbon::parse($log->clock_out) : $now;

        return max(0, ($end->getTimestamp() - $start->getTimestamp()) / 60 - $log->break_minutes) / 60;
    }
}
