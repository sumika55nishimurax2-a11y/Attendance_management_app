<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
}
