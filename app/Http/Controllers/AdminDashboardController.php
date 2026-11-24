<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $employeeCount = DB::table('employees')->where('role', 'employee')->count();
        $present = DB::table('time_logs')
            ->join('employees', 'employees.emp_id', '=', 'time_logs.emp_id')
            ->where('employees.role', 'employee')
            ->whereDate('clock_in', $today)
            ->distinct()
            ->count('time_logs.emp_id');
        $absent = max(0, $employeeCount - $present);

        $attendanceTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'value' => DB::table('time_logs')
                    ->join('employees', 'employees.emp_id', '=', 'time_logs.emp_id')
                    ->where('employees.role', 'employee')
                    ->whereDate('clock_in', $date)
                    ->distinct()
                    ->count('time_logs.emp_id'),
            ];
        });

        $payrollTrend = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = today()->startOfMonth()->subMonths($monthsAgo);

            return [
                'label' => $date->format('M Y'),
                'value' => (float) DB::table('payrolls')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('net_salary'),
            ];
        });

        return view('dashboard', [
            'employeeCount' => $employeeCount,
            'present' => $present,
            'absent' => $absent,
            'pendingLeaves' => DB::table('leave_forms')->where('status', 'pending')->count(),
            'monthPayroll' => (float) DB::table('payrolls')
                ->whereYear('created_at', $today->year)
                ->whereMonth('created_at', $today->month)
                ->sum('net_salary'),
            'attendanceTrend' => $attendanceTrend,
            'departments' => DB::table('employees')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.department_id')
                ->where('employees.role', 'employee')
                ->selectRaw("COALESCE(NULLIF(departments.department_name, ''), 'Unassigned') as label, COUNT(*) as value")
                ->groupBy('label')
                ->orderByDesc('value')
                ->get(),
            'payrollTrend' => $payrollTrend,
            'recentLeaves' => DB::table('leave_forms')
                ->join('employees', 'employees.emp_id', '=', 'leave_forms.emp_id')
                ->select('leave_forms.*', 'employees.name')
                ->orderByDesc('leave_forms.created_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
