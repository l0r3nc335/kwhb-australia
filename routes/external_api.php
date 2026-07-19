<?php

use App\Core\v1\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;

Route::middleware('external_api')
    ->group(function () {

        Route::get('/debug', function () {
            return response()->json(['status' => 'ok']);
        });

        Route::post('/users', [UserController::class, 'store']);
});
