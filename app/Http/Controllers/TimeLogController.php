<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class TimeLogController extends Controller
{
    public function index()
    {
        $employee = Auth::user();

        $openLog = TimeLog::where('emp_id', $employee->emp_id)->whereNull('clock_out')->latest('clock_in')->first();

        $todayLogs = TimeLog::where('emp_id', $employee->emp_id)->whereDate('clock_in', today())->orderByDesc('clock_in')->get();

        $weekLogs = TimeLog::where('emp_id', $employee->emp_id)->whereBetween('clock_in', [
                now()->startOfWeek(),
                now()->endOfWeek()
                ])->whereNotNull('clock_out')->get();

        $weeklyHours = $weekLogs->sum(fn (TimeLog $log) => (float) $log->hours);

        $todayHours = $todayLogs->whereNotNull('clock_out')->sum(fn (TimeLog $log) => (float) $log->hours);

        $recentLogs = TimeLog::where('emp_id', $employee->emp_id)->orderByDesc('clock_in')->limit(8)->get();

        return view('clock', compact(
            'employee',
            'openLog',
            'todayLogs',
            'recentLogs',
            'todayHours',
            'weeklyHours'
        ));
    }

    public function clockIn(Request $request)
    {
        $employee = Auth::user();

        $openLog = TimeLog::where('emp_id', $employee->emp_id)->whereNull('clock_out')->exists();

        if ($openLog) {
            return back()->with('error', 'You are already clocked in.');
        }

        TimeLog::create([
            'emp_id' => $employee->emp_id,
            'clock_in' => now(),
            'status' => 'pending',
        ]);

        return redirect()->route('clock')->with('success', 'You are clocked in.');
    }

    public function clockOut(Request $request)
    {
        $employee = Auth::user();

        $openLog = TimeLog::where('emp_id', $employee->emp_id)->whereNull('clock_out')->latest('clock_in')->first();

        if (! $openLog) {
            return back()->with('error', 'You are not currently clocked in.');
        }

        $clockOut = now();
        $breakMinutes = max(0, (int) $openLog->break_minutes);
        $elapsedMinutes = $openLog->clock_in->diffInMinutes($clockOut);
        $workedMinutes = max(0, $elapsedMinutes - $breakMinutes);

        $openLog->update([
            'clock_out' => $clockOut,
            'hours' => round($workedMinutes / 60, 2),
        ]);

        return redirect()->route('clock')->with('success', 'You are clocked out.');
    }
}
