<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SleepRecordController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CharacterSelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 認証ルート
require __DIR__.'/auth.php';

// キャラクター選択ルート
Route::middleware(['auth'])->group(function () {
    Route::get('/character-selection', [CharacterSelectionController::class, 'index'])
        ->name('character.selection');
    Route::post('/character-selection', [CharacterSelectionController::class, 'select'])
        ->name('character.select');
});

// 認証が必要なルート
Route::middleware(['auth'])->group(function () {
    // ホーム
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // 睡眠記録
    Route::get('/sleep', [SleepRecordController::class, 'index'])->name('sleep.index');
    Route::get('/sleep/create', [SleepRecordController::class, 'create'])->name('sleep.create');
    Route::post('/sleep', [SleepRecordController::class, 'store'])->name('sleep.store');
    Route::get('/sleep/{id}/edit', [SleepRecordController::class, 'edit'])->name('sleep.edit');
    Route::put('/sleep/{id}', [SleepRecordController::class, 'update'])->name('sleep.update');
    Route::delete('/sleep/{id}', [SleepRecordController::class, 'destroy'])->name('sleep.destroy');
        
    // 設定
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('/settings/reset', [SettingController::class, 'reset'])->name('settings.reset');
    
    // ショップ
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/shop/{item}/purchase', [ShopController::class, 'purchase'])->name('shop.purchase');
    Route::get('/shop/my-items', [ShopController::class, 'myItems'])->name('shop.my-items');
    Route::post('/shop/{item}/toggle-equip', [ShopController::class, 'toggleEquip'])->name('shop.toggle-equip');
});