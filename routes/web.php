<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaylistController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware('auth')->get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');

