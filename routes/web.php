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
use App\Http\Controllers\Teacher\AccessPlanController;
use App\Http\Controllers\Teacher\CourseManageController;
use App\Http\Controllers\Teacher\LessonContentController;
use App\Http\Controllers\Teacher\LessonController;
use App\Http\Controllers\Teacher\SemesterController;
use App\Http\Controllers\Teacher\UnitController;
use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\EarningsController;
use App\Http\Controllers\Teacher\EnrolledStudentController;
use App\Http\Controllers\Teacher\InteractionController;
use App\Http\Controllers\Teacher\PayoutController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\SubscriptionRequestController as TeacherSubscriptionRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

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
    Route::get('/internal/ui-kit', fn () => view('internal.ui-kit'))->name('ui-kit');
    Route::get('teachers', [AdminTeacherController::class, 'index'])->name('teachers.index');
    Route::post('teachers/{teacher}/verify', [AdminTeacherController::class, 'verify'])->name('teachers.verify');
    Route::get('payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
    Route::post('payouts/{payoutRequest}/settle', [AdminPayoutController::class, 'markAsSettled'])->name('payouts.settle');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/students/{student}/remind', [TeacherDashboardController::class, 'remind'])->name('dashboard.remind');
    Route::resource('courses', CourseController::class)->except(['show'])->names('courses');
    Route::get('courses/{course}', fn (\App\Models\Course $course) => redirect()->route('teacher.courses.content', $course))->name('courses.manage');
    Route::post('courses/{course}/publish', [CourseManageController::class, 'togglePublish'])->name('courses.publish');
    Route::get('courses/{course}/preview', [CourseManageController::class, 'preview'])->name('courses.preview');
    Route::get('courses/{course}/overview', [CourseManageController::class, 'overview'])->name('courses.overview');
    Route::get('courses/{course}/content', [UnitController::class, 'index'])->name('courses.content');
    Route::get('courses/{course}/students', [CourseManageController::class, 'students'])->name('courses.students');
    Route::get('courses/{course}/analytics', [CourseManageController::class, 'analytics'])->name('courses.analytics');
    Route::get('courses/{course}/settings', [CourseManageController::class, 'settings'])->name('courses.settings');

    Route::get('/packages', fn () => redirect()->route('teacher.courses.index'))->name('packages.index');

    Route::scopeBindings()->group(function () {
        Route::get('courses/{course}/semesters/create', [SemesterController::class, 'create'])->name('courses.semesters.create');
        Route::post('courses/{course}/semesters', [SemesterController::class, 'store'])->name('courses.semesters.store');
        Route::get('courses/{course}/semesters/{semester}/edit', [SemesterController::class, 'edit'])->name('courses.semesters.edit');
        Route::put('courses/{course}/semesters/{semester}', [SemesterController::class, 'update'])->name('courses.semesters.update');
        Route::delete('courses/{course}/semesters/{semester}', [SemesterController::class, 'destroy'])->name('courses.semesters.destroy');
        Route::post('courses/{course}/semesters/{semester}/move', [SemesterController::class, 'move'])->name('courses.semesters.move');
        Route::post('courses/{course}/semesters/reorder', [SemesterController::class, 'reorder'])->name('courses.semesters.reorder');

        Route::get('courses/{course}/units/create', [UnitController::class, 'create'])->name('courses.units.create');
        Route::post('courses/{course}/units', [UnitController::class, 'store'])->name('courses.units.store');
        Route::get('courses/{course}/units/{unit}/edit', [UnitController::class, 'edit'])->name('courses.units.edit');
        Route::put('courses/{course}/units/{unit}', [UnitController::class, 'update'])->name('courses.units.update');
        Route::delete('courses/{course}/units/{unit}', [UnitController::class, 'destroy'])->name('courses.units.destroy');
        Route::post('courses/{course}/units/{unit}/move', [UnitController::class, 'move'])->name('courses.units.move');
        Route::post('courses/{course}/semesters/{semester}/units/reorder', [UnitController::class, 'reorder'])->name('courses.units.reorder');

        Route::get('courses/{course}/lessons/create', [LessonController::class, 'create'])->name('courses.lessons.create');
        Route::post('courses/{course}/lessons', [LessonController::class, 'store'])->name('courses.lessons.store');
        Route::get('courses/{course}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('courses.lessons.edit');
        Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.lessons.update');
        Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.lessons.destroy');
        Route::post('courses/{course}/lessons/{lesson}/duplicate', [LessonController::class, 'duplicate'])->name('courses.lessons.duplicate');
        Route::post('courses/{course}/lessons/{lesson}/move', [LessonController::class, 'move'])->name('courses.lessons.move');
        Route::post('courses/{course}/lessons/{lesson}/relocate', [LessonController::class, 'relocate'])->name('courses.lessons.relocate');
        Route::post('courses/{course}/units/{unit}/lessons/reorder', [LessonController::class, 'reorder'])->name('courses.lessons.reorder');
        Route::post('courses/{course}/lessons/{lesson}/contents/reorder', [LessonController::class, 'reorderContents'])->name('courses.lesson-contents.reorder');
        Route::post('courses/{course}/lessons/{lesson}/contents', [LessonContentController::class, 'store'])->name('courses.lesson-contents.store');
        Route::put('courses/{course}/lessons/{lesson}/contents/{content}', [LessonContentController::class, 'update'])->name('courses.lesson-contents.update');
        Route::delete('courses/{course}/lessons/{lesson}/contents/{content}', [LessonContentController::class, 'destroy'])->name('courses.lesson-contents.destroy');

        Route::get('courses/{course}/packages', [AccessPlanController::class, 'index'])->name('courses.packages.index');
        Route::post('courses/{course}/packages', [AccessPlanController::class, 'store'])->name('courses.packages.store');
        Route::put('courses/{course}/packages/{accessPlan}', [AccessPlanController::class, 'update'])->name('courses.packages.update');
        Route::delete('courses/{course}/packages/{accessPlan}', [AccessPlanController::class, 'destroy'])->name('courses.packages.destroy');
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
