<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('admin.students.destroy');

    // Class Management
    Route::post('/classes/{classList}/generate-pin', [ClassController::class, 'generatePin'])->name('admin.classes.generatePin');

    // Teacher Management
    Route::get('/teachers/search', [TeacherController::class, 'search'])->name('admin.teachers.search');
    Route::get('/teachers', [TeacherController::class, 'index'])->name('admin.teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('admin.teachers.store');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('admin.teachers.update');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('admin.teachers.destroy');

    // Vocabulary Library
    Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('admin.vocabulary');
    Route::post('/vocabulary', [VocabularyController::class, 'store'])->name('admin.vocabulary.store');
    Route::put('/vocabulary/{vocabulary}', [VocabularyController::class, 'update'])->name('admin.vocabulary.update');
    Route::delete('/vocabulary/{vocabulary}', [VocabularyController::class, 'destroy'])->name('admin.vocabulary.destroy');

    // Bilingual Assets
    Route::get('/assets', [AssetController::class, 'index'])->name('admin.assets');
    Route::post('/assets/{vocabulary}/upload', [AssetController::class, 'upload'])->name('admin.assets.upload');
    Route::delete('/assets/{vocabulary}/{language}', [AssetController::class, 'destroy'])->name('admin.assets.destroy');

    // Access Control (static display)
    Route::get('/access', fn() => view('admin.access'))->name('admin.access');

    // Static admin views
    Route::get('/logs', fn() => view('admin.logs'))->name('admin.logs');
    Route::get('/consultations', fn() => view('admin.consultations'))->name('admin.consultations');
    Route::get('/sync', fn() => view('admin.sync'))->name('admin.sync');
    Route::get('/snapshots', fn() => view('admin.snapshots'))->name('admin.snapshots');
    Route::get('/reports', fn() => view('admin.reports'))->name('admin.reports');
});


// --- TEACHER PORTAL ROUTES ---
Route::prefix('teacher')->middleware(['auth', 'teacher'])->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    Route::get('/students', [TeacherDashboardController::class, 'students'])->name('teacher.students');
    Route::get('/vocabulary', [TeacherDashboardController::class, 'vocabulary'])->name('teacher.vocabulary');
    Route::post('/vocabulary/suggest', [TeacherDashboardController::class, 'suggest'])->name('teacher.vocabulary.suggest');
    Route::get('/enrollment', [TeacherDashboardController::class, 'enrollment'])->name('teacher.enrollment');
    Route::get('/pin', [TeacherDashboardController::class, 'pin'])->name('teacher.pin');
    Route::post('/logout', [TeacherDashboardController::class, 'logout'])->name('teacher.logout');
});


require __DIR__.'/auth.php';