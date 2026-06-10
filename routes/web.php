<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\Admin\VocabularySuggestionsController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\AdminSubstituteController;
use App\Http\Controllers\Admin\AdminClassController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\CmsController as AdminCmsController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\TicketController as TeacherTicketController;
use App\Http\Controllers\Teacher\MessagingController as TeacherMessagingController;
use App\Http\Controllers\Teacher\ConsultationController as TeacherConsultationController;
use App\Http\Controllers\Teacher\NotificationController as TeacherNotificationController;
use App\Http\Controllers\Teacher\TeacherClassSwitchController;
use App\Http\Controllers\ParentPortal\ProfileController as ParentProfileController;
use App\Http\Controllers\ParentPortal\NotificationController as ParentNotificationController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboardController;
use App\Http\Controllers\ParentPortal\ProgressController as ParentProgressController;
use App\Http\Controllers\ParentPortal\ProficiencyController as ParentProficiencyController;
use App\Http\Controllers\ParentPortal\MessagingController as ParentMessagingController;
use App\Http\Controllers\ParentPortal\ConsultationsController as ParentConsultationsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public landing page (no auth — homepage)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Public teacher account activation (no auth — reached from emailed link)
Route::get('/teacher/activate/{token}', [\App\Http\Controllers\TeacherActivationController::class, 'activate'])
    ->name('teacher.activate');

