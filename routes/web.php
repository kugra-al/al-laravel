<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\GitHubOAuthController;

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



Auth::routes(['register'=>false]);
// No middleware
Route::get('/auth/github', [GitHubOAuthController::class, 'gitRedirect']);
Route::get('/auth/github/callback', [GitHubOAuthController::class, 'gitCallback']);

// HomeController sets auth middleware on _construct
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/change-password', [App\Http\Controllers\HomeController::class, 'changePassword'])->name('change-password');
Route::post('/change-password', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('update-password');

// Don't group around these or github logins don't always work for some reason
Route::group(['middleware' => ['auth','can:access items']], function() {
    Route::get('/items', [App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::post('/items/loadfile', [App\Http\Controllers\ItemController::class, 'loadItmFile'])->name('items.loadfile');
    Route::post('/items/export', [App\Http\Controllers\ItemController::class, 'index'])->name('items.export');

    Route::get('/map', [App\Http\Controllers\MapController::class, 'index'])->name('map.index');
    Route::get('/data/{type}', [App\Http\Controllers\DataController::class, 'index'])->name('data.index');
});
Route::group(['middleware' => ['auth','can:access admin']], function() {
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
    Route::get('/admin/jobs', [App\Http\Controllers\Admin\JobController::class, 'index'])->name('admin.jobs');
    Route::post('/admin/jobs/run', [App\Http\Controllers\Admin\JobController::class, 'runJob'])->name('admin.jobs.run');
    Route::resource('/admin/users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'update' => 'admin.users.update',
        'edit' => 'admin.users.edit',
        'destroy' => 'admin.users.destroy'
    ]);
    Route::get('/admin/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('admin.logs');
});
