<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessTemplateController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\CertificateTemplateRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\GuestCertificateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/verify', [VerificationController::class, 'lookup'])->name('verify.lookup');
Route::get('/verify/{certificateNumber}', [VerificationController::class, 'show'])->name('verify.show');
Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

Route::get('/terms', fn() => Inertia::render('Legal/TermsOfService', [
    'isAuthenticated' => auth()->check(),
    'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
]))->name('terms.service');

Route::get('/privacy', fn() => Inertia::render('Legal/PrivacyPolicy', [
    'isAuthenticated' => auth()->check(),
    'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
]))->name('privacy.policy');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

//Onboarding route was suppose to be an auth route but was added here because of the sessions it was loosing due to captureReferralCode not running before auth middleware
Route::get('/onboarding', [BusinessController::class, 'create'])->name('business.create');

Route::get('/certificates/preview', [CertificateController::class, 'preview'])->name('certificates.preview');
Route::get('/certificates/guest/preview', [CertificateController::class, 'previewGuest'])->name('certificates.guest.preview');
Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');





Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureBusinessExists::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
    Route::post('/programmes', [ProgrammeController::class, 'store'])->name('programmes.store');
    Route::put('/programmes/{programme}', [ProgrammeController::class, 'update'])->name('programmes.update');
    Route::patch('/programmes/{programme}/archive', [ProgrammeController::class, 'archive'])->name('programmes.archive');
    Route::patch('/programmes/{programme}/restore', [ProgrammeController::class, 'restore'])->name('programmes.restore');
    Route::delete('/programmes/{programme}', [ProgrammeController::class, 'destroy'])->name('programmes.destroy');


    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::patch('/students/{student}/complete', [StudentController::class, 'complete'])->name('students.complete');
    Route::patch('/students/{student}/uncomplete', [StudentController::class, 'uncomplete'])->name('students.uncomplete');


// Onboarding — no business-exists check here, this route creates the business
    Route::post('/onboarding', [BusinessController::class, 'store'])->name('business.store');

    Route::get('/settings/business', [BusinessController::class, 'edit'])->name('business.edit');
    Route::put('/settings/business', [BusinessController::class, 'update'])->name('business.update');


    Route::get('/certificates/guest', [GuestCertificateController::class, 'create'])->name('certificates.guest.create');
    Route::post('/certificates/guest', [GuestCertificateController::class, 'store'])->name('certificates.guest.store');

    Route::get('/certificates/{student?}', [CertificateController::class, 'index'])->name('certificates.index');

    Route::post('/certificates', [CertificateController::class, 'store'])->name('certificates.store');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])
        ->name('certificates.download');


    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referrals/{referral}/request-payout', [ReferralController::class, 'requestPayout'])
        ->name('referrals.request-payout');


    Route::get('/payments/onboarding', [PaymentController::class, 'payOnboardingFee'])->name('payments.onboarding');
    Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');

    Route::post('/payments/certificate', [PaymentController::class, 'payCertificateFee'])->name('payments.certificate');

    Route::post('/payments/subscribe/{plan:slug}', [PaymentController::class, 'paySubscription'])->name('payments.subscribe');
    Route::post('/payments/fund-wallet', [PaymentController::class, 'payFundWallet'])
        ->name('payments.fund-wallet');

    Route::post('/payments/from-wallet', [PaymentController::class, 'payCertificateFeeFromWalletBalance'])
        ->name('payments.certificate.wallet');

// Business-facing
    Route::get('/certificate-templates', [CertificateTemplateController::class, 'index'])
        ->name('certificate-templates.index');
    Route::patch('/certificate-templates/{certificateTemplate}/activate', [CertificateTemplateController::class, 'activate'])
        ->name('certificate-templates.activate');
    Route::delete('/certificate-templates/{certificateTemplate}', [CertificateTemplateController::class, 'destroy'])
        ->name('certificate-templates.destroy');

    Route::post('/certificate-template-requests', [CertificateTemplateRequestController::class, 'store'])
        ->name('certificate-template-requests.store');
    Route::delete('/certificate-template-requests/{certificateTemplateRequest}', [CertificateTemplateRequestController::class, 'destroy'])
        ->name('certificate-template-requests.destroy');

    Route::post('/certificate-templates/generate', [CertificateTemplateController::class, 'generate'])
        ->name('certificate-templates.generate');
    Route::post('/certificate-templates/{certificateTemplate}/reject', [CertificateTemplateController::class, 'reject'])
        ->name('certificate-templates.reject');

    Route::post('/pay/custom-cert-fee', [PaymentController::class, 'payCustomCertFee'])
        ->name('payments.custom-cert-fee');
//    Route::post('/pay/template-request-fee', [PaymentController::class, 'payTemplateRequestFee'])
//        ->name('payments.template-request-fee');

    Route::post('/pay/template-request-fee', [PaymentController::class, 'payTemplateRequestFee'])
        ->name('payments.template-request-fee');

        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');

    Route::post('/certificate-templates/default-builtin', [CertificateTemplateController::class, 'setDefaultBuiltin'])
        ->name('certificate-templates.default-builtin');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Admin-facing — adjust prefix/middleware to match however admin routes are grouped elsewhere in your app
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/certificate-template-requests', [CertificateTemplateRequestController::class, 'index'])
        ->name('certificate-template-requests.index');
    Route::patch('/certificate-template-requests/{certificateTemplateRequest}/claim', [CertificateTemplateRequestController::class, 'claim'])
        ->name('certificate-template-requests.claim');
    Route::post('/certificate-template-requests/{certificateTemplateRequest}/generate', [CertificateTemplateRequestController::class, 'generate'])
        ->name('certificate-template-requests.generate');
    Route::post('/certificate-template-requests/{certificateTemplateRequest}/decline', [CertificateTemplateRequestController::class, 'decline'])
        ->name('certificate-template-requests.decline');
});

require __DIR__ . '/auth.php';
