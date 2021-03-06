<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\LoginController as UserLoginController;
use App\Http\Controllers\User\ShoppingController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginControlle;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\UserController as UController;
use App\Http\Controllers\User\ProductController as PController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\Admin\OrderController as AOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ADMIN
Route::group(['prefix' => 'admin'], function () {

    Route::post('/login', [AdminLoginControlle::class, 'login']);
    Route::post('/logout', [AdminLoginControlle::class, 'logout']);

    Route::group(['middleware' => ['assign.guard:admins']], function () {

        Route::get('/me', [AdminLoginControlle::class, 'me']);
        Route::get('/dashboard', [HomeController::class, 'index']);

        Route::group(['prefix' => 'category'], function () {

            Route::get('/list', [CategoryController::class, 'index']);
            Route::get('/detail/{id}', [CategoryController::class, 'show']);
            Route::post('/update/{id}', [CategoryController::class, 'update']);
            Route::post('/create', [CategoryController::class, 'store']);
            Route::delete('/delete/{id}', [CategoryController::class, 'destroy']);
        
        });

        Route::group(['prefix' => 'product'], function () {

            Route::get('/list', [ProductController::class, 'index']);
            Route::post('/create', [ProductController::class, 'store']);
            Route::get('/detail/{id}', [ProductController::class, 'show']);
            Route::post('/update/{id}', [ProductController::class, 'update']);
            Route::delete('/delete/{id}', [ProductController::class, 'destroy']);
        
        });

        Route::group(['prefix' => 'user'], function () {

            Route::get('/list', [UController::class, 'index']);
            Route::get('/detail/{id}', [UController::class, 'show']);
        
        });

        Route::group(['prefix' => 'order'], function () {

            Route::get('/list', [AOrderController::class, 'index']);
            Route::get('/detail/{id}', [AOrderController::class, 'show']);
            Route::post('/update-order/{id}', [AOrderController::class, 'update']);
            Route::get('/export', [ExportController::class, 'export']);
        
        });
    });

});


/// USER
Route::group(['prefix' => 'user'], function () {

    Route::post('/login', [UserLoginController::class, 'login']);
    Route::post('/logout', [UserLoginController::class, 'logout']);
    Route::post('/signup', [UserController::class, 'sendMailRegister']);
    Route::get('/signup/check/{token}', [UserController::class, 'create']);
    Route::post('/signup/input/{token}', [UserController::class, 'store']);
    Route::post('/reset/input', [UserController::class, 'sendMailResetPassword']);
    Route::get('/reset/check/{token}', [UserController::class, 'viewReset']);
    Route::post('/reset/token/{token}', [UserController::class, 'reset']);

    Route::group(['middleware' => ['assign.guard:users']], function () {

        Route::get('/me', [UserLoginController::class, 'me']);

        Route::get('/profile', [UserController::class, 'show']);
        Route::post('/update', [UserController::class, 'update']);

        Route::get('/check-stock', [ShoppingController::class, 'checkStock']);
        Route::post('/cancel-order/{id}', [OrderController::class, 'cancelOrder']);

        Route::get('/cart', [ShoppingController::class, 'index']);
        Route::post('/transaction', [OrderController::class, 'store']);
        Route::group(['prefix' => 'cart'], function () {
            
            Route::post('/add', [ShoppingController::class, 'create']);
            Route::post('/update', [ShoppingController::class, 'update']);
            Route::delete('/delete/{id}', [ShoppingController::class, 'destroy']);
        });
        
        Route::post('/product/comment/{id}', [PController::class, 'comment']);
    });
    
});


// All
Route::get('/category/list', [CategoryController::class, 'index']);
Route::get('/product/list', [PController::class, 'index']);


