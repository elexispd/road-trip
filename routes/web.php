<?php

use App\Http\Controllers\DestinationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/destinations/', [DestinationController::class, 'index'])->name('destination.index');
Route::get('/destinations/create', [DestinationController::class, 'create'])->name('destination.create');
Route::post('/destinations/create', [DestinationController::class, 'store'])->name('destination.store');
Route::delete('/destinations/{id}', [DestinationController::class, 'destroy'])->name('destination.destroy');
Route::post('/destinations/reorder', [DestinationController::class, 'reorder'])->name('destination.reorder');
