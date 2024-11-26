<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SparqlController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\TriplesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware(['log.request'])->group(function () {
//     Route::resource('resources', ResourcesController::class);
// });


Route::post('/sparql/query', [SparqlController::class, 'query']);
Route::get('/resources', [ResourcesController::class, 'index']);

Route::post('executeScript', [TriplesController::class, 'executeScript']);