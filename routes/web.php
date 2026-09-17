<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\NewsletterController;
use App\Http\Controllers\Shop\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

/*
 * Catalog. Categories and products are addressed by slug rather than id, so the
 * URLs read as names and stay stable if rows are ever renumbered.
 */
Route::get('/shop', [CatalogController::class, 'index'])->name('shop');
Route::get('/category/{category}', [CatalogController::class, 'category'])->name('category');
Route::get('/product/{product}', [CatalogController::class, 'product'])->name('product');
Route::get('/search', SearchController::class)->name('search');

/*
 * The basket.
 *
 * Open to signed-out visitors by design: a guest builds a basket in the session
 * and it is folded into their account the moment they sign in. Every write is
 * scoped to the caller inside the Cart service.
 */
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/fetch', [CartController::class, 'fetch'])->name('cart.fetch');
Route::post('/cart/details', [CartController::class, 'details'])->name('cart.details');
Route::post('/cart/total', [CartController::class, 'total'])->name('cart.total');

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
