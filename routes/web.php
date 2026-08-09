<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Web用のルートをここに登録します。
|
*/

// お問い合わせ入力画面
Route::get('/', [ContactController::class, 'index']);

// お問い合わせ確認
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contacts.confirm');

// お問い合わせ登録
Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contacts.store');

// サンクスページ
Route::get('/thanks', [ContactController::class, 'thanks'])
    ->name('contacts.thanks');


/*
|--------------------------------------------------------------------------
| 管理画面
|--------------------------------------------------------------------------
|
| ログインしているユーザーのみアクセスできます。
|
*/

Route::middleware('auth')->group(function () {

    // 管理画面一覧
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.index');

    // お問い合わせ詳細
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])
        ->name('admin.show');

    // お問い合わせ削除
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
        ->name('admin.destroy');

    // CSVエクスポート
    Route::get('/contacts/export', [AdminController::class, 'export'])
        ->name('contacts.export');

    // タグ登録
    Route::post('/admin/tags', [AdminController::class, 'storeTag'])
        ->name('admin.tags.store');

    // タグ編集画面
    Route::get('/admin/tags/{tag}/edit', [AdminController::class, 'editTag'])
        ->name('admin.tags.edit');

    // タグ更新
    Route::put('/admin/tags/{tag}', [AdminController::class, 'updateTag'])
        ->name('admin.tags.update');

    // タグ削除
    Route::delete('/admin/tags/{tag}', [AdminController::class, 'destroyTag'])
        ->name('admin.tags.destroy');
});