<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use app\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderCustomerController;
use App\Http\Controllers\OrderSupplierController;
use App\Http\Controllers\OrderCustomerTransactionController;
use App\Http\Controllers\OrderSupplierTransactionController;

// Route::middleware('auth:sanctum')->group(function () {
// Route::post('/register', [AuthController::class, 'register']);
// });



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
   Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum', 'isAPIAdmin')->group(function () {
   Route::get('/checkingAuthenticated', function (){
    return response()->json(['message'=>'You are in', 'status'=>200], 200);
   });
});

Route::get('/register', [AuthController::class, 'fetchUserList']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::delete('/register/{user}', [AuthController::class, 'destroy']);


Route::resource('/userProfiles', 'App\Http\Controllers\UserProfileController');
Route::resource('/categories', 'App\Http\Controllers\CategoryController');

Route::resource('/products', 'App\Http\Controllers\ProductController');
Route::resource('/brands', 'App\Http\Controllers\BrandController');
Route::resource('/suppliers', 'App\Http\Controllers\SupplierController');

Route::resource('/orderSupplierTransaction', 'App\Http\Controllers\OrderSupplierTransactionController');
Route::get('/orderSupplierTransaction/fetchByOrderSupplierTransactionId/{id}', [OrderSupplierTransactionController::class, 'fetchByOrderSupplierTransactionId']);
Route::put('/orderSupplierTransaction/setToCompleteTransaction/{id}', [OrderSupplierTransactionController::class, 'setToCompleteTransaction']);

Route::resource('/orderSuppliers', 'App\Http\Controllers\OrderSupplierController');
Route::get('/orderSuppliers/fetchOrderByTransactionId/{id}', [OrderSupplierController::class, 'fetchOrderByTransactionId']);
Route::get('/orderSuppliers/fetchOrderBySupplierId/{id}', [OrderSupplierController::class, 'fetchOrderBySupplierId']);
Route::get('/orderSuppliers/fetchOrderByProductId/{id}', [OrderSupplierController::class, 'fetchOrderByProductId']);

Route::resource('/markUpPrice', 'App\Http\Controllers\MarkUpProductController');


Route::resource('/orderCustomers', 'App\Http\Controllers\OrderCustomerController');
Route::get('/orderCustomers/fetchOrderByTransactionId/{id}', [OrderCustomerController::class, 'fetchOrderByTransactionId']);
Route::get('/orderCustomers/fetchOrderByProductId/{id}', [OrderCustomerController::class, 'fetchOrderByProductId']);


Route::resource('/orderCustomerTransaction', 'App\Http\Controllers\OrderCustomerTransactionController');
Route::get('/orderCustomerTransaction/fetchByOrderSupplierTransactionId/{id}', [OrderCustomerTransactionController::class, 'fetchByOrderSupplierTransactionId']);
Route::put('/orderCustomerTransaction/setToCompleteTransaction/{id}', [OrderCustomerTransactionController::class, 'setToCompleteTransaction']);





// Route::group(['middleware' => 'auth:sanctum'], function(){
    
//     Route::get('/register', [AuthController::class, 'fetchUserList']);
//     Route::post('register', [AuthController::class, 'register']);
// Route::resource('/userProfiles', 'App\Http\Controllers\UserProfileController');
// Route::resource('/categories', 'App\Http\Controllers\CategoryController');

// Route::resource('/products', 'App\Http\Controllers\ProductController');
// Route::resource('/brands', 'App\Http\Controllers\BrandController');
// });

// Route::get('/register', [AuthController::class, 'fetchUserList']);

// Route::resource('/userProfiles', 'App\Http\Controllers\UserProfileController');
// Route::resource('/categories', 'App\Http\Controllers\CategoryController');

// Route::resource('/products', 'App\Http\Controllers\ProductController');
// Route::resource('/brands', 'App\Http\Controllers\BrandController');


// Route::get('/categories', 'App\Http\Controllers\CategoryController@index');
// Route::get('/categories/{id}', 'App\Http\Controllers\CategoryController@show');
// Route::post('/categories', 'App\Http\Controllers\CategoryController@store');
// Route::put('/categories/{category}', 'App\Http\Controllers\CategoryController@update');
// Route::delete('/categories/{category}', 'App\Http\Controllers\CategoryController@destroy');
// Route::get('/categories/getId/{category_name}', 'App\Http\Controllers\CategoryController@getId');