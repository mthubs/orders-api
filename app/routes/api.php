<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/orders', [OrderController::class, 'index']);
Route::get('/order/{order}', [OrderController::class, 'show']);
Route::post('/order', [OrderController::class, 'store']);
Route::delete('/order/{order}', [OrderController::class, 'destroy']);
Route::get('/order/{order}/discounts', [ OrderController::class, 'discounts' ]);
