<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        // 承認待ち一覧
        $pendingRequests = CorrectionRequest::with(['attendance.user'])
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        // 承認済み一覧
        $approvedRequests = CorrectionRequest::with(['attendance.user'])
            ->where('status', 'approved')
            ->orderBy('requested_at', 'desc')
            ->paginate(10, ['*'], 'approved_page');

        return view('admin.request-list', compact('pendingRequests', 'approvedRequests'));
    }

    public function showApprove(CorrectionRequest $attendance_correction_request)
    {
        // 勤怠データも一緒に取得
        $attendance = $attendance_correction_request->attendance;

        return view('admin.approval', [
            'correctionRequest' => $attendance_correction_request,
            'attendance' => $attendance,
        ]);
    }

    public function approve(CorrectionRequest $attendance_correction_request)
    {
        $attendance_correction_request->status = 'approved';
        $attendance_correction_request->save();

        // 承認後に同じ申請ページへリダイレクト
        return redirect()->route('admin.stamp_correction_request.show', [
            'attendance_correction_request' => $attendance_correction_request->id
        ])->with('success', '申請を承認しました。');
    }
}
