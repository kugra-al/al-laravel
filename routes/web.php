<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;

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

Route::get('/', [HomeController::class, 'index']);



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/items', [App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
Route::post('/items/loadfile', [App\Http\Controllers\ItemController::class, 'loadItmFile'])->name('items.loadfile');
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
Route::get('/admin/jobs', [App\Http\Controllers\Admin\JobController::class, 'index'])->name('admin.jobs');
Route::post('/admin/jobs/run', [App\Http\Controllers\Admin\JobController::class, 'runJob'])->name('admin.jobs.run');
Route::post('/items/export', [App\Http\Controllers\ItemController::class, 'index'])->name('items.export');
