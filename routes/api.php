<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\HospitalController;
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
    Route::resource('/currencies', CurrencyController::class);
});