<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use app\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderCustomerController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderSupplierController;
use App\Http\Controllers\ProductTransaction;
use App\Http\Controllers\OrderCustomerTransactionController;
use App\Http\Controllers\OrderSupplierTransactionController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopTypeController;
use App\Http\Controllers\ShopOrderTransactionController;
use App\Http\Controllers\ShopOrderController;
use App\Http\Controllers\BranchStockTransactionController;
use App\Http\Controllers\MarkUpProductController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ExpensesTypeController;
use App\Http\Controllers\ExpensesCategoryController;
use App\Http\Controllers\ModeOfPaymentController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\StockOrderController;
use App\Http\Controllers\ProductSupplierController;
use App\Http\Controllers\ModeOfPaymentPoController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\PaymentTypePoController;



// Route::middleware('auth:sanctum')->group(function () {
// Route::post('/register', [AuthController::class, 'register']);
// });



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware('auth:sanctum')->group(function () {
//    Route::post('logout', [AuthController::class, 'logout']);
// });

Route::middleware('auth:sanctum', 'isAPIAdmin')->group(function () {
   Route::get('/checkingAuthenticated', function (){
    return response()->json(['message'=>'You are in', 'status'=>200], 200);
   });
});

Route::get('/register', [AuthController::class, 'fetchUserList']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::delete('/register/{user}', [AuthController::class, 'destroy']);


Route::resource('/userProfiles', 'App\Http\Controllers\UserProfileController');
Route::resource('/categories', 'App\Http\Controllers\CategoryController');
Route::resource('/customerTypes', 'App\Http\Controllers\CustomerTypeController');

Route::resource('/products', 'App\Http\Controllers\ProductController');
Route::get('/products/fetchProductByCategoryId/{id}', [ProductController::class, 'fetchProductByCategoryId']);
Route::get('/products/fetchProductByCategoryIdV2/{id}', [ProductController::class, 'fetchProductByCategoryIdV2']);
Route::get('/products/fetchByStockWarning/{id}', [ProductController::class, 'fetchByStockWarning']);
Route::get('/products/fetchById/{id}', [ProductController::class, 'fetchById']);
Route::resource('/productTransactions', 'App\Http\Controllers\ProductTransaction');
Route::get('/productTransactions/fetchProductTransactionList/{id}', [ProductTransaction::class, 'fetchProductTransactionList']);
Route::resource('/brands', 'App\Http\Controllers\BrandController');
Route::resource('/customers', 'App\Http\Controllers\CustomerController');
Route::get('/customers/fetchCustomerEnabled/{date}', [CustomerController::class, 'fetchCustomerEnabled']);
Route::resource('/suppliers', 'App\Http\Controllers\SupplierController');
Route::resource('/branchStock', 'App\Http\Controllers\BranchStockController');

Route::resource('/orderSupplierTransaction', 'App\Http\Controllers\OrderSupplierTransactionController');
Route::get('/orderSupplierTransaction/fetchByOrderSupplierTransactionId/{id}', [OrderSupplierTransactionController::class, 'fetchByOrderSupplierTransactionId']);
Route::put('/orderSupplierTransaction/setToCompleteTransaction/{id}', [OrderSupplierTransactionController::class, 'setToCompleteTransaction']);
Route::put('/orderSupplierTransaction/setToCancelTransaction/{id}', [OrderSupplierTransactionController::class, 'setToCancelTransaction']);

Route::resource('/orderSuppliers', 'App\Http\Controllers\OrderSupplierController');
Route::get('/orderSuppliers/fetchOrderByTransactionId/{id}', [OrderSupplierController::class, 'fetchOrderByTransactionId']);
Route::get('/orderSuppliers/fetchOrderBySupplierId/{id}', [OrderSupplierController::class, 'fetchOrderBySupplierId']);
Route::get('/orderSuppliers/fetchOrderByProductId/{id}', [OrderSupplierController::class, 'fetchOrderByProductId']);

Route::resource('/markUpPrice', 'App\Http\Controllers\MarkUpProductController');
Route::post('/markUpPrice/saveMarkUp', [MarkUpProductController::class, 'saveMarkUp']);


Route::resource('/orderCustomers', 'App\Http\Controllers\OrderCustomerController');
Route::get('/orderCustomers/fetchOrderByTransactionId/{id}', [OrderCustomerController::class, 'fetchOrderByTransactionId']);
Route::get('/orderCustomers/fetchOrderByProductId/{id}', [OrderCustomerController::class, 'fetchOrderByProductId']);
Route::post('/orderCustomers/saveCustomerTransaction', [OrderCustomerController::class, 'saveCustomerTransaction']);
// Route::post('/orderCustomers/saveCustomerTransaction', 'OrderCustomerController@request');


Route::resource('/orderCustomerTransaction', 'App\Http\Controllers\OrderCustomerTransactionController');
Route::get('/orderCustomerTransaction/fetchCustomerOrderTransaction/{id}', [OrderCustomerTransactionController::class, 'fetchCustomerOrderTransaction']);
Route::get('/orderCustomerTransaction/fetchOrderByTransactionId/{id}', [OrderCustomerTransactionController::class, 'fetchOrderByTransactionId']);
Route::get('/orderCustomerTransaction/fetchByOrderSupplierTransactionId/{id}', [OrderCustomerTransactionController::class, 'fetchByOrderSupplierTransactionId']);
Route::put('/orderCustomerTransaction/setToCompleteTransaction/{id}', [OrderCustomerTransactionController::class, 'setToCompleteTransaction']);

Route::resource('/warehouse', 'App\Http\Controllers\WarehouseController');
Route::get('/warehouse/fetchWarehouseStock/{id}', [WarehouseController::class, 'fetchWarehouseStock']);

Route::resource('/shop', 'App\Http\Controllers\ShopController');
Route::get('/shop/fetchShopList/{id}', [ShopController::class, 'fetchShopList']);
Route::get('/shop/fetcOnlineShopList/{id}', [ShopController::class, 'fetcOnlineShopList']);
Route::get('/shop/fetchPhysicalStoreList/{id}', [ShopController::class, 'fetchPhysicalStoreList']);
Route::get('/shop/fetchOnlineOrderList/{id}', [ShopController::class, 'fetchOnlineOrderList']);
Route::get('/shop/test/{id}', [ShopController::class, 'test']);

Route::resource('/shopOrderTransaction', 'App\Http\Controllers\ShopOrderTransactionController');
Route::get('/shopOrderTransaction/fetchShopOrderTransactionList/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionList']);
Route::post('/shopOrderTransaction/fetchOnlineShopOrderTransactionListReportByDate', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListReportByDate']);
Route::post('/shopOrderTransaction/fetchSortedCustomerReport', [ShopOrderTransactionController::class, 'fetchSortedCustomerReport']);
Route::post('/shopOrderTransaction/fetchSortedProductReport', [ShopOrderTransactionController::class, 'fetchSortedProductReport']);
Route::post('/shopOrderTransaction/fetchShopOrderTransactionListReportByDate', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionListReportByDate']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListReport/{id}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListReport']);
Route::get('/shopOrderTransaction/fetchShopOrderTransactionListReport/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionListReport']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionList/{id}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionList']);
Route::post('/shopOrderTransaction/fetchPendingTransactionList', [ShopOrderTransactionController::class, 'fetchPendingTransactionList']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListByIdDate/{id}/{date}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListByIdDate']);
Route::get('/shopOrderTransaction/fetchExpensesList/{id}/{date}', [ShopOrderTransactionController::class, 'fetchExpensesList']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListByDate/{date}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListByDate']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListByStatus/{status}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListByStatus']);
Route::get('/shopOrderTransaction/fetchSortedProduct/{id}', [ShopOrderTransactionController::class, 'fetchSortedProduct']);
Route::get('/shopOrderTransaction/fetchSortedCustomer/{id}', [ShopOrderTransactionController::class, 'fetchSortedCustomer']);
Route::get('/shopOrderTransaction/fetchShopOrderTransactionListByDate/{date}', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionListByDate']);
Route::get('/shopOrderTransaction/fetchShopOrderTransaction/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransaction']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransaction/{id}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransaction']);
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

Route::resource('/expenses', 'App\Http\Controllers\ExpensesController');
Route::resource('/expensesType', 'App\Http\Controllers\ExpensesTypeController');
Route::resource('/expensesCategory', 'App\Http\Controllers\ExpensesCategoryController');
Route::post('/expenses/fetchExpensesTransactionByDate', [ExpensesController::class, 'fetchExpensesTransactionByDate']);
Route::get('/expenses/fetchExpensesTransaction/{id}', [ExpensesController::class, 'fetchExpensesTransaction']);
Route::get('/expenses/fetchExpensesMandatoryToday/{id}', [ExpensesController::class, 'fetchExpensesMandatoryToday']);
Route::get('/expenses/fetchExpensesNonMandatoryToday/{id}', [ExpensesController::class, 'fetchExpensesNonMandatoryToday']);

Route::get('/expenses/fetchExpensesTransactionToday/{id}', [ExpensesController::class, 'fetchExpensesTransactionToday']);
Route::get('/expenses/fetchExpensesByDate/{id}', [ExpensesController::class, 'fetchExpensesByDate']);

Route::get('/expenses/fetchExpensesTransactionById/{id}', [ExpensesController::class, 'fetchExpensesTransactionById']);
Route::get('/expenses/fetchExpenseById/{id}', [ExpensesController::class, 'fetchExpenseById']);

Route::resource('/modeOfPayment', 'App\Http\Controllers\ModeOfPaymentController');
Route::get('/modeOfPayment/fetchPaymentTypeByShopTransactionId/{id}', [ModeOfPaymentController::class, 'fetchPaymentTypeByShopTransactionId']);
Route::put('/modeOfPayment/updatePaidStatus/{id}', [ModeOfPaymentController::class, 'updatePaidStatus']);

Route::resource('/paymentType', 'App\Http\Controllers\PaymentTypeController');
Route::get('/paymentType/fetchEnablePaymentType/{id}', [PaymentTypeController::class, 'fetchEnablePaymentType']);


Route::resource('/stockOrder', 'App\Http\Controllers\StockOrderController');
Route::get('/shopOrderTransaction/fetchShopOrderTransactionList/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionList']);



Route::resource('/productSupplier', 'App\Http\Controllers\ProductSupplierController');
Route::get('/productSupplier/fetchProductSupplierById/{id}', [ProductSupplierController::class, 'fetchProductSupplierById']);

Route::resource('/modeOfPaymentPo', 'App\Http\Controllers\ModeOfPaymentPoController');
Route::get('/modeOfPaymentPo/fetchPaymentTypePoByShopTransactionId/{id}', [ModeOfPaymentPoController::class, 'fetchPaymentTypePoByShopTransactionId']);

Route::resource('/paymentTerm', 'App\Http\Controllers\PaymentTermController');

Route::resource('/paymentTypePo', 'App\Http\Controllers\PaymentTypePoController');



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