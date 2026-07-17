<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\GuestController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Hóspedes
    Route::resource('dashboard/guests', GuestController::class)->names('guests');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reservas — listagem e busca de disponibilidade
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');

    // Reservas — APIs auxiliares para o Wizard
    Route::get('/reservations/api/availability', [ReservationController::class, 'apiAvailability'])->name('reservations.api.availability');
    Route::get('/reservations/api/guests', [ReservationController::class, 'apiGuests'])->name('reservations.api.guests');
    Route::post('/reservations/api/guests', [ReservationController::class, 'apiStoreGuest'])->name('reservations.api.store_guest');

    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations/availability', [ReservationController::class, 'availability'])->name('reservations.availability');
    Route::get('/reservations/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

    // Reservas — detalhe e ações (UUID constraint impede colisão com /create, /confirm etc.)
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])
        ->whereUuid('reservation')
        ->name('reservations.show');

    Route::post('/reservations/{reservation}/checkin', [ReservationController::class, 'checkin'])
        ->whereUuid('reservation')
        ->name('reservations.checkin');

    Route::post('/reservations/{reservation}/checkout', [ReservationController::class, 'checkout'])
        ->whereUuid('reservation')
        ->name('reservations.checkout');

    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->whereUuid('reservation')
        ->name('reservations.cancel');

    Route::post('/reservations/{reservation}/charges', [ReservationController::class, 'addCharge'])
        ->whereUuid('reservation')
        ->name('reservations.charges.store');

    Route::post('/reservations/{reservation}/payments', [ReservationController::class, 'addPayment'])
        ->whereUuid('reservation')
        ->name('reservations.payments.store');

    // Faturas
    Route::post('/reservations/{reservation}/invoice', [\App\Http\Controllers\InvoiceController::class, 'store'])
        ->whereUuid('reservation')
        ->name('invoices.store');

    Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])
        ->whereUuid('invoice')
        ->name('invoices.show');
});

require __DIR__.'/auth.php';
