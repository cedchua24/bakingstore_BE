<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use app\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderCustomerController;
use App\Http\Controllers\OrderSupplierController;
use App\Http\Controllers\OrderCustomerTransactionController;
use App\Http\Controllers\OrderSupplierTransactionController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopTypeController;
use App\Http\Controllers\ShopOrderTransactionController;
use App\Http\Controllers\ShopOrderController;
use App\Http\Controllers\BranchStockTransactionController;



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
Route::resource('/branchStock', 'App\Http\Controllers\BranchStockController');

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
Route::post('/orderCustomers/saveCustomerTransaction', [OrderCustomerController::class, 'saveCustomerTransaction']);
// Route::post('/orderCustomers/saveCustomerTransaction', 'OrderCustomerController@request');


Route::resource('/orderCustomerTransaction', 'App\Http\Controllers\OrderCustomerTransactionController');
Route::get('/orderCustomerTransaction/fetchOrderByTransactionId/{id}', [OrderCustomerTransactionController::class, 'fetchOrderByTransactionId']);
Route::get('/orderCustomerTransaction/fetchByOrderSupplierTransactionId/{id}', [OrderCustomerTransactionController::class, 'fetchByOrderSupplierTransactionId']);
Route::put('/orderCustomerTransaction/setToCompleteTransaction/{id}', [OrderCustomerTransactionController::class, 'setToCompleteTransaction']);

Route::resource('/warehouse', 'App\Http\Controllers\WarehouseController');
Route::get('/warehouse/fetchWarehouseStock/{id}', [WarehouseController::class, 'fetchWarehouseStock']);

Route::resource('/shop', 'App\Http\Controllers\ShopController');
Route::get('/shop/fetchShopList/{id}', [ShopController::class, 'fetchShopList']);

Route::resource('/shopOrderTransaction', 'App\Http\Controllers\ShopOrderTransactionController');
Route::get('/shopOrderTransaction/fetchShopOrderTransactionList', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionList']);
Route::get('/shopOrderTransaction/fetchShopOrderTransaction/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransaction']);
Route::put('/shopOrderTransaction/updateShopOrderTransactionStatus/{id}', [ShopOrderTransactionController::class, 'updateShopOrderTransactionStatus']);
Route::delete('/shopOrderTransaction/cancel/{shopOrderTransaction}', [ShopOrderTransactionController::class, 'cancel']);
Route::delete('/shopOrderTransaction/deleteShopOrderTransaction/{shopOrderTransaction}', [ShopOrderTransactionController::class, 'deleteShopOrderTransaction']);


Route::resource('/shopOrder', 'App\Http\Controllers\ShopOrderController');
Route::get('/shopOrder/fetchShopOrderDTO/{id}', [ShopOrderController::class, 'fetchShopOrderDTO']);
Route::get('/shopOrder/fetchShopOrder/{id}', [ShopOrderController::class, 'fetchShopOrder']);
Route::delete('/shopOrder/delete/{user}', [ShopOrderController::class, 'delete']);

Route::resource('/branchStockTransaction', 'App\Http\Controllers\BranchStockTransactionController');
Route::get('/branchStockTransaction/fetchBranchStockWarehouseList/{id}', [BranchStockTransactionController::class, 'fetchBranchStockWarehouseList']);


Route::resource('/shopType', 'App\Http\Controllers\ShopTypeController');





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
// Route::get('/categories/getId/{category_name}', 'App\Http\Controllers\CategoryController@getId');5