<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\UserController;

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
Route::group(['middleware' => ['can:access items']], function() {
    Route::get('/items', [App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::post('/items/loadfile', [App\Http\Controllers\ItemController::class, 'loadItmFile'])->name('items.loadfile');
    Route::post('/items/export', [App\Http\Controllers\ItemController::class, 'index'])->name('items.export');
});
Route::group(['middleware' => ['can:access admin']], function() {
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
    Route::get('/admin/jobs', [App\Http\Controllers\Admin\JobController::class, 'index'])->name('admin.jobs');
    Route::post('/admin/jobs/run', [App\Http\Controllers\Admin\JobController::class, 'runJob'])->name('admin.jobs.run');
    Route::resource('/admin/users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'update' => 'admin.users.update'
    ]);
});
