<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ResortManager\ManagerController;
use App\Http\Controllers\Client\ClientController;


// ═══════════════════════════════════════════════════════════════════════════
// CLIENT ROUTES
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard',                           [ClientController::class, 'dashboard'])->name('dashboard');
    
    // Bookings
    Route::get('/bookings',                            [ClientController::class, 'bookings'])->name('bookings');
    Route::post('/bookings/{booking}/cancel',          [ClientController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::get('/book/{resort}/{room}',                [ClientController::class, 'showBookingForm'])->name('book.form');
    Route::get('/check-availability/{resort}/{room}',  [ClientController::class, 'checkAvailability'])->name('check.availability');
    Route::post('/book/{resort}/{room}',               [ClientController::class, 'storeBooking'])->name('book.store');
    
    // Profile
    Route::get('/profile',                             [ClientController::class, 'profile'])->name('profile');
    Route::post('/profile',                            [ClientController::class, 'updateProfile'])->name('profile.update');
    
    // Notifications
    Route::post('/notifications/read',                 [ClientController::class, 'markNotificationsRead'])->name('notifications.read');
    
 // Contact page
    Route::get('/contact', function() {
        return view('client.contact');
    })->name('contact');
    
    // NEW: Client contact submit with different name
    Route::post('/contact-submit',                      [App\Http\Controllers\Client\ClientController::class, 'submitContact'])->name('contact.submit');
    
    // OTP
    Route::post('/change-password',                    [OtpController::class, 'changePassword'])->name('change.password');
    Route::post('/change-email/send-otp',              [OtpController::class, 'sendChangeEmailOtp'])->name('change.email.otp');
    Route::post('/change-email/verify-otp',            [OtpController::class, 'verifyChangeEmailOtp'])->name('change.email.verify');
    Route::get('/change-email/confirm',                [OtpController::class, 'showConfirmNewEmailForm'])->name('otp.change-email.confirm.form');
    Route::post('/change-email/confirm',               [OtpController::class, 'confirmNewEmail'])->name('otp.change-email.confirm');

     // Resend OTP routes for email change
    Route::post('/change-email/resend-otp',            [OtpController::class, 'resendChangeEmailOtp'])->name('change.email.resend');
    Route::post('/change-email/resend-new-otp',        [OtpController::class, 'resendNewEmailOtp'])->name('change.email.resend.new');
});