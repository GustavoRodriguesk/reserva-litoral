<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PlanningBoardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RatePlanController;

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
    Route::post('/reservations/{reservation}/invoice', [InvoiceController::class, 'store'])
        ->whereUuid('reservation')
        ->name('invoices.store');

    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->whereUuid('invoice')
        ->name('invoices.show');

    // Planning Board
    Route::get('/planning', PlanningBoardController::class)->name('planning.index');
    Route::post('/planning/reallocate', [PlanningBoardController::class, 'reallocate'])->name('planning.reallocate');

    // Quartos
    Route::resource('rooms', RoomController::class);

    Route::post('/rooms/{room}/block', [RoomController::class, 'block'])
        ->name('rooms.block');

    Route::post('/rooms/{room}/unblock', [RoomController::class, 'unblock'])
        ->name('rooms.unblock');

    // Configurações da Pousada
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/hotel', [SettingsController::class, 'updateHotel'])->name('settings.hotel');
    Route::put('/settings/operation', [SettingsController::class, 'updateSettings'])->name('settings.operation');

    // Relatórios
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    // Tarifários
    Route::resource('rate-plans', RatePlanController::class);

    // Governança / Housekeeping
    Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::patch('/housekeeping/rooms/{room}/status', [HousekeepingController::class, 'updateStatus'])->name('housekeeping.update-status');

    // Categorias de Quarto
    Route::resource('room-types', RoomTypeController::class);
    Route::post('/room-types/{roomType}/images', [RoomTypeController::class, 'uploadImage'])->name('room-types.images.upload');
    Route::delete('/room-types/{roomType}/images/{image}', [RoomTypeController::class, 'deleteImage'])->name('room-types.images.delete');
    Route::post('/room-types/{roomType}/images/reorder', [RoomTypeController::class, 'reorderImages'])->name('room-types.images.reorder');

    // Comodidades
    Route::resource('amenities', AmenityController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/auth.php';
