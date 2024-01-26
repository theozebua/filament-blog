<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->as('v1.')->group(function (): void {
    Route::prefix('pages')->as('pages.')->controller(PageController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/{page:slug}', 'show')->name('show');
    });

    Route::prefix('categories')->as('category.')->controller(CategoryController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/{category:slug}', 'show')->name('show');
    });

    Route::prefix('posts')->as('posts.')->controller(PostController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/{post:slug}', 'show')->name('show');
    });
});
