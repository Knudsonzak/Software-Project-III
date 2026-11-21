<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimeLogController extends Controller
{
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