Route::get('/dashboard', function () {
    return match (Auth::user()->role) {
        'Admin'   => redirect()->route('admin.dashboard'),
        'Teacher' => redirect()->route('teacher.dashboard'),
        'Parent'  => redirect()->route('parent.dashboard'),
        default   => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

// Default Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- ADMIN PORTAL ROUTES ---
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');

    // Student Mapping
    Route::get('/students', [StudentController::class, 'index'])->name('admin.students');
    Route::post('/students', [StudentController::class, 'store'])->name('admin.students.store');
    Route::patch('/students/{student}', [StudentController::class, 'update'])->name('admin.students.update');
    Route::get('/students/{student}/profile', [\App\Http\Controllers\Admin\UserManagementController::class, 'showStudentProfile'])->name('admin.students.profile');
    Route::post('/students/{student}/archive', [StudentController::class, 'archive'])->name('admin.students.archive');
    Route::post('/students/{student}/restore', [StudentController::class, 'restore'])->name('admin.students.restore');

    // Class Management
    Route::post('/classes/{classList}/generate-pin', [ClassController::class, 'generatePin'])->name('admin.classes.generatePin');
    Route::post('/classes/assign',           [AdminClassController::class, 'assignClass'])->name('admin.classes.assign');
    Route::delete('/classes/{id}/unassign',  [AdminClassController::class, 'unassignClass'])->name('admin.classes.unassign');
    Route::patch('/classes/{id}/archive',    [AdminClassController::class, 'archiveClass'])->name('admin.classes.archive');
    Route::patch('/classes/{id}/restore',    [AdminClassController::class, 'restoreClass'])->name('admin.classes.restore');
    Route::post('/classes/create-assign',    [AdminClassController::class, 'createAndAssign'])->name('admin.classes.create-assign');
    Route::patch('/classes/update-subject',  [AdminClassController::class, 'updateSubject'])->name('admin.classes.update-subject');

    // Teacher Management
    Route::get('/teachers/search', [TeacherController::class, 'search'])->name('admin.teachers.search');
    Route::get('/teachers', [TeacherController::class, 'index'])->name('admin.teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('admin.teachers.store');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('admin.teachers.update');
    Route::post('/teachers/{teacher}/resend-activation', [TeacherController::class, 'resendActivation'])->name('admin.teachers.resend-activation');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('admin.teachers.destroy');
    Route::get('/teachers/{teacher}/profile', [\App\Http\Controllers\Admin\UserManagementController::class, 'showTeacherProfile'])->name('admin.teachers.profile');
    Route::get('/parents/{user}/profile', [\App\Http\Controllers\Admin\UserManagementController::class, 'showParentProfile'])->name('admin.parents.profile');
    Route::post('/admins', [\App\Http\Controllers\Admin\UserManagementController::class, 'storeAdmin'])->name('admin.admins.store');

    // Vocabulary Suggestions
    Route::get('/vocabulary-suggestions', [VocabularySuggestionsController::class, 'index'])->name('admin.vocabulary-suggestions.index');
    Route::post('/vocabulary-suggestions/{suggestion}/approve', [VocabularySuggestionsController::class, 'approve'])->name('admin.vocabulary-suggestions.approve');
    Route::post('/vocabulary-suggestions/{suggestion}/reject', [VocabularySuggestionsController::class, 'reject'])->name('admin.vocabulary-suggestions.reject');

    // Vocabulary Library
    Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('admin.vocabulary');
    Route::post('/vocabulary', [VocabularyController::class, 'store'])->name('admin.vocabulary.store');
    Route::put('/vocabulary/{vocabulary}', [VocabularyController::class, 'update'])->name('admin.vocabulary.update');
    Route::delete('/vocabulary/{vocabulary}', [VocabularyController::class, 'destroy'])->name('admin.vocabulary.destroy');
    Route::post('/vocabulary/fetch-image', [VocabularyController::class, 'fetchImageForWord'])->name('admin.vocabulary.fetch-image');
    Route::post('/vocabulary/generate-audio', [VocabularyController::class, 'generateAudio'])->name('admin.vocabulary.generate-audio');

    // Access Control (static display)
    Route::get('/access', fn() => view('admin.access'))->name('admin.access');

    // Tickets
    Route::get('/tickets',           [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::post('/tickets',          [AdminTicketController::class, 'store'])->name('admin.tickets.store');
    Route::patch('/tickets/{ticket}', [AdminTicketController::class, 'update'])->name('admin.tickets.update');

    // Content Management System (landing page)
    Route::get('/cms',                              [AdminCmsController::class, 'index'])->name('admin.cms.index');
    Route::patch('/cms/section/{sectionKey}',       [AdminCmsController::class, 'updateSection'])->name('admin.cms.section.update');
    Route::post('/cms/announcements',               [AdminCmsController::class, 'storeAnnouncement'])->name('admin.cms.announcements.store');
    Route::patch('/cms/announcements/{id}',         [AdminCmsController::class, 'updateAnnouncement'])->name('admin.cms.announcements.update');
    Route::delete('/cms/announcements/{id}',        [AdminCmsController::class, 'destroyAnnouncement'])->name('admin.cms.announcements.destroy');

    // Static admin views
    Route::get('/consultations',        [AdminConsultationController::class, 'index'])->name('admin.consultations');
    Route::get('/consultations/export', [AdminConsultationController::class, 'export'])->name('admin.consultations.export');
    Route::get('/sync', fn() => view('admin.sync'))->name('admin.sync');
    Route::get('/snapshots', fn() => view('admin.snapshots'))->name('admin.snapshots');
    Route::get('/activity-logs',        [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity.logs');
    Route::get('/activity-logs/export', [\App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('admin.activity-logs.export');

    // Substitute Management
    Route::post('/substitutes/assign',        [AdminSubstituteController::class, 'assign'])->name('admin.substitutes.assign');
    Route::delete('/substitutes/{id}/remove', [AdminSubstituteController::class, 'remove'])->name('admin.substitutes.remove');
    Route::get('/substitutes/list',           [AdminSubstituteController::class, 'listForClass'])->name('admin.substitutes.list');
    Route::get('/reports',                         [AdminReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/export/student-progress', [AdminReportController::class, 'exportStudentProgressCsv'])->name('admin.reports.export.progress');
    Route::get('/reports/export/mastery-scores',   [AdminReportController::class, 'exportMasteryScoresCsv'])->name('admin.reports.export.mastery');
    Route::get('/reports/export/captured-objects', [AdminReportController::class, 'exportCapturedObjectsCsv'])->name('admin.reports.export.captured');
    Route::get('/reports/{student}',               [AdminReportController::class, 'show'])->name('admin.reports.show');

    // Profile & Password
    Route::get('/profile',          [AdminProfileController::class, 'index'])->name('admin.profile');
    Route::post('/profile',         [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile/picture', [AdminProfileController::class, 'uploadPicture'])->name('admin.profile.upload-picture');
    Route::get('/password',         [AdminProfileController::class, 'passwordForm'])->name('admin.password');
    Route::post('/password',        [AdminProfileController::class, 'changePassword'])->name('admin.password.change');

    // Notification actions
    Route::post('/notifications/read-all', function () {
        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('recipient_id', auth()->id())
            ->where('recipient_role', 'Admin')
            ->update(['is_read' => true]);
        return response()->noContent();
    })->name('admin.notifications.read-all');

    Route::post('/notifications/{id}/read', function ($id) {
        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_id', auth()->id())
            ->where('recipient_role', 'Admin')
            ->update(['is_read' => true]);
        return response()->noContent();
    })->name('admin.notifications.read');

    Route::get('/notifications/redirect/{id}', [AdminNotificationController::class, 'redirect'])->name('admin.notifications.redirect');
});


// --- TEACHER PORTAL ROUTES ---
Route::prefix('teacher')->middleware(['auth', 'teacher'])->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    Route::get('/students', [TeacherDashboardController::class, 'students'])->name('teacher.students');
    Route::get('/vocabulary', [TeacherDashboardController::class, 'vocabulary'])->name('teacher.vocabulary');
    Route::post('/vocabulary/suggest', [TeacherDashboardController::class, 'suggest'])->name('teacher.vocabulary.suggest');
    Route::get('/enrollment',  [TeacherDashboardController::class, 'enrollment'])->name('teacher.enrollment');
    Route::post('/enrollment', [TeacherDashboardController::class, 'enrollmentStore'])->name('teacher.enrollment.store');
    Route::get('/pin', [TeacherDashboardController::class, 'pin'])->name('teacher.pin');
    Route::get('/student-progress', [\App\Http\Controllers\Teacher\StudentProgressController::class, 'index'])->name('teacher.student-progress');
    Route::get('/spelling-analysis', [\App\Http\Controllers\Teacher\SpellingAnalysisController::class, 'index'])->name('teacher.spelling-analysis');
    Route::get('/milestones', [\App\Http\Controllers\Teacher\MilestonesController::class, 'index'])->name('teacher.milestones');
    Route::get('/reports',                          [\App\Http\Controllers\Teacher\ReportController::class, 'index'])->name('teacher.reports');
    Route::get('/reports/export/{student}',         [\App\Http\Controllers\Teacher\ReportController::class, 'exportStudentReportCsv'])->name('teacher.reports.export');
    Route::get('/reports/{student}',                [\App\Http\Controllers\Teacher\ReportController::class, 'show'])->name('teacher.reports.show');
    Route::post('/reports/{student}/send',          [\App\Http\Controllers\Teacher\ReportController::class, 'send'])->name('teacher.reports.send');
    Route::get('/word-sets',         [\App\Http\Controllers\Teacher\WordSetsController::class, 'index'])->name('teacher.word-sets');
    Route::post('/word-sets/toggle', [\App\Http\Controllers\Teacher\WordSetsController::class, 'toggle'])->name('teacher.word-sets.toggle');
    Route::get('/annotations',       [\App\Http\Controllers\Teacher\AnnotationsController::class, 'index'])->name('teacher.annotations');
    Route::post('/annotations',      [\App\Http\Controllers\Teacher\AnnotationsController::class, 'store'])->name('teacher.annotations.store');
    Route::get('/mobile-sync',       [\App\Http\Controllers\Teacher\MobileSyncController::class,  'index'])->name('teacher.mobile-sync');
    Route::post('/logout', [TeacherDashboardController::class, 'logout'])->name('teacher.logout');
    Route::post('/switch-class', [TeacherClassSwitchController::class, 'switch'])->name('teacher.switch-class');

    // Messaging
    Route::get('/messaging',  [TeacherMessagingController::class,   'index'])->name('teacher.messaging');
    Route::post('/messaging', [TeacherMessagingController::class,   'store'])->name('teacher.messaging.store');

    // Tickets
    Route::get('/tickets',  [TeacherTicketController::class, 'index'])->name('teacher.tickets.index');
    Route::post('/tickets', [TeacherTicketController::class, 'store'])->name('teacher.tickets.store');

    // Consultation Availability
    Route::get('/consultation-availability',              [TeacherConsultationController::class, 'index'])->name('teacher.consultation');
    Route::post('/consultation-availability',             [TeacherConsultationController::class, 'store'])->name('teacher.consultation.store');
    Route::post('/consultation-availability/save',        [TeacherConsultationController::class, 'saveSchedule'])->name('teacher.consultation.save');
    Route::post('/consultation-availability/status',      [TeacherConsultationController::class, 'updateStatus'])->name('teacher.consultation.updateStatus');
    Route::post('/consultation-availability/max-appointments', [TeacherConsultationController::class, 'saveMaxAppointments'])->name('teacher.consultation.maxAppointments');
    Route::post('/consultation-availability/bookings/{booking}/confirm',  [TeacherConsultationController::class, 'confirmBooking'])->name('teacher.consultation.booking.confirm');
    Route::post('/consultation-availability/bookings/{booking}/reject',   [TeacherConsultationController::class, 'rejectBooking'])->name('teacher.consultation.booking.reject');
    Route::post('/consultation-availability/bookings/{booking}/complete', [TeacherConsultationController::class, 'completeBooking'])->name('teacher.consultation.booking.complete');
    Route::delete('/consultation-availability/{slot}',    [TeacherConsultationController::class, 'destroy'])->name('teacher.consultation.destroy');

    // Profile & Password
    Route::get('/profile',   [TeacherProfileController::class, 'index'])->name('teacher.profile');
    Route::post('/profile',         [TeacherProfileController::class, 'update'])->name('teacher.profile.update');
    Route::post('/profile/picture', [TeacherProfileController::class, 'uploadPicture'])->name('teacher.profile.upload-picture');
    Route::get('/password',         [TeacherProfileController::class, 'passwordForm'])->name('teacher.password');
    Route::post('/password', [TeacherProfileController::class, 'changePassword'])->name('teacher.password.change');

    // Notification actions
    Route::post('/notifications/read-all', function () {
        $teacher = \App\Models\Teacher::where('user_id', auth()->id())->first();
        if ($teacher) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('recipient_id', $teacher->id)
                ->where('recipient_role', 'Teacher')
                ->update(['is_read' => true]);
        }
        return response()->noContent();
    })->name('teacher.notifications.read-all');

    Route::post('/notifications/{id}/read', function ($id) {
        $teacher = \App\Models\Teacher::where('user_id', auth()->id())->first();
        if ($teacher) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('id', $id)
                ->where('recipient_id', $teacher->id)
                ->where('recipient_role', 'Teacher')
                ->update(['is_read' => true]);
        }
        return response()->noContent();
    })->name('teacher.notifications.read');

    Route::get('/notifications/redirect/{id}', [TeacherNotificationController::class, 'redirect'])->name('teacher.notifications.redirect');
});


// --- PARENT PORTAL ROUTES ---
Route::prefix('parent')->middleware(['auth', 'parent'])->name('parent.')->group(function () {
    Route::get('/dashboard',              [ParentDashboardController::class,     'index'])->name('dashboard');
    Route::post('/password/change',       [ParentDashboardController::class,     'changePassword'])->name('password.change');
    Route::get('/progress',               [ParentProgressController::class,      'index'])->name('progress');
    Route::get('/proficiency',            [ParentProficiencyController::class,   'index'])->name('proficiency');
    Route::get('/messaging',              [ParentMessagingController::class,     'index'])->name('messaging');
    Route::post('/messaging',             [ParentMessagingController::class,     'store'])->name('messaging.store');
    Route::get('/consultations',          [ParentConsultationsController::class, 'index'])->name('consultations');
    Route::post('/consultations',         [ParentConsultationsController::class, 'store'])->name('consultations.store');
    Route::get('/consultations/slots',    [ParentConsultationsController::class, 'slots'])->name('consultations.slots');
    Route::post('/consultations/{booking}/cancel', [ParentConsultationsController::class, 'cancel'])->name('consultations.cancel');

    // Profile & Portal Password (web login — separate from mobile app password on dashboard)
    Route::get('/profile',   [ParentProfileController::class, 'index'])->name('profile');
    Route::post('/profile',         [ParentProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/picture', [ParentProfileController::class, 'uploadPicture'])->name('profile.upload-picture');
    Route::get('/password',         [ParentProfileController::class, 'passwordForm'])->name('portal.password');
    Route::post('/password', [ParentProfileController::class, 'changePassword'])->name('portal.password.change');

    // Notification actions
    Route::post('/notifications/read-all', function () {
        $parent = \App\Models\ParentProfile::where('user_id', auth()->id())->first();
        if ($parent) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('recipient_id', $parent->id)
                ->where('recipient_role', 'Parent')
                ->update(['is_read' => true]);
        }
        return response()->noContent();
    })->name('notifications.read-all');

    Route::post('/notifications/{id}/read', function ($id) {
        $parent = \App\Models\ParentProfile::where('user_id', auth()->id())->first();
        if ($parent) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('id', $id)
                ->where('recipient_id', $parent->id)
                ->where('recipient_role', 'Parent')
                ->update(['is_read' => true]);
        }
        return response()->noContent();
    })->name('notifications.read');

    Route::get('/notifications/redirect/{id}', [ParentNotificationController::class, 'redirect'])->name('notifications.redirect');
});


require __DIR__.'/auth.php';