<?php

use App\Http\Controllers\Booking\BookingFlowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| White-label booking flow (public, multi-tenant)
|--------------------------------------------------------------------------
| Each hotel is reachable at /book/{slug}. In production a tenant can also be
| served from its own (sub)domain mapped to the same routes.
*/
Route::prefix('book/{hotel}')->group(function () {
    Route::get('/', [BookingFlowController::class, 'show'])->name('booking.show');
    Route::post('/events', [BookingFlowController::class, 'storeEvent'])->name('booking.events');
    Route::post('/quote', [BookingFlowController::class, 'quote'])->name('booking.quote');
    Route::post('/bookings', [BookingFlowController::class, 'store'])->name('booking.store');
    Route::post('/bookings/{reference}/complete', [BookingFlowController::class, 'complete'])->name('booking.complete');
    Route::get('/confirmation/{reference}', [BookingFlowController::class, 'confirmation'])->name('booking.confirmation');
});
