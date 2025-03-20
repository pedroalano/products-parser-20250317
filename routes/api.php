<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', [ProductController::class, 'index']);
Route::put('/products/{code}', [ProductController::class, 'update']);
Route::delete('/products/{code}', [ProductController::class, 'delete']);
Route::get('/products/{code}', [ProductController::class, 'show']);
Route::get('/products', [ProductController::class, 'list']);
