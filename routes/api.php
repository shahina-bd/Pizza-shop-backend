<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PizzaController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminReportController;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('pizzas', [PizzaController::class, 'index']);
Route::get('pizzas/{pizza}', [PizzaController::class, 'show']);
Route::post('pizzas', [PizzaController::class, 'store']);
Route::put('pizzas/{pizza}', [PizzaController::class, 'update']);
Route::delete('pizzas/{pizza}', [PizzaController::class, 'destroy']);

Route::get('categories', [CategoryController::class, 'index']);
Route::post('categories', [CategoryController::class, 'store']);

Route::get('reviews', [ReviewController::class, 'index']);
Route::post('reviews', [ReviewController::class, 'store']);
Route::get('my-reviews', [ReviewController::class, 'myReviews']);
Route::delete('remove', [ReviewController::class, 'remove']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('cart/add', [CartController::class, 'add']);
    Route::get('cart', [CartController::class, 'index']);
    Route::put('cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('cart/{cartItem}', [CartController::class, 'remove']);

    Route::post('orders', [PaymentController::class, 'checkout']);
    Route::get('orders', [CustomerOrderController::class, 'index']);
    Route::put('orders/{order}/status', [CustomerOrderController::class, 'updateStatus']);
});

Route::prefix('v1')->group(function(){
    Route::prefix('customer')->group(function(){

        Route::post('register', [CustomerAuthController::class, 'register']);
        Route::post('login', [CustomerAuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function(){
            Route::get('me', [CustomerAuthController::class,'me']);     
            Route::post('checkout', [PaymentController::class,'checkout']); 

            Route::get('orders', [CustomerOrderController::class, 'index']);
            Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
            Route::post('logout', [CustomerAuthController::class, 'logout']);
        });
    });

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

     // payment gateway route

    Route::post('checkout', [PaymentController::class, 'checkout']);
    
    Route::post('sslcommerz/success', [PaymentController::class, 'paymentSuccess'])->name('sslc.success');
    Route::post('sslcommerz/failure', [PaymentController::class, 'paymentFailure'])->name('sslc.failure');
    Route::post('sslcommerz/cancel', [PaymentController::class, 'paymentCancel'])->name('sslc.cancel');
    Route::post('sslcommerz/ipn', [PaymentController::class, 'paymentIpn'])->name('sslc.ipn');


     Route::middleware('auth:sanctum')->group(function(){

          Route::get('admin/pizzas', [PizzaController::class, 'index']);
          Route::post('admin/pizzas', [PizzaController::class, 'store']);
          Route::put('admin/pizzas/{pizza}', [PizzaController::class, 'update']);
          Route::delete('admin/pizzas/{pizza}', [PizzaController::class, 'destroy']);

         // User Management Routes
         Route::prefix('users')->group(function(){
             Route::get('/', [UserController::class, 'index']);
             Route::get('{user}', [UserController::class, 'show']);
             Route::post('{user}/assign-role', [UserController::class, 'assignRole']);
             Route::post('{user}/remove-role', [UserController::class, 'removeRole']);
             Route::put('{user}/sync-roles', [UserController::class, 'syncRoles']);
         });

         // Permission Routes
        Route::prefix('permissions')->group(function(){
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::get('grouped', [PermissionController::class, 'grouped']);
            Route::post('bulk', [PermissionController::class, 'bulkStore']);
            Route::get('{id}', [PermissionController::class, 'show']);
            Route::put('{id}', [PermissionController::class, 'update']);
            Route::delete('{id}', [PermissionController::class, 'destroy']);
        });

        // Role Routes
        Route::prefix('roles')->group(function(){
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('with-permissions', [RoleController::class, 'getAllWithPermissions']);
            Route::get('{id}', [RoleController::class, 'show']);
            Route::put('{id}', [RoleController::class, 'update']);
            Route::delete('{id}', [RoleController::class, 'destroy']);
            Route::post('{id}/assign-permissions', [RoleController::class, 'assignPermissions']);
            Route::post('{id}/revoke-permissions', [RoleController::class, 'revokePermissions']);
        });

        // Admin Order Routes
        Route::prefix('admin/orders')->group(function(){
            Route::get('/', [AdminOrderController::class, 'index']);
            Route::get('{id}', [AdminOrderController::class, 'show']);
            Route::put('{id}/status', [AdminOrderController::class, 'updateStatus']);
            Route::put('{id}/payment-status', [AdminOrderController::class, 'updatePaymentStatus']);
            Route::delete('{id}', [AdminOrderController::class, 'destroy']);
        });

        // Admin Report Routes
        Route::prefix('admin/reports')->group(function(){
            Route::get('sales', [AdminReportController::class, 'sales']);
            Route::get('customers', [AdminReportController::class, 'customers']);
            Route::get('orders', [AdminReportController::class, 'orders']);
            Route::get('pizzas', [AdminReportController::class, 'pizzas']);
        });

    });


});
