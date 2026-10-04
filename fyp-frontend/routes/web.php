<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecommendController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PasswordResetController;


// Web routes

// Public auth routes
// ── Landing page — public, auto-redirects logged-in students ──────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get( '/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup']);

Route::get( '/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password',         [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password/verify', [PasswordResetController::class, 'verifyIdentity'])->name('password.forgot.verify');
Route::get('/reset-password/{token}',  [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password',         [PasswordResetController::class, 'updatePassword'])->name('password.reset.update');

Route::get('/email-lecturer', function () { return view('email'); })->name('email.lecturer')->middleware('student.auth');

// Protected routes - require logging in
Route::middleware('student.auth')->group(function () {

    // Main dashboard / search form
    Route::get('/dashboard', [RecommendController::class, 'index'])->name('home');

    // Handle form submission - call FastApi - render results
    Route::post('/recommend', [RecommendController::class, 'recommend'])->name('recommend');

    // Named alias so the layout can link to the results page on the nav
    Route::get('/recommend', fn() => redirect('/'))->name('recommend.results');

    // Search history
    Route::get('/history',      [RecommendController::class, 'historyList'])->name('history');
    Route::get('/history/{id}', [RecommendController::class, 'history'])->name('history.show');

    // Lecturer directory
    Route::get('/lecturers', [RecommendController::class, 'lecturers'])->name('lecturers');

    // For Debug
    Route::get('health', [RecommendController::class, 'health'])->name('health');

});


