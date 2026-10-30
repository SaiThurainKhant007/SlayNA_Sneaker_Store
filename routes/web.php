<?php

use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Account\SavedCardController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\InventoryController;
use App\Http\Controllers\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Staff\PaymentController as StaffPaymentController;
use Illuminate\Support\Facades\Route;

// ---- Public: home + product browsing (UC_04) ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/brands', [PageController::class, 'brands'])->name('brands');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/new-arrivals', [ProductController::class, 'newArrivals'])->name('products.new');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/reviews', [ProductController::class, 'storeReview'])
    ->middleware(['auth', 'role:customer'])
    ->name('products.reviews.store');

// ---- Auth (UC_01) ----
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ---- My Profile: every signed-in user ----
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// ---- Customer: cart, checkout, account (Customer Scope) ----
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    Route::get('/account/orders', [OrderController::class, 'history'])->name('account.orders');
    Route::get('/account/orders/{order}', [OrderController::class, 'show'])->name('account.orders.show');
    Route::patch('/account/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('account.orders.cancel');

    Route::get('/account', [AccountDashboardController::class, 'show'])->name('account.dashboard');
    Route::put('/account', [AccountDashboardController::class, 'update'])->name('account.update');

    Route::get('/account/saved-cards', [SavedCardController::class, 'index'])->name('account.saved-cards');
    Route::post('/account/saved-cards', [SavedCardController::class, 'store'])->name('account.saved-cards.store');
    Route::delete('/account/saved-cards/{savedCard}', [SavedCardController::class, 'destroy'])->name('account.saved-cards.destroy');
});

// ---- Staff: inventory (UC_07), order processing (UC_08), payments (UC_09) ----
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::patch('/inventory/{variant}', [InventoryController::class, 'update'])->name('inventory.update');

    Route::get('/orders', [StaffOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}', [StaffOrderController::class, 'update'])->name('orders.update');

    Route::get('/payments', [StaffPaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}', [StaffPaymentController::class, 'update'])->name('payments.update');
});

// ---- Admin: products/categories/brands (UC_03), staff & customers (UC_05), reports (UC_06) ----
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/brands', [AdminBrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [AdminBrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [AdminBrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/promotions', [AdminPromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions', [AdminPromotionController::class, 'store'])->name('promotions.store');
    Route::put('/promotions/{promotion}', [AdminPromotionController::class, 'update'])->name('promotions.update');
    Route::delete('/promotions/{promotion}', [AdminPromotionController::class, 'destroy'])->name('promotions.destroy');

    Route::get('/gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
    Route::post('/gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
    Route::put('/gallery/{image}', [AdminGalleryController::class, 'update'])->name('gallery.update');
    Route::delete('/gallery/{image}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');

    Route::get('/staff', [AdminUserController::class, 'index'])->name('staff.index');
    Route::post('/staff', [AdminUserController::class, 'store'])->name('staff.store');
    Route::patch('/staff/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('staff.toggle-status');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}.csv', [AdminReportController::class, 'export'])
        ->whereIn('report', ['sales', 'top-products', 'inventory', 'customers'])
        ->name('reports.export');
});
