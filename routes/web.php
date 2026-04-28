<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FeedbackFormController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\StaffSubjectController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

Route::get('/', HomeController::class)->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [GoogleAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::resource('colleges', CollegeController::class)->except(['show']);
    Route::post('colleges/{id}/restore', [CollegeController::class, 'restore'])->name('colleges.restore');

    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::post('departments/{id}/restore', [DepartmentController::class, 'restore'])->name('departments.restore');

    Route::resource('semesters', SemesterController::class)->except(['show']);
    Route::post('semesters/{id}/restore', [SemesterController::class, 'restore'])->name('semesters.restore');

    Route::post('students/delete-all', [StudentController::class, 'destroyAll'])->name('students.delete-all');
    Route::resource('students', StudentController::class)->except(['show']);
    Route::post('students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');

    Route::post('staff/delete-all', [StaffSubjectController::class, 'destroyAll'])->name('staff.delete-all');
    Route::resource('staff', StaffSubjectController::class)->except(['show']);
    Route::post('staff/{id}/restore', [StaffSubjectController::class, 'restore'])->name('staff.restore');

    Route::resource('admins', AdminUserController::class)->except(['show']);
    Route::post('admins/{id}/restore', [AdminUserController::class, 'restore'])->name('admins.restore');

    Route::get('import/students', [ImportController::class, 'studentsForm'])->name('import.students');
    Route::get('import/students/template', [ImportController::class, 'downloadStudentsTemplate'])->name('import.students.template');
    Route::post('import/students', [ImportController::class, 'students'])->name('import.students.run');
    Route::get('import/staff', [ImportController::class, 'staffForm'])->name('import.staff');
    Route::get('import/staff/template', [ImportController::class, 'downloadStaffTemplate'])->name('import.staff.template');
    Route::post('import/staff', [ImportController::class, 'staff'])->name('import.staff.run');

    Route::get('feedback/forms', [FeedbackFormController::class, 'index'])->name('feedback.forms.index');
    Route::post('feedback/forms', [FeedbackFormController::class, 'store'])->name('feedback.forms.store');
    Route::get('feedback/forms/{form}/edit', [FeedbackFormController::class, 'edit'])->name('feedback.forms.edit');
    Route::put('feedback/forms/{form}', [FeedbackFormController::class, 'update'])->name('feedback.forms.update');
    Route::delete('feedback/forms/{form}', [FeedbackFormController::class, 'destroy'])->name('feedback.forms.destroy');
    Route::post('feedback/forms/{form}/versions', [FeedbackFormController::class, 'publishVersion'])->name('feedback.forms.versions.publish');
    Route::put('feedback/forms/{form}/versions/{version}', [FeedbackFormController::class, 'updateVersion'])->name('feedback.forms.versions.update');
    Route::post('feedback/forms/{form}/versions/{version}/questions', [FeedbackFormController::class, 'storeQuestion'])->name('feedback.forms.questions.store');
    Route::put('feedback/forms/{form}/versions/{version}/questions/{question}', [FeedbackFormController::class, 'updateQuestion'])->name('feedback.forms.questions.update');
    Route::delete('feedback/forms/{form}/versions/{version}/questions/{question}', [FeedbackFormController::class, 'destroyQuestion'])->name('feedback.forms.questions.destroy');
    Route::post('feedback/forms/{form}/versions/{version}/questions/reorder', [FeedbackFormController::class, 'reorderQuestions'])->name('feedback.forms.questions.reorder');
    Route::get('feedback/forms/{form}/preview', [FeedbackFormController::class, 'preview'])->name('feedback.forms.preview');

    Route::get('reports/participation', [ReportController::class, 'participation'])->name('reports.participation');
    Route::get('reports/staff', [ReportController::class, 'staff'])->name('reports.staff');
    Route::get('reports/special', [ReportController::class, 'special'])->name('reports.special');
    Route::get('reports/results', [ReportController::class, 'results'])->name('reports.results');
    Route::get('reports/results/export-excel', [ReportController::class, 'exportResultsExcel'])->name('reports.results.export.excel');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');

    Route::get('audit', AuditLogController::class)->name('audit.index');
});

Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', StudentDashboardController::class)->name('dashboard');
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback/start', [FeedbackController::class, 'start'])->name('feedback.start');
    Route::get('/feedback/wizard', [FeedbackController::class, 'wizard'])->name('feedback.wizard');
    Route::post('/feedback/wizard', [FeedbackController::class, 'saveStep'])->name('feedback.wizard.save');
    Route::get('/feedback/review', [FeedbackController::class, 'review'])->name('feedback.review');
    Route::post('/feedback/submit', [FeedbackController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('feedback.submit');
    Route::post('/feedback/draft', [FeedbackController::class, 'saveDraftAjax'])->name('feedback.draft');
});
