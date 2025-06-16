<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentTypeController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPackageController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/adminlogin', [AuthController::class, 'adminLogin']);


Route::resource('/client_types', ClientTypeController::class);
Route::resource('/countries', CountryController::class);


Route::middleware('auth:sanctum')->group(function () {
    // Main routes

    //Hospital
    Route::resource('/hospitals', HospitalController::class);

    // currency
    Route::resource('/currencies', CurrencyController::class);

    // Services (products, packages)
    // products
    Route::resource('/products', ProductController::class);

    // packages
    Route::resource('/packages', ProductPackageController::class);

    // Fleets
    Route::resource('/fleets', FleetController::class);

    // Clients
    Route::resource('/clients', ClientController::class);

    // Staffs
    Route::resource('/staffs', StaffController::class);

    // Documents
    Route::resource('/documents', DocumentController::class);

    // Document Type
    Route::resource('/documenttypes', DocumentTypeController::class);

    // Inquiry
    Route::resource('/inquiries', InquiryController::class);

    // Roles & Permissions
    Route::resource('/roles', RolesController::class);

    Route::resource('/permission', PermissionController::class);

    Route::get('/roles/{roleId}/give-permissions', [RolesController::class, 'addPermissionToRole']);
    Route::put('/roles/{roleId}/give-permissions', [RolesController::class, 'updatePermissionToRole']);

});