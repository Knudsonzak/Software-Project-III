<?php

namespace App\Http\Controllers;

use App\Models\LeaveForm;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AdminLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $allowedStatuses = ['pending', 'approved', 'rejected'];

        $query = LeaveForm::with(['employee', 'leaveType'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 WHEN 'rejected' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at');

        if (in_array($statusFilter, $allowedStatuses, true)) {
            $query->where('status', $statusFilter);
        } else {
            $statusFilter = null;
        }

        return view('admin.leaves.index', [
            'leaveRequests' => $query->get(),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function approve(LeaveForm $leaveForm): RedirectResponse
    {
        return $this->setStatus($leaveForm, 'approved', 'Leave request approved.');
    }

    public function reject(LeaveForm $leaveForm): RedirectResponse
    {
        return $this->setStatus($leaveForm, 'rejected', 'Leave request rejected.');
    }

    public function updateStatus(Request $request, LeaveForm $leaveForm): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $message = match ($validated['status']) {
            'approved' => 'Leave request approved.',
            'rejected' => 'Leave request rejected.',
            default => 'Leave request status updated.',
        };

        return $this->setStatus($leaveForm, $validated['status'], $message);
    }

    private function setStatus(LeaveForm $leaveForm, string $status, string $successMessage): RedirectResponse
    {
        try {
            $leaveForm->update(['status' => $status]);
        } catch (QueryException | Throwable $e) {
            return Redirect::back()
                ->with('error', 'Unable to update leave request status. Please try again.');
        }

        return Redirect::back()
            ->with('success', $successMessage);
    }
}