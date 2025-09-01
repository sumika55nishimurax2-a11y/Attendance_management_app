<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Staff\Auth\LoginController as StaffLoginController;
use App\Http\Controllers\Staff\Auth\RegisterController;
use App\Http\Controllers\Staff\Auth\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\AttendanceController as StaffAttendanceController;
use App\Http\Controllers\Admin\Auth\DashboardController as AdminDashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('attendance');
})->middleware('auth');

Route::get('/email/verify', function () {
    return view('staff.auth.verify-email');
})->middleware('auth')->name('verification.notice');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 開発環境限定ルート・ワンクリック認証完了ショートカット
if (App::environment('local', 'staging')) {
    Route::post('/staff/auth/verify-now', function () {
        $user = auth()->user();
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return redirect('/attendance')->with('status', 'メールアドレスを認証しました');
    })->middleware('auth')->name('verification.now');
}

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/attendance/list/{date?}', [AdminDashboardController::class, 'index'])->name('attendance.list');
        Route::get('/attendance/detail/{id?}', [AdminDashboardController::class, 'show'])->name('attendance.detail');
    });
});


Route::get('/login', [StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/login', [StaffLoginController::class, 'login']);
Route::post('/logout', [StaffLoginController::class, 'logout'])->name('staff.logout');

Route::get('/register', function () {
    return view('staff.auth.register');
})->name('register.form');
Route::post('/register', [RegisterController::class, 'store'])->name('staff.register');

Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/attendance', [StaffDashboardController::class, 'index'])->name('attendance');
    Route::post('/attendance/start', [StaffDashboardController::class, 'startWork'])->name('attendance.start');
    Route::post('/attendance/finish', [StaffDashboardController::class, 'finishWork'])->name('attendance.finish');
    Route::post('/attendance/break-start', [StaffDashboardController::class, 'startBreak'])->name('attendance.break_start');
    Route::post('/attendance/break-end', [StaffDashboardController::class, 'endBreak'])->name('attendance.break_end');
    Route::get('/attendance/list', [StaffAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StaffAttendanceController::class, 'requestList'])->name('stamp_correction_request.list');
    Route::get('/attendance/detail/{id?}', [StaffAttendanceController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/detail/{id?}/update', [StaffAttendanceController::class, 'update'])->name('attendance.detail.update');
});
