<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController,
    LoginController,
    SuperAdminDashboardController,
    AdminLocalDashboardController,
    AdminFederationDashboardController,
    UserChampionatController,
    DelegationsController,
    ProvisionalRegistrationController,
    DefinitiveRegistrationController,
    NominativeRegistrationController,
    AdminRegistrationController,
    AccreditationController,
    SecurePreviewController,
    HotelController,
    RoomController,
    RoomReservationController,
    PaymentController,
    AccommodationDashboardController,
    AccommodationExportController
};

/*
|--------------------------------------------------------------------------
| AUTHENTICATION & PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get("/home", [HomeController::class, "index"]);
Route::get("/inscription", function() {
    return view("inscription");
})->name("inscription");

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:championat')->group(function () {

    // General Home Redirection
    Route::get('/home', function () {
        $role = auth('championat')->user()->role;
        return match ($role) {
            'super-admin'      => redirect()->route('dashboard.super-admin'),
            'admin-local'      => redirect()->route('dashboard.admin-local'),
            'admin-federation' => redirect()->route('dashboard.admin-federation'),
            default            => abort(403),
        };
    })->name('home');

    // Secure Preview
    Route::get('/secure-preview/{context}/{id}/{field}', [SecurePreviewController::class, 'preview'])
        ->name('secure.preview');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:super-admin'])->get('/dashboard/super-admin', [SuperAdminDashboardController::class, 'index'])
        ->name('dashboard.super_admin');

    Route::middleware(['role:admin-local'])->get('/dashboard/admin_local', [AdminLocalDashboardController::class, 'index'])
        ->name('dashboard.admin_local');

    Route::middleware(['role:admin-federation'])->get('/dashboard/admin_federation', [AdminFederationDashboardController::class, 'index'])
        ->name('dashboard.admin_federation');


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN & ADMIN LOCAL SHARED ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:super-admin,admin-local'])->group(function () {

        // User Management (Role Inscription)
        Route::controller(UserChampionatController::class)->prefix('role_inscription')->name('role_inscription')->group(function () {
            Route::get('/', 'index'); // name: role_inscription
            Route::post('/', 'store')->name('.store');
            Route::put('/{userChampionat}', 'update')->name('.update');
            Route::delete('/{userChampionat}', 'destroy')->name('.destroy');
            Route::patch('/{userChampionat}/toggle-status', 'toggleStatus')->name('.toggle-status');
        });

        // Delegations Management
        Route::controller(DelegationsController::class)->prefix('delegations')->name('delegations')->group(function () {
            Route::get('/', 'index'); // name: delegations
            Route::post('/', 'store')->name('.store');
            Route::put('/{delegation}', 'update')->name('.update');
            Route::delete('/{delegation}', 'destroy')->name('.destroy');
        });

        // Registrations Administration
        Route::controller(AdminRegistrationController::class)->prefix('admin/registrations')->name('admin.registrations.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{delegation}', 'show')->name('show');
            Route::post('/{delegation}/{type}/validate', 'validateStep')->name('validate');
            Route::post('/{delegation}/{type}/reject', 'rejectStep')->name('reject');
            Route::get('/{delegation}/download', 'downloadDelegation')->name('download');
            Route::get('/member/{member}/download', 'downloadMember')->name('member.download');
        });

        // Accreditations
        Route::controller(AccreditationController::class)->prefix('admin/accreditations')->name('admin.accreditations.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/delegations/{delegation}', 'showByDelegation')->name('showByDelegation');
            Route::get('/delegation/{delegation}', 'show')->name('show');
            Route::get('/{delegation}/badges/pdf', 'badgesPdf')->name('badges.pdf');
            Route::get('/export/excel', 'exportExcel')->name('export.excel');
            Route::post('/generate/{member}', 'generate')->name('generate');
            Route::post('/validate/{accreditation}', 'validateBadge')->name('validate');
            Route::post('/reject/{accreditation}', 'rejectBadge')->name('reject');
            Route::get('/print/{accreditation}', 'printSingle')->name('print');
            Route::get('/print/delegation/{delegation}', 'printDelegation')->name('print.delegation');
        });

        // Accommodation Administration
        Route::get('/accommodation', [AccommodationDashboardController::class, 'index'])->name('accommodation.dashboard');

        Route::controller(HotelController::class)->prefix('hotels')->name('hotels.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{hotel}', 'update')->name('update');
            Route::delete('/{hotel}', 'destroy')->name('destroy');
        });

        Route::controller(RoomController::class)->prefix('rooms')->name('rooms.')->group(function () {
            Route::post('/', 'store')->name('store');
            Route::put('/{room}', 'update')->name('update');
            Route::delete('/{room}', 'destroy')->name('destroy');
        });

        // Payments Administration
        Route::controller(PaymentController::class)->prefix('payments')->name('payments.')->group(function () {
            Route::patch('/{payment}/validate', 'validatePayment')->name('validate');
            Route::patch('/{payment}/reject', 'rejectPayment')->name('reject');
            Route::post('/bulk-validate', 'validateBulk')->name('bulk-validate');
            Route::post('/bulk-reject', 'rejectBulk')->name('bulk-reject');
        });

        // Reservation Management (Admin Side)
        Route::post('/reservations/{reservation}/cancel', [RoomReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::get('/accommodation/export/reservations', [AccommodationExportController::class, 'exportReservations'])->name('accommodation.export.reservations');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN FEDERATION ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin-federation'])->group(function () {

        // Provisional Registration
        Route::controller(ProvisionalRegistrationController::class)->prefix('provisional-registration')->name('registrations.provisional_registration')->group(function () {
            Route::get('/', 'index'); 
            Route::post('/', 'store')->name('.store');
            Route::post('/validate', 'validateStep')->name('.validate');
        });

        // Definitive Registration
        Route::controller(DefinitiveRegistrationController::class)->prefix('definitive-registration')->group(function () {
            Route::get('/', 'index')->name('definitive');
            Route::post('/', 'store')->name('definitive.store');
            Route::post('/validate', 'validateStep')->name('definitive.validateStep');
        });

        // Nominative Registration
        Route::controller(NominativeRegistrationController::class)->group(function () {
            Route::prefix('nominative-registration')->name('nominative.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{member}', 'update')->name('update');
                Route::delete('/{member}', 'destroy')->name('destroy');
            });
            Route::post('/delegation-info', 'storeDelegationInfo')->name('delegation.info.store');
        });

        // Accommodation (Federation View)
        // Explicitly uniquely named to avoid conflicts with shared 'accommodation.index' if necessary, 
        // though middleware separation allows same URI. Route names must be unique.
        Route::get('/federation/hotels', [HotelController::class, 'index'])->name('accommodation.federation.index'); 
        
        Route::get('/accommodation/hotel/{hotel}', [HotelController::class, 'show'])
            ->name('accommodation.federation.hotel.show');
            
        Route::post('/reservations', [RoomReservationController::class, 'store'])
            ->name('reservations.store');
            
        Route::post('/reservations/{reservation}/payment', [PaymentController::class, 'store'])
            ->name('payments.store');
    });

    /*
    |--------------------------------------------------------------------------
    | SHARED ACCOMMODATION ROUTES
    |--------------------------------------------------------------------------
    */
    // Accessible by all authenticated users (permission handled by controller)
    Route::get('/hotels-list', [HotelController::class, 'index'])->name('accommodation.index');

    Route::controller(RoomReservationController::class)->prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{reservation}', 'show')->name('show');
    });

});
