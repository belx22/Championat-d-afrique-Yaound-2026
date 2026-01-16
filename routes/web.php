<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserChampionatController;
use App\Http\Controllers\DelegationsController;
use App\Http\Controllers\ProvisionalRegistrationController;
use App\Http\Controllers\AdminLocal\ProvisionalValidationController;
use App\Http\Controllers\{
    SuperAdminDashboardController,
    AdminLocalDashboardController,
    AdminFederationDashboardController,
    DefinitiveRegistrationController,
    definitiveController,
    NominativeRegistrationController,
    AdminRegistrationController,
    AccreditationController,
    SecurePreviewController,
};

use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::get('/', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::middleware('auth:championat')->get('/home', function () {

    $role = auth('championat')->user()->role;

    return match ($role) {
        'super-admin'      => redirect()->route('dashboard.super-admin'),
        'admin-local'      => redirect()->route('dashboard.admin-local'),
        'admin-federation' => redirect()->route('dashboard.admin-federation'),
        default            => abort(403),
    };
})->name('home');


Route::get(
    '/secure-preview/{context}/{id}/{field}',
    [SecurePreviewController::class, 'preview']
)
->name('secure.preview')
->middleware('auth:championat');


/////////////////////////////////////////////////////

Route::middleware(['auth:championat'])->group(function () {

  
    Route::middleware(['role:super-admin'])->get('/dashboard/super-admin', [SuperAdminDashboardController::class, 'index']
    )->name('dashboard.super_admin');
    Route::middleware(['role:admin-local'])->get(
        '/dashboard/admin_local',
        [AdminLocalDashboardController::class, 'index']
    )->name('dashboard.admin_local');

    Route::middleware(['role:admin-federation'])->get(
        '/dashboard/admin_federation',
        [AdminFederationDashboardController::class, 'index']
    )->name('dashboard.admin_federation');
});

////////////////////////////////////////////////////////////////////

Route::get("home", [HomeController::class, "index"]);

/////////////////////////////////////////////////////////////////////////



Route::get("/inscription",function(){
    return view("inscription");

})->name("inscription");


// listes des liens pour la gestion des utilisateurs 


Route::middleware(['auth:championat','role:super-admin,admin-local'])->group(function () {

    Route::get('/role_inscription', [UserChampionatController::class, 'index'])
        ->name('role_inscription');

    Route::post('/role_inscription', [UserChampionatController::class, 'store'])
        ->name('role_inscription.store');

    Route::put('/role_inscription/{userChampionat}', [UserChampionatController::class, 'update'])
        ->name('role_inscription.update');

    Route::patch('/role_inscription/{userChampionat}/toggle-status', [UserChampionatController::class, 'toggleStatus'])
        ->name('role_inscription.toggle-status');

    Route::delete('/role_inscription/{userChampionat}', [UserChampionatController::class, 'destroy'])
        ->name('role_inscription.destroy');
});


// listes des liens ver les delegations 

Route::middleware(['auth:championat','role:super-admin,admin-local'])->group(function () {



    Route::get('/delegations', [DelegationsController::class, 'index'])
        ->name('delegations');

    Route::post('/delegations', [DelegationsController::class, 'store'])
        ->name('delegations.store');

    Route::put('/delegations/{delegation}', [DelegationsController::class, 'update'])
        ->name('delegations.update');

    Route::delete('/delegations/{delegation}', [DelegationsController::class, 'destroy'])
        ->name('delegations.destroy');

});


//liste des liens vers 

Route::middleware(['auth:championat','role:admin-federation'])->group(function () {
    Route::get('/provisional-registration', [ProvisionalRegistrationController::class, 'index'])
        ->name('registrations.provisional_registration');

    Route::post('/provisional-registration', [ProvisionalRegistrationController::class, 'store'])
        ->name('registrations.provisional_registration.store');

    Route::post('/provisional-registration/validate', [ProvisionalRegistrationController::class, 'validateStep'])
        ->name('registrations.provisional_registration.validate');
});


///////



//////
Route::middleware([
    'auth:championat',
    //'provisional.validated',
    'role:admin-federation'
])->group(function () {

    Route::get('/definitive-registration', [DefinitiveRegistrationController::class, 'index'])
        ->name('definitive');


    Route::post('/definitive-registration', [DefinitiveRegistrationController::class, 'validateStep'])
        ->name('definitive.validateStep');


        Route::post('/definitive-registration', [DefinitiveRegistrationController::class, 'store'])
        ->name('definitive.store');

});








/////////////////////////


Route::middleware(['auth:championat','role:admin-federation'])->group(function () {

    Route::get('/nominative-registration',
        [NominativeRegistrationController::class,'index'])
        ->name('nominative.index');

    Route::put('/nominative-registration/{member}',
        [NominativeRegistrationController::class,'update'])
        ->name('nominative.update');

    Route::post('/delegation-info',
        [NominativeRegistrationController::class,'storeDelegationInfo'])
        ->name('delegation.info.store');



    Route::post('/nominative-registration',
        [NominativeRegistrationController::class,'store'])
        ->name('nominative.store');


    Route::delete('/nominative-registration/{member}',
        [NominativeRegistrationController::class,'destroy'])
        ->name('nominative.destroy');
});

/*


Route::middleware(['auth:championat', 'role:super-admin'])->group(function () {

    Route::get('/users', [UserChampionatController::class, 'index']);
    Route::post('/users', [UserChampionatController::class, 'store']);
});


Route::middleware(['auth:championat', 'role:super-admin,admin-local'])->group(function () {

    Route::get('/admin-local/dashboard', [AdminLocalDashboardController::class, 'index']);

    Route::get('/admin-local/provisional-registrations',
        [ProvisionalValidationController::class, 'index']
    );
});

Route::middleware(['auth:championat', 'role:admin-federation'])->group(function () {

    Route::get('/provisional-registration',
        [ProvisionalRegistrationController::class, 'index']
    );

    Route::post('/provisional-registration',
        [ProvisionalRegistrationController::class, 'store']
    );
});

*/



/*

use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomReservationController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| HÉBERGEMENT – ADMIN
|--------------------------------------------------------------------------

Route::middleware(['auth:championat', 'role:super-admin,admin-local'])->group(function () {

    // Page principale hébergement
    Route::get('/accommodation', [HotelController::class, 'index'])
        ->name('accommodation.index');

    // Création hôtel
    Route::post('/hotels', [HotelController::class, 'store'])
        ->name('hotels.store');

    // Ajout chambre à un hôtel
    Route::post('/rooms', [RoomController::class, 'store'])
        ->name('rooms.store');
});

/*
|--------------------------------------------------------------------------
| VALIDATION PAIEMENT – ADMIN LOCAL
|--------------------------------------------------------------------------

Route::middleware(['auth:championat', 'role:admin-local'])->group(function () {

    Route::patch('/payments/{payment}/validate', [PaymentController::class, 'validatePayment'])
        ->name('payments.validate');

    Route::patch('/payments/{payment}/reject', [PaymentController::class, 'rejectPayment'])
        ->name('payments.reject');
});

/*
|--------------------------------------------------------------------------
| RÉSERVATIONS – ADMIN FÉDÉRATION
|--------------------------------------------------------------------------

Route::middleware([
    'auth:championat',
    'role:admin-federation',
    'nominative.validated'
])->group(function () {

    // Réserver des chambres
    Route::post('/reservations', [RoomReservationController::class, 'store'])
        ->name('reservations.store');

    // Upload du reçu de paiement
    Route::post('/reservations/{reservation}/payment', [PaymentController::class, 'store'])
        ->name('payments.store');
});


*/



Route::middleware(['auth:championat','role:super-admin,admin-local'])->group(function () {

    Route::get('/admin/registrations', 
        [AdminRegistrationController::class, 'index']
    )->name('admin.registrations.index');

    Route::get('/admin/registrations/{delegation}', 
        [AdminRegistrationController::class, 'show']
    )->name('admin.registrations.show');

    Route::post('/admin/registrations/{delegation}/{type}/validate', 
        [AdminRegistrationController::class, 'validateStep']
    )->name('admin.registrations.validate');

    Route::post('/admin/registrations/{delegation}/{type}/reject', 
        [AdminRegistrationController::class, 'rejectStep']
    )->name('admin.registrations.reject');

    Route::get('/admin/registrations/{delegation}/download', 
        [AdminRegistrationController::class, 'downloadDelegation']
    )->name('admin.registrations.download');

    Route::get('/admin/registrations/member/{member}/download', 
        [AdminRegistrationController::class, 'downloadMember']
    )->name('admin.registrations.member.download');
});



//////////////////////////


Route::middleware(['auth:championat','role:super-admin,admin-local'])
    ->prefix('admin/accreditations')
    ->name('admin.accreditations.')
    ->group(function () {

        Route::get(
                '/{delegation}/badges/pdf',
                [AccreditationController::class, 'badgesPdf']
            )->name('badges.pdf');

        Route::get(
                '/export/excel',
                [AccreditationController::class, 'exportExcel']
            )->name('export.excel');

        Route::get('/delegations/{delegation}', [AccreditationController::class,'showByDelegation'])->name('showByDelegation');
        Route::get('/', [AccreditationController::class,'index'])->name('index');
        Route::get('/delegation/{delegation}', [AccreditationController::class,'show'])->name('show');

        Route::post('/generate/{member}', [AccreditationController::class,'generate'])->name('generate');

        Route::post('/validate/{accreditation}', [AccreditationController::class,'validateBadge'])->name('validate');
        Route::post('/reject/{accreditation}', [AccreditationController::class,'rejectBadge'])->name('reject');

        Route::get('/print/{accreditation}', [AccreditationController::class,'printSingle'])->name('print');
        Route::get('/print/delegation/{delegation}', [AccreditationController::class,'printDelegation'])->name('print.delegation');
});





/*
|--------------------------------------------------------------------------
| ACCOMMODATION ROUTES - Role-based access
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:championat'])->group(function () {
    
    // Hotel management (Super Admin / Local Admin only)
    Route::middleware(['role:super-admin,admin-local'])->group(function () {
       
        Route::get('/accommodation', [\App\Http\Controllers\AccommodationDashboardController::class, 'index'])
            ->name('accommodation.dashboard');
        Route::get('/hotels', [HotelController::class, 'index'])->name('accommodation.index');
        Route::post('/hotels', [HotelController::class, 'store'])->name('hotels.store');
        Route::put('/hotels/{hotel}', [HotelController::class, 'update'])->name('hotels.update');
        Route::delete('/hotels/{hotel}', [HotelController::class, 'destroy'])->name('hotels.destroy');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // Payment validation (Super Admin / Local Admin)
        Route::patch('/payments/{payment}/validate', [PaymentController::class, 'validatePayment'])
            ->name('payments.validate');
        Route::patch('/payments/{payment}/reject', [PaymentController::class, 'rejectPayment'])
            ->name('payments.reject');
        Route::post('/payments/bulk-validate', [PaymentController::class, 'validateBulk'])
            ->name('payments.bulk-validate');
        Route::post('/payments/bulk-reject', [PaymentController::class, 'rejectBulk'])
            ->name('payments.bulk-reject');
        
        // Manual cancellation
        Route::post('/reservations/{reservation}/cancel', [RoomReservationController::class, 'cancel'])
            ->name('reservations.cancel');
        
        // Export reservations
        Route::get('/accommodation/export/reservations', [\App\Http\Controllers\AccommodationExportController::class, 'exportReservations'])
            ->name('accommodation.export.reservations');
    });
    
    // Reservations - All roles can access (role-based views in controller)
    Route::get('/reservations', [RoomReservationController::class, 'index'])
        ->name('reservations.index');
    Route::get('/reservations/{reservation}', [RoomReservationController::class, 'show'])
        ->name('reservations.show');
    
    // Hotel details & reservation creation (Federation Admin only)
    Route::middleware(['role:admin-federation'])->group(function () {
        Route::get('/hotels', [HotelController::class, 'index'])->name('accommodation.index');
        Route::get('/accommodation/hotel/{hotel}', [HotelController::class, 'show'])
            ->name('accommodation.federation.hotel.show');
        Route::post('/reservations', [RoomReservationController::class, 'store'])
            ->name('reservations.store');
        Route::post('/reservations/{reservation}/payment', [PaymentController::class, 'store'])
            ->name('payments.store');
    });
});
