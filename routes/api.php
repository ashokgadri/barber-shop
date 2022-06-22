<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('api/v1')->prefix('v1')->as('.v1')->group(function () {
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointment', [AppointmentController::class, 'store']);
});
