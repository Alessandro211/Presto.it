<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ArticleController;

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

Route::get('/', [PublicController::class, 'home'])->name('welcome');
Route::get('/crea', [ArticleController::class, 'create'])->middleware('auth')->name('create');
Route::get('/categoria/{category}', [PublicController::class, 'categoryShow'])->name('categoryShow');