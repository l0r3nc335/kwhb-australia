<?php

use Illuminate\Support\Facades\Route;
use App\Core\v1\Resource\Http\Controllers\ResourceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'resources'], function () {
    Route::get('/serve/{filename}', [ResourceController::class, 'serveResource'])
        ->where('filename', '.*');
});

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');
