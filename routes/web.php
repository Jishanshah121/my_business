<?php

declare(strict_types=1);

use App\Controllers\Admin\BusinessApprovalController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ProductManagementController;
use App\Controllers\Web\AccountController;
use App\Controllers\Web\BusinessController;
use App\Controllers\Web\BusinessRegisterController;
use App\Controllers\Web\CartController;
use App\Controllers\Web\CatalogController;
use App\Controllers\Web\CheckoutController;
use App\Controllers\Web\EmailVerificationController;
use App\Controllers\Web\EventPlannerController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\LoginController;
use App\Controllers\Web\OtpAuthController;
use App\Controllers\Web\PasswordResetController;
use App\Controllers\Web\ProductController;
use App\Controllers\Web\RegisterController;
use App\Http\Router;

/** @var Router $router */

// OTP Authentication API
$router->post('/api/auth/send-otp', [OtpAuthController::class, 'sendOtp']);
$router->post('/api/auth/verify-otp', [OtpAuthController::class, 'verifyOtp']);

// ---------------------------------------------------------------- Storefront
$router->get('/', [HomeController::class, 'index'])->name('home');

// Catalog & Products
$router->get('/categories', [CatalogController::class, 'index'])->name('catalog.index');
$router->get('/category/{slug:[a-z0-9-]+}', [CatalogController::class, 'show'])->name('catalog.category');
$router->get('/products/{slug:[a-z0-9-]+}', [ProductController::class, 'show'])->name('catalog.product');

// Event Planning & Party Box
$router->get('/party-box', [EventPlannerController::class, 'partyBox'])->name('events.party_box');
$router->get('/event-calculator', [EventPlannerController::class, 'calculator'])->name('events.calculator');

// B2B Wholesale
$router->get('/b2b', [BusinessController::class, 'index'])->name('b2b.portal');

// Cart & Checkout
$router->get('/cart', [CartController::class, 'index'])->name('cart');
$router->get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
$router->post('/api/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place');

// ------------------------------------------------------------- guest-only
$router->group(['middleware' => ['guest']], function (Router $router): void {
    $router->get('/login', [LoginController::class, 'show'])->name('login');
    $router->post('/login', [LoginController::class, 'store'])
        ->middleware('csrf', 'throttle:login')->name('login.store');

    $router->get('/register', [RegisterController::class, 'show'])->name('register');
    $router->post('/register', [RegisterController::class, 'store'])
        ->middleware('csrf', 'throttle:register')->name('register.store');

    $router->get('/register/business', [BusinessRegisterController::class, 'show'])->name('register.business');
    $router->post('/register/business', [BusinessRegisterController::class, 'store'])
        ->middleware('csrf', 'throttle:register')->name('register.business.store');

    $router->get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    $router->post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('csrf', 'throttle:password_reset')->name('password.email');

    $router->get('/reset-password/{token:[a-f0-9]{64}}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    $router->post('/reset-password/{token:[a-f0-9]{64}}', [PasswordResetController::class, 'reset'])
        ->middleware('csrf', 'throttle:password_reset')->name('password.update');
});

// --------------------------------------------------------- email verification
// The verify link is public: it arrives by email and may be opened in a
// browser with no session.
$router->get('/verify-email/{token:[a-f0-9]{64}}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

$router->group(['middleware' => ['auth']], function (Router $router): void {
    $router->get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    $router->post('/email/verify/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('csrf', 'throttle:password_reset')->name('verification.resend');

    $router->post('/logout', [LoginController::class, 'destroy'])->middleware('csrf')->name('logout');
});

// -------------------------------------------------------------- account area
$router->group(['prefix' => 'account', 'middleware' => ['auth']], function (Router $router): void {
    $router->get('/', [AccountController::class, 'dashboard'])->name('account');

    $router->get('/profile', [AccountController::class, 'profile'])->name('account.profile');
    $router->post('/profile', [AccountController::class, 'updateProfile'])
        ->middleware('csrf')->name('account.profile.update');

    $router->get('/security', [AccountController::class, 'security'])->name('account.security');
    $router->post('/security/password', [AccountController::class, 'updatePassword'])
        ->middleware('csrf')->name('account.password.update');
    $router->post('/security/sessions', [AccountController::class, 'signOutOtherSessions'])
        ->middleware('csrf')->name('account.sessions.destroy');

    $router->get('/business', [AccountController::class, 'business'])->name('account.business');

    $router->post('/addresses', [AccountController::class, 'storeAddress'])
        ->middleware('csrf')->name('account.addresses.store');
    $router->post('/addresses/{id:\d+}/edit', [AccountController::class, 'updateAddress'])
        ->middleware('csrf')->name('account.addresses.update');
    $router->post('/addresses/{id:\d+}/default', [AccountController::class, 'setDefaultAddress'])
        ->middleware('csrf')->name('account.addresses.default');
    $router->post('/addresses/{id:\d+}/delete', [AccountController::class, 'deleteAddress'])
        ->middleware('csrf')->name('account.addresses.delete');
});

// ---------------------------------------------------------------- admin area
// Every admin route is permission-gated. `can:` implies authentication, so the
// group is guarded by the permission the page actually needs, not by a role.
$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function (Router $router): void {
    $router->get('/', [DashboardController::class, 'index'])
        ->middleware('can:reports.view')->name('admin');

    // Products & Catalog Studio (Amazon / Flipkart / Meesho Style)
    $router->get('/products', [ProductManagementController::class, 'index'])
        ->middleware('can:products.view')->name('admin.products');
    $router->get('/products/create', [ProductManagementController::class, 'create'])
        ->middleware('can:products.create')->name('admin.products.create');
    $router->post('/products', [ProductManagementController::class, 'store'])
        ->middleware('csrf', 'can:products.create')->name('admin.products.store');
    $router->get('/products/{id:\d+}/edit', [ProductManagementController::class, 'edit'])
        ->middleware('can:products.edit')->name('admin.products.edit');
    $router->post('/products/{id:\d+}/update', [ProductManagementController::class, 'update'])
        ->middleware('csrf', 'can:products.edit')->name('admin.products.update');
    $router->post('/products/{id:\d+}/delete', [ProductManagementController::class, 'destroy'])
        ->middleware('csrf', 'can:products.delete')->name('admin.products.delete');

    $router->get('/business-accounts', [BusinessApprovalController::class, 'index'])
        ->middleware('can:b2b.view')->name('admin.b2b');

    $router->post('/business-accounts/{id:\d+}/approve', [BusinessApprovalController::class, 'approve'])
        ->middleware('csrf', 'can:b2b.approve')->name('admin.b2b.approve');

    $router->post('/business-accounts/{id:\d+}/reject', [BusinessApprovalController::class, 'reject'])
        ->middleware('csrf', 'can:b2b.approve')->name('admin.b2b.reject');
});
