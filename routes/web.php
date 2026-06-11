<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandWorkspaceController;
use App\Http\Controllers\ContentPlanController;
use App\Http\Controllers\ContentPlanPreviewController;
use App\Http\Controllers\ContentPlanStatusController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'active.user'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::redirect('/', '/brands');

    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::get('/brands/{brand}/workspace', [BrandWorkspaceController::class, 'show'])->name('brands.workspace');

    Route::post('/brands/{brand}/contents', [ContentPlanController::class, 'store'])->name('contents.store');
    Route::put('/contents/{contentPlan}', [ContentPlanController::class, 'update'])->name('contents.update');
    Route::delete('/contents/{contentPlan}', [ContentPlanController::class, 'destroy'])->name('contents.destroy');
    Route::patch('/contents/{contentPlan}/toggle-made', ContentPlanStatusController::class)->name('contents.toggle-made');
    Route::get('/contents/{contentPlan}/preview', [ContentPlanPreviewController::class, 'show'])->name('contents.preview');
    Route::get('/contents/{contentPlan}/pdf', [ContentPlanPreviewController::class, 'previewPdf'])->name('contents.pdf.preview');
    Route::get('/contents/{contentPlan}/pdf/download', [ContentPlanPreviewController::class, 'downloadPdf'])->name('contents.pdf.download');
    Route::get('/contents/{contentPlan}/print', [ContentPlanPreviewController::class, 'print'])->name('contents.print');

    Route::get('/media/brands/{brand}/logo', [MediaController::class, 'brandLogo'])->name('media.brand-logo');
    Route::get('/media/{contentImage}', [MediaController::class, 'show'])->name('media.show');
});
