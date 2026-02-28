<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\BookingController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LawyerController as AdminLawyerController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController; 
use App\Http\Controllers\Admin\ClientRecordController as AdminClientRecordController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;

// Lawyer & Staff Controllers
use App\Http\Controllers\Lawyer\DashboardController as LawyerDashboardController;
use App\Http\Controllers\Lawyer\AppointmentController as LawyerAppointmentController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\AppointmentController as StaffAppointmentController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/booking/disabled-dates', [BookingController::class, 'getDisabledDates']);

/*
|--------------------------------------------------------------------------
| Client Booking Routes
|--------------------------------------------------------------------------
*/

Route::prefix('book')->name('book.')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::post('/step/{step}', [BookingController::class, 'processStep'])->name('step');
    Route::post('/back/{step}', [BookingController::class, 'goBack'])->name('back');
    Route::post('/submit', [BookingController::class, 'submit'])->name('submit');
    Route::get('/time-slots', [BookingController::class, 'getTimeSlots'])->name('timeslots');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Lawyers
    Route::resource('lawyers', AdminLawyerController::class);
    Route::post('lawyers/{lawyer}/approve', [AdminLawyerController::class, 'approve'])->name('lawyers.approve');
    Route::post('lawyers/{lawyer}/reject', [AdminLawyerController::class, 'reject'])->name('lawyers.reject');
    Route::post('lawyers/{lawyer}/suspend', [AdminLawyerController::class, 'suspend'])->name('lawyers.suspend');

    // Appointments
    Route::get('appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/queue', [AdminAppointmentController::class, 'queue'])->name('appointments.queue'); 
    Route::get('appointments/summary', [AdminAppointmentController::class, 'summary'])->name('appointments.summary');
    Route::get('appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');

    Route::post('appointments/{appointment}/confirm', [AdminAppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('appointments/{appointment}/decline', [AdminAppointmentController::class, 'decline'])->name('appointments.decline');
    Route::post('appointments/{appointment}/cancel', [AdminAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/start', [AdminAppointmentController::class, 'start'])->name('appointments.start');
    Route::post('appointments/{appointment}/complete', [AdminAppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{appointment}/no-show', [AdminAppointmentController::class, 'noShow'])->name('appointments.noShow');
    Route::post('appointments/{appointment}/check-in', [AdminAppointmentController::class, 'checkIn'])->name('appointments.checkIn');
    Route::post('appointments/{appointment}/note', [AdminAppointmentController::class, 'addNote'])->name('appointments.addNote');

    // Client Records
    Route::get('clients', [AdminClientRecordController::class, 'index'])->name('clients.index');
    Route::get('clients/summary', [AdminClientRecordController::class, 'summary'])->name('clients.summary');
    Route::get('clients/{clientRecord}', [AdminClientRecordController::class, 'show'])->name('clients.show');
    Route::get('clients/{clientRecord}/print', [AdminClientRecordController::class, 'print'])->name('clients.print');
    Route::put('clients/{clientRecord}', [AdminClientRecordController::class, 'update'])->name('clients.update');
    Route::post('clients/{clientRecord}/note', [AdminClientRecordController::class, 'addNote'])->name('clients.addNote');
    Route::patch('clients/{clientRecord}/status', [AdminClientRecordController::class, 'updateStatus'])->name('clients.updateStatus');

    // Staff Management
    Route::get('staff', [AdminStaffController::class, 'index'])->name('staff.index');
    Route::get('staff/create', [AdminStaffController::class, 'create'])->name('staff.create');
    Route::post('staff', [AdminStaffController::class, 'store'])->name('staff.store');
    Route::get('staff/{staff}/edit', [AdminStaffController::class, 'edit'])->name('staff.edit');
    Route::put('staff/{staff}', [AdminStaffController::class, 'update'])->name('staff.update');
    Route::delete('staff/{staff}', [AdminStaffController::class, 'destroy'])->name('staff.destroy');

    // Settings
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Appointment Queue Data
    Route::get('appointments/queue-data', [AdminAppointmentController::class, 'queueData'])->name('appointments.queueData');
});

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:staff'])->group(function () {

    Route::get('/', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/queue', [StaffDashboardController::class, 'queue'])->name('queue');
    Route::get('/appointments', [StaffAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [StaffAppointmentController::class, 'show'])->name('appointments.show');

    // Action Routes (Names reverted to camelCase to match Views)
    Route::patch('/appointments/{appointment}/confirm', [StaffAppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::patch('/appointments/{appointment}/check-in', [StaffAppointmentController::class, 'checkIn'])->name('appointments.checkIn'); // Fixed
    Route::patch('/appointments/{appointment}/decline', [StaffAppointmentController::class, 'decline'])->name('appointments.decline');
    Route::patch('/appointments/{appointment}/cancel', [StaffAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/note', [StaffAppointmentController::class, 'addNote'])->name('appointments.addNote'); // Fixed
    Route::patch('/appointments/{appointment}/start', [StaffAppointmentController::class, 'start'])->name('appointments.start');
    Route::patch('/appointments/{appointment}/complete', [StaffAppointmentController::class, 'complete'])->name('appointments.complete');
    Route::patch('/appointments/{appointment}/no-show', [StaffAppointmentController::class, 'noShow'])->name('appointments.noShow'); // Fixed

    // Appointment Queue Data
    Route::get('appointments/queue-data', [StaffAppointmentController::class, 'queueData'])->name('appointments.queueData');
});

/*
|--------------------------------------------------------------------------
| Lawyer Routes
|--------------------------------------------------------------------------
*/

Route::prefix('lawyer')->name('lawyer.')->middleware(['auth', 'role:lawyer'])->group(function () {

    Route::get('/', [LawyerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [LawyerDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [LawyerDashboardController::class, 'updateProfile'])->name('profile.update');

    Route::get('/schedule', [LawyerDashboardController::class, 'schedule'])->name('schedule');
    Route::put('/schedule', [LawyerDashboardController::class, 'updateSchedule'])->name('schedule.update');

    Route::get('/appointments', [LawyerAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [LawyerAppointmentController::class, 'show'])->name('appointments.show');

    Route::post('/appointments/{appointment}/note', [LawyerAppointmentController::class, 'addNote'])->name('appointments.addNote');
    Route::post('/appointments/{appointment}/start', [LawyerAppointmentController::class, 'start'])->name('appointments.start');
    Route::post('/appointments/{appointment}/complete', [LawyerAppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{appointment}/confirm', [LawyerAppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/decline', [LawyerAppointmentController::class, 'decline'])->name('appointments.decline');
    Route::post('/appointments/{appointment}/check-in', [LawyerAppointmentController::class, 'checkIn'])->name('appointments.checkIn');
    Route::post('/appointments/{appointment}/cancel', [LawyerAppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::post('/schedule/unavailability', [LawyerDashboardController::class, 'storeUnavailability'])->name('schedule.unavailability.store');
    Route::delete('/schedule/unavailability/{id}', [LawyerDashboardController::class, 'destroyUnavailability'])->name('schedule.unavailability.destroy');
});