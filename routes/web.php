<?php

use App\Http\Controllers\Admin\ListingManagementController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\TicketVerificationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MpesaCallbackController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Publicly accessible routes
// Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/support', [PageController::class, 'support'])->name('support');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

// Routes that require authentication
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // "My Tickets" (Purchased Tickets)
    Route::get('/my-tickets', [CartController::class, 'index'])->name('cart.index');

    // Ticket purchase page (only approved, unsold tickets are shown)
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // M-Pesa checkout (rate limited to slow down abuse)
    Route::post('/pay', [PaymentController::class, 'checkout'])
        ->middleware('throttle:10,1')
        ->name('pay');
    Route::get('/orders/{order}', [PaymentController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/status', [PaymentController::class, 'status'])
        ->middleware('throttle:60,1')
        ->name('orders.status');
    Route::get('/payment/success/{ticket}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');

    // A purchased ticket with its QR code (buyer only)
    Route::get('/my-tickets/{ticket}', [TicketController::class, 'showPurchasedTicket'])->name('tickets.purchased.show');

    // Private proof-of-purchase file (seller who uploaded it, or an admin)
    Route::get('/tickets/{ticket}/proof', [TicketController::class, 'showProof'])->name('tickets.proof');

    // Seller-specific routes
    Route::middleware('isSeller')->prefix('seller')->name('seller.')->group(function () {
        Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
        Route::resource('tickets', TicketController::class)->except(['index', 'show']);
    });

    // Admin-specific routes
    Route::middleware('isAdmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        // Listing Management
        Route::get('/tickets/pending', [TicketVerificationController::class, 'index'])->name('tickets.pending');
        Route::get('/listings', [ListingManagementController::class, 'index'])->name('listings.index');
        Route::get('/listings/{ticket}/edit', [ListingManagementController::class, 'edit'])->name('listings.edit');
        Route::put('/listings/{ticket}', [ListingManagementController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{ticket}', [ListingManagementController::class, 'destroy'])->name('listings.destroy');

        // Ticket Verification
        Route::get('/tickets/verify', [TicketVerificationController::class, 'index'])->name('tickets.index');
        Route::patch('/tickets/{ticket}/approve', [TicketVerificationController::class, 'approve'])->name('tickets.approve');
        // Payouts & refunds
        Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::patch('/payouts/{payout}/paid', [PayoutController::class, 'markPaid'])->name('payouts.paid');
        Route::patch('/orders/{order}/refunded', [PayoutController::class, 'markRefunded'])->name('orders.refunded');

        Route::patch('/tickets/{ticket}/reject', [TicketVerificationController::class, 'reject'])->name('tickets.reject');
    });
});

// Safaricom posts STK push results here. Public, CSRF-exempt (see
// bootstrap/app.php) and protected by a secret path segment.
Route::post('/mpesa/callback/{secret}', MpesaCallbackController::class)
    ->middleware('throttle:120,1')
    ->name('mpesa.callback');

// Include default auth routes (login, register, password reset, etc.)
require __DIR__.'/auth.php';