<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;

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

Route::get('/', [ContactController::class, 'index']);

Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contacts.confirm');

Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contacts.store');

Route::get('/thanks', [ContactController::class, 'thanks'])
    ->name('contacts.thanks');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.index');
});

Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])
    ->name('admin.show');


Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
    ->name('admin.destroy');

// CSVエクスポート
Route::get('/contacts/export', [AdminController::class, 'export'])
    ->name('contacts.export');



Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
    ->name('admin.destroy');


Route::post('/admin/tags', [AdminController::class, 'storeTag'])
    ->name('admin.tags.store');

Route::get('/admin/tags/{tag}/edit', [AdminController::class, 'editTag'])
    ->name('admin.tags.edit');

Route::put('/admin/tags/{tag}', [AdminController::class, 'updateTag'])
    ->name('admin.tags.update');

Route::delete('/admin/tags/{tag}', [AdminController::class, 'destroyTag'])
    ->name('admin.tags.destroy');