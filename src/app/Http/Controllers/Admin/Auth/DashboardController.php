<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function index($date = null)
    {
        $currentDate = $date ? Carbon::parse($date) : Carbon::today();

        $users = User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get();

        $attendances = Attendance::whereDate('work_date', $currentDate)->get()->keyBy('user_id');

        return view('admin.admin-list', compact('currentDate', 'users', 'attendances'));
    }

    public function show(Request $request, $id = null)
    {
        if ($id) {
            $attendance = Attendance::with('user')->findOrFail($id);
            $date = $attendance->work_date ? Carbon::parse($attendance->work_date) : Carbon::today();
        } else {
            $userId = $request->input('user_id');
            $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
            $attendance = new Attendance([
                'user_id'   => $userId,
                'work_date' => $date,
            ]);
            $attendance->setRelation('user', User::find($userId));
        }

        return view('admin.admin-detail', compact('attendance', 'date'));
    }
}
