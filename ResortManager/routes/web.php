<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ResortManager\ManagerController;
use App\Http\Controllers\Client\ClientController;


Route::middleware(['auth', 'resort_manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard',                           [ManagerController::class, 'dashboard'])->name('dashboard');
    
    // Resort
    Route::get('/resort',                              [ManagerController::class, 'myResortShow'])->name('resort');
    Route::post('/resort',                             [ManagerController::class, 'updateResort'])->name('resort.update');
    
    // Photos
    Route::get('/photos',                              [ManagerController::class, 'photos'])->name('photos');
    Route::post('/photos/upload',                      [ManagerController::class, 'uploadPhoto'])->name('photos.upload');
    Route::delete('/photos/{photo}',                   [ManagerController::class, 'deletePhoto'])->name('photos.delete');
    Route::post('/photos/{photo}/cover',               [ManagerController::class, 'setCover'])->name('photos.cover');
    
    // Rooms
    Route::get('/rooms',                               [ManagerController::class, 'rooms'])->name('rooms');
    Route::post('/rooms',                              [ManagerController::class, 'addRoom'])->name('rooms.add');
    Route::put('/rooms/{room}',                        [ManagerController::class, 'updateRoom'])->name('rooms.update');
    Route::delete('/rooms/{room}',                     [ManagerController::class, 'deleteRoom'])->name('rooms.delete');
    
    // Bookings
    Route::get('/bookings',                            [ManagerController::class, 'bookings'])->name('bookings');
    Route::post('/bookings/{booking}/confirm',         [ManagerController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/cancel',          [ManagerController::class, 'cancelBooking'])->name('bookings.cancel');
    
    // Profile
    Route::get('/profile',                             [ManagerController::class, 'profile'])->name('profile');
    Route::post('/profile',                            [ManagerController::class, 'updateProfile'])->name('profile.update');
    
    // Notifications
    Route::post('/notifications/read',                 [ManagerController::class, 'markNotificationsRead'])->name('notifications.read');
    
    // Contact Admin
    Route::get('/contact',                             [ManagerController::class, 'contact'])->name('contact');
    Route::post('/contact-submit',                     [App\Http\Controllers\ResortManager\ManagerController::class, 'submitContact'])->name('contact.submit');
    
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
