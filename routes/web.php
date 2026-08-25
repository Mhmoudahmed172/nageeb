<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Admin\OverviewController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentRegisterController;
use App\Http\Controllers\Auth\TeacherRegisterController;
use App\Http\Controllers\CourseCatalogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicTeacherProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\MyCoursesController;
use App\Http\Controllers\Student\SubscriptionRequestController as StudentSubscriptionRequestController;
use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\EarningsController;
use App\Http\Controllers\Teacher\EnrolledStudentController;
use App\Http\Controllers\Teacher\InteractionController;
use App\Http\Controllers\Teacher\PackageController;
use App\Http\Controllers\Teacher\PayoutController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\SubscriptionRequestController as TeacherSubscriptionRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('design-system');
});

Route::get('/courses', [CourseCatalogController::class, 'index'])->name('courses.index');
Route::get('/teachers/{teacher}', [PublicTeacherProfileController::class, 'show'])->name('teachers.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register/student', [StudentRegisterController::class, 'create'])->name('register.student');
    Route::post('/register/student', [StudentRegisterController::class, 'store']);

    Route::get('/register/teacher', [TeacherRegisterController::class, 'create'])->name('register.teacher');
    Route::post('/register/teacher', [TeacherRegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
    ->name('notifications.read-all');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [OverviewController::class, 'index'])->name('dashboard');
    Route::get('teachers', [AdminTeacherController::class, 'index'])->name('teachers.index');
    Route::post('teachers/{teacher}/verify', [AdminTeacherController::class, 'verify'])->name('teachers.verify');
    Route::get('payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
    Route::post('payouts/{payoutRequest}/settle', [AdminPayoutController::class, 'markAsSettled'])->name('payouts.settle');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
    Route::resource('courses', CourseController::class)->except(['show'])->names('courses');

    Route::get('/packages', fn () => redirect()->route('teacher.courses.index'))->name('packages.index');

    Route::scopeBindings()->group(function () {
        Route::get('courses/{course}/packages', [PackageController::class, 'index'])->name('courses.packages.index');
        Route::post('courses/{course}/packages', [PackageController::class, 'store'])->name('courses.packages.store');
        Route::put('courses/{course}/packages/{package}', [PackageController::class, 'update'])->name('courses.packages.update');
        Route::delete('courses/{course}/packages/{package}', [PackageController::class, 'destroy'])->name('courses.packages.destroy');
    });

    Route::get('subscription-requests', [TeacherSubscriptionRequestController::class, 'index'])->name('subscription-requests.index');
    Route::post('subscription-requests/{subscriptionRequest}/approve', [TeacherSubscriptionRequestController::class, 'approve'])->name('subscription-requests.approve');
    Route::post('subscription-requests/{subscriptionRequest}/reject', [TeacherSubscriptionRequestController::class, 'reject'])->name('subscription-requests.reject');

    Route::get('enrollments', [EnrolledStudentController::class, 'index'])->name('enrollments.index');
    Route::get('interactions', [InteractionController::class, 'index'])->name('interactions.index');
    Route::post('interactions/{comment}/reply', [InteractionController::class, 'reply'])->name('interactions.reply');

    Route::get('earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [PayoutController::class, 'store'])->name('payouts.store');

    Route::get('profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [TeacherProfileController::class, 'update'])->name('profile.update');

    Route::get('settings', [AccountSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AccountSettingsController::class, 'update'])->name('settings.update');
    Route::put('settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    Route::get('my-courses', [MyCoursesController::class, 'index'])->name('my-courses.index');
    Route::get('my-courses/{course}', [MyCoursesController::class, 'show'])->name('my-courses.show');
    Route::post('my-courses/{course}/lessons/{lesson}/comments', [MyCoursesController::class, 'storeComment'])->name('my-courses.comments.store');

    Route::get('settings', [AccountSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AccountSettingsController::class, 'update'])->name('settings.update');
    Route::put('settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/courses/{course}/subscribe', [StudentSubscriptionRequestController::class, 'create'])->name('courses.subscribe');
    Route::post('/courses/{course}/subscribe', [StudentSubscriptionRequestController::class, 'store'])->name('courses.subscribe.store');
    Route::get('/courses/{course}/subscribe/confirmation', [StudentSubscriptionRequestController::class, 'confirmation'])->name('courses.subscribe.confirmation');
});
