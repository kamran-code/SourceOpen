<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotManController;



Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/', [HomeController::class,'index']);
Route::match(['get', 'post'], '/botman', [BotManController::class, 'handle']);

