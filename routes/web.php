<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategories;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EarningsController;
use App\Http\Controllers\Admin\MerchantApprovalController;
use App\Http\Controllers\Admin\OrderController as AdminOrders;
use App\Http\Controllers\Admin\ProductController as AdminProducts;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController as AdminUsers;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MerchantRegisterController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Merchant\DashboardController as MerchantDashboard;
use App\Http\Controllers\Merchant\ProductController as MerchantProducts;
use App\Http\Controllers\Merchant\SalesController as MerchantSales;
use App\Http\Controllers\Merchant\StockController as MerchantStock;
use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\InvoiceController;
use App\Http\Controllers\Shop\NewsletterController;
use App\Http\Controllers\Shop\OrderController;
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
 * Checking out, and everything that follows from it. Customers only: staff
 * accounts have panels of their own and never hold a basket.
 */
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout/billing', [CheckoutController::class, 'storeBilling'])->name('checkout.billing');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/payment', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])
        ->name('checkout.confirmation');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/invoice', InvoiceController::class)->name('orders.invoice');

    Route::get('/account', [AccountController::class, 'edit'])->name('account');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
});

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

// Anyone may apply to supply; approval is what grants access.
Route::get('/become-a-supplier', [MerchantRegisterController::class, 'create'])->name('merchant.register');
Route::post('/become-a-supplier', [MerchantRegisterController::class, 'store'])->name('merchant.register.store');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');

    Route::resource('products', AdminProducts::class)->except('show');
    Route::resource('categories', AdminCategories::class)->except('show');

    Route::get('orders', [AdminOrders::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrders::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrders::class, 'updateStatus'])->name('orders.status');

    Route::resource('users', AdminUsers::class)->except('show');

    Route::get('merchants', [MerchantApprovalController::class, 'index'])->name('merchants.index');
    Route::patch('merchants/{user}/approve', [MerchantApprovalController::class, 'approve'])->name('merchants.approve');
    Route::patch('merchants/{user}/reject', [MerchantApprovalController::class, 'reject'])->name('merchants.reject');

    Route::get('earnings', EarningsController::class)->name('earnings');

    Route::get('reports/orders', [ReportController::class, 'orders'])->name('reports.orders');
    Route::get('reports/users', [ReportController::class, 'users'])->name('reports.users');
    Route::get('reports/earnings', [ReportController::class, 'earnings'])->name('reports.earnings');

    Route::get('reviews', [ProductReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{product}/approve', [ProductReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('reviews/{product}/reject', [ProductReviewController::class, 'reject'])->name('reviews.reject');
});

Route::middleware(['auth', 'role:merchant'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/', MerchantDashboard::class)->name('dashboard');

    Route::resource('products', MerchantProducts::class)->except('show');

    Route::get('stock', [MerchantStock::class, 'index'])->name('stock.index');
    Route::patch('stock/{product}', [MerchantStock::class, 'update'])->name('stock.update');

    Route::get('sales', [MerchantSales::class, 'index'])->name('sales');
});
