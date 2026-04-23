<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Публичные страницы
Route::get('/', function () {
    return Inertia::render('Products/Index');
})->name('home');

Route::get('/product/{id}', function ($id) {
    return Inertia::render('Products/Show', ['id' => (int) $id]);
})->name('product.show');

Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

// Административные страницы (без middleware, защита на фронте)
Route::prefix('admin')->group(function () {
    Route::get('/products', function () {
        return Inertia::render('Admin/Products/Index');
    });
    Route::get('/products/create', function () {
        return Inertia::render('Admin/Products/Create');
    });
    Route::get('/products/{id}/edit', function ($id) {
        return Inertia::render('Admin/Products/Edit', ['id' => (int) $id]);
    });
});
