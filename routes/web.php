<?php

use App\Http\Controllers\ClientInfoController;
use App\Http\Controllers\ContratsController;
use App\Http\Controllers\MapController;
use App\Services\GetInterventionDetail;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('client.info', ['action' => 'login']))->name('login');

Route::match(['get', 'post'], '/ClientInfo', [ClientInfoController::class, 'handle'])
    ->name('client.info')
    ->middleware('check.auth');

Route::get('/ajax/jour/{date}', [ContratsController::class, 'getDayInfo']);
Route::get('/ajax/salaries/{codeAgence}', [ContratsController::class, 'getSalariesByAgence']);
Route::get('/ajax/histo/{numInt}', [ContratsController::class, 'getHistoByNumInt']);
Route::post('/ajax/update-heure', [ContratsController::class, 'updateHeure']);

Route::get('/api/rdv', [MapController::class, 'getRDV']);
Route::get('/carte', [MapController::class, 'generateMap']);
Route::post('/api/rdv/{num}/reassign', [MapController::class, 'reassign']);

Route::get('/api/intervention/{numInt}', function ($numInt, GetInterventionDetail $service) {
    return response()->json($service->getInterventionDetail($numInt));
});
