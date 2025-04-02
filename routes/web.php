<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotManController;
use App\Http\Controllers\BotManBankController;



Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/', [HomeController::class,'index']);
Route::match(['get', 'post'], '/botman', [BotManController::class, 'handle']);
Route::match(['get', 'post'], '/botman-bank', [BotManBankController::class, 'handle']);
Route::match(['get', 'post'], '/botman-triptoll', [BotManBankController::class, 'handle_triptoll']);

