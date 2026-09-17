<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\NewsletterController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::view('/about', 'shop.pages.about')->name('about');
Route::view('/privacy-policy', 'shop.pages.privacy')->name('privacy');
Route::view('/terms-conditions', 'shop.pages.terms')->name('terms');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');

/*
 * Sign-in and sign-up.
 *
 * These are not behind the `guest` middleware: an already-signed-in visitor is
 * sent to the panel their role belongs to, which a blanket redirect cannot do.
 */
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:merchant'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::view('/', 'merchant.dashboard')->name('dashboard');
});
