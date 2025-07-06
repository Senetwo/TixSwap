<?php

use App\Http\Controllers\Admin\ListingManagementController;
use App\Http\Controllers\Admin\TicketVerificationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
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

// Route to view a single ticket for purchase.
// The controller ensures it's an 'approved' ticket.
 Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

// Routes that require authentication
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // "My Tickets" (Purchased Tickets)
    Route::get('/my-tickets', [CartController::class, 'index'])->name('cart.index');

    // The route to show the ticket purchase page
    Route::get('/tickets/{ticket}', [App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show');

    // The routes for handling the payment
    Route::post('/pay', [App\Http\Controllers\PaymentController::class, 'processSimulatedPayment'])->name('pay');
    Route::get('/payment/success/{ticket}', [App\Http\Controllers\PaymentController::class, 'paymentSuccess'])->name('payment.success');

    // The route to view the final purchased ticket
    Route::get('/my-tickets/{ticket}', [App\Http\Controllers\TicketController::class, 'showPurchasedTicket'])->name('tickets.purchased.show');

    // This route allows a user to view a ticket they have purchased.
    // It uses the ticket's ID or UUID for lookup.
    // Route::get('/my-tickets/{ticket}', [App\Http\Controllers\TicketController::class, 'showPurchasedTicket'])->name('tickets.purchased.show');

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
    Route::patch('/tickets/{ticket}/reject', [TicketVerificationController::class, 'reject'])->name('tickets.reject');
    });
});

// Include default auth routes (login, register, password reset, etc.)
require __DIR__.'/auth.php';