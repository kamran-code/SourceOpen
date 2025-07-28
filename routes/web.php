<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotManController;
use App\Http\Controllers\BotManBankController;
use Illuminate\Support\Facades\DB;



Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/', [HomeController::class, 'index']);
Route::match(['get', 'post'], '/botman', [BotManController::class, 'handle']);
Route::match(['get', 'post'], '/botman-bank', [BotManBankController::class, 'handle']);
Route::match(['get', 'post'], '/botman-triptoll', [BotManBankController::class, 'handle_triptoll']);


Route::get('/qr', [HomeController::class, 'index1'])->name('qr.index');
Route::get('/qr/{id}/{eventId}', [HomeController::class, 'generate'])->name('qr.show');

Route::get('/clear', function () {
    DB::table('attendances')->truncate();
    return redirect()->back()->with('success', 'Attendance table truncated successfully.');
});
