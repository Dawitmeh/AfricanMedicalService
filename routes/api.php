<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPackageController;
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

});