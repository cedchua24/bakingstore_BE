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
use App\Http\Controllers\EmailController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CreditCardPaymentController;
use App\Http\Controllers\InstallmentPaymentTransactionController;
use App\Http\Controllers\InstallmentPaymentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanTransactionController;
use App\Http\Controllers\CreditCardDueController;
use App\Http\Controllers\CreditCardPayController;
use App\Http\Controllers\CreditCardInstallmentDtailsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SpoilageController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\DeliveryCustomerController;
use App\Http\Controllers\CustomerUpdateController;
use App\Http\Controllers\OutOfStockUpdateController;
use App\Http\Controllers\ReturnToSellerController;







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

Route::resource('/customerUpdate', 'App\Http\Controllers\CustomerUpdateController');

Route::resource('/products', 'App\Http\Controllers\ProductController');
Route::get('/products/fetchProductByCategoryId/{id}', [ProductController::class, 'fetchProductByCategoryId']);
Route::get('/products/fetchProductByCategoryIdV2/{id}', [ProductController::class, 'fetchProductByCategoryIdV2']);
Route::get('/products/fetchProductValue/{id}', [ProductController::class, 'fetchProductValue']);
Route::get('/products/fetchProductListV2/{id}', [ProductController::class, 'fetchProductListV2']);
Route::get('/products/fetchProductToNotify/{id}', [ProductController::class, 'fetchProductToNotify']);

Route::get('/products/fetchProductListNote/{id}', [ProductController::class, 'fetchProductListNote']);
Route::get('/products/fetchProductListExpiration/{id}', [ProductController::class, 'fetchProductListExpiration']);
Route::get('/products/fetchOrderSupplierExpirationList/{id}', [ProductController::class, 'fetchOrderSupplierExpirationList']);
Route::post('/products/testController', [ProductController::class, 'testController']);

Route::resource('/spoilage', 'App\Http\Controllers\SpoilageController');
Route::get('/spoilage/fetchById/{id}', [SpoilageController::class, 'fetchById']);
Route::get('/spoilage/fetchSpoilageToday/{id}', [SpoilageController::class, 'fetchSpoilageToday']);
Route::post('/spoilage/fetchSpoilageReport', [SpoilageController::class, 'fetchSpoilageReport']);
Route::get('/spoilage/fetchSpoilageReportByDate/{id}', [SpoilageController::class, 'fetchSpoilageReportByDate']);

Route::resource('/rts', 'App\Http\Controllers\ReturnToSellerController');
Route::get('/rts/fetchById/{id}', [ReturnToSellerController::class, 'fetchById']);


Route::resource('/discount', 'App\Http\Controllers\DiscountController');
Route::post('/discount/fetchDiscountReport', [DiscountController::class, 'fetchDiscountReport']);
Route::post('/discount/fetchDiscountLossReport', [DiscountController::class, 'fetchDiscountLossReport']);



Route::resource('/outOfStockUpdate', 'App\Http\Controllers\OutOfStockUpdateController');
Route::get('/outOfStockUpdate/fetchCustomerToNotify/{id}', [OutOfStockUpdateController::class, 'fetchCustomerToNotify']);


Route::get('/products/fetchByStockWarning/{id}', [ProductController::class, 'fetchByStockWarning']);
Route::get('/products/fetchStockWarningPerSupplier/{id}', [ProductController::class, 'fetchStockWarningPerSupplier']);
Route::get('/products/fetchStockPerSupplier/{id}', [ProductController::class, 'fetchStockPerSupplier']);
Route::get('/products/fetchNoStockWarning/{id}', [ProductController::class, 'fetchNoStockWarning']);
Route::get('/products/fetchOutOfStock/{id}', [ProductController::class, 'fetchOutOfStock']);
Route::get('/products/fetchProductListDisabled/{id}', [ProductController::class, 'fetchProductListDisabled']);
Route::get('/products/fetchModifiedStockDaily/{id}', [ProductController::class, 'fetchModifiedStockDaily']);
Route::post('/products/fetchModifiedReportList', [ProductController::class, 'fetchModifiedReportList']);
Route::get('/products/fetchById/{id}', [ProductController::class, 'fetchById']);
Route::resource('/productTransactions', 'App\Http\Controllers\ProductTransaction');
Route::get('/productTransactions/fetchProductTransactionList/{id}', [ProductTransaction::class, 'fetchProductTransactionList']);
Route::resource('/brands', 'App\Http\Controllers\BrandController');
Route::resource('/emails', 'App\Http\Controllers\EmailController');
Route::resource('/customers', 'App\Http\Controllers\CustomerController');
Route::post('/customers/customerLastOrderList/{id}', [CustomerController::class, 'customerLastOrderList']);
Route::post('/customers/customerConvoList/{id}', [CustomerController::class, 'customerConvoList']);

Route::post('/customers/fetchCustomerByDate', [CustomerController::class, 'fetchCustomerByDate']);

Route::get('/customers/fetchCustomerEnabled/{date}', [CustomerController::class, 'fetchCustomerEnabled']);
Route::get('/customers/fetchCustomerTransactionList/{id}', [CustomerController::class, 'fetchCustomerTransactionList']);
Route::post('/customers/fetchCustomerAds', [CustomerController::class, 'fetchCustomerAds']);
Route::post('/customers/fetchCustomerTransactionListByDate', [CustomerController::class, 'fetchCustomerTransactionListByDate']);


Route::get('/customers/fetchAllCustomer/{id}', [CustomerController::class, 'fetchAllCustomer']);
Route::get('/customers/fetchCustomerTransaction/{id}', [CustomerController::class, 'fetchCustomerTransaction']);
Route::get('/customers/fetchCustomerProduct/{id}', [CustomerController::class, 'fetchCustomerProduct']);

Route::resource('/suppliers', 'App\Http\Controllers\SupplierController');
Route::get('/suppliers/fetchSupplierProduct/{id}', [SupplierController::class, 'fetchSupplierProduct']);

Route::resource('/branchStock', 'App\Http\Controllers\BranchStockController');

Route::resource('/orderSupplierTransaction', 'App\Http\Controllers\OrderSupplierTransactionController');
Route::get('/orderSupplierTransaction/fetchByOrderSupplierTransactionId/{id}', [OrderSupplierTransactionController::class, 'fetchByOrderSupplierTransactionId']);
Route::put('/orderSupplierTransaction/setToCompleteTransaction/{id}', [OrderSupplierTransactionController::class, 'setToCompleteTransaction']);
Route::put('/orderSupplierTransaction/setToCancelTransaction/{id}', [OrderSupplierTransactionController::class, 'setToCancelTransaction']);
Route::put('/orderSupplierTransaction/setToCompletePaymentTransaction/{id}', [OrderSupplierTransactionController::class, 'setToCompletePaymentTransaction']);
Route::get('/orderSupplierTransaction/fetchOrderSupplierByDate/{id}', [OrderSupplierTransactionController::class, 'fetchOrderSupplierByDate']);
Route::get('/orderSupplierTransaction/fetchOrderSupplierByDateV2/{id}', [OrderSupplierTransactionController::class, 'fetchOrderSupplierByDateV2']);
Route::post('/orderSupplierTransaction/fetchPendingOrderSupplier', [OrderSupplierTransactionController::class, 'fetchPendingOrderSupplier']);
Route::post('/orderSupplierTransaction/fetchAllOrderSupplier', [OrderSupplierTransactionController::class, 'fetchAllOrderSupplier']);


Route::post('/orderSupplierTransaction/fetchOrderSupplierReport', [OrderSupplierTransactionController::class, 'fetchOrderSupplierReport']);

Route::resource('/orderSuppliers', 'App\Http\Controllers\OrderSupplierController');
Route::get('/orderSuppliers/fetchOrderByTransactionId/{id}', [OrderSupplierController::class, 'fetchOrderByTransactionId']);
Route::get('/orderSuppliers/fetchOrderBySupplierId/{id}', [OrderSupplierController::class, 'fetchOrderBySupplierId']);
Route::get('/orderSuppliers/fetchOrderByProductId/{id}', [OrderSupplierController::class, 'fetchOrderByProductId']);
Route::post('/orderSuppliers/setToActiveExpiration', [OrderSupplierController::class, 'setToActiveExpiration']);
Route::post('/orderSuppliers/saveAutoPo', [OrderSupplierController::class, 'saveAutoPo']);

Route::resource('/markUpPrice', 'App\Http\Controllers\MarkUpProductController');
Route::post('/markUpPrice/saveMarkUp', [MarkUpProductController::class, 'saveMarkUp']);
Route::get('/markUpPrice/fetchMarkUpBySupplierId/{id}', [MarkUpProductController::class, 'fetchMarkUpBySupplierId']);



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
Route::get('/shop/fetchShopActive/{id}', [ShopController::class, 'fetchShopActive']);
Route::get('/shop/fetchCurrentShop/{id}', [ShopController::class, 'fetchCurrentShop']);
Route::get('/shop/fetchShopCurrent/{id}', [ShopController::class, 'fetchShopCurrent']);
Route::get('/shop/fetcOnlineShopList/{id}', [ShopController::class, 'fetcOnlineShopList']);
Route::get('/shop/fetchPhysicalStoreList/{id}', [ShopController::class, 'fetchPhysicalStoreList']);
Route::get('/shop/fetchOnlineOrderList/{id}', [ShopController::class, 'fetchOnlineOrderList']);
Route::post('/shop/sendReport', [ShopController::class, 'sendReport']);
Route::get('/shop/test/{id}', [ShopController::class, 'test']);

Route::resource('/shopOrderTransaction', 'App\Http\Controllers\ShopOrderTransactionController');
Route::get('/shopOrderTransaction/fetchShopOrderTransactionList/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionList']);
Route::post('/shopOrderTransaction/fetchOnlineShopOrderTransactionListReportByDate', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListReportByDate']);
Route::post('/shopOrderTransaction/fetchSortedCustomerReport', [ShopOrderTransactionController::class, 'fetchSortedCustomerReport']);
Route::post('/shopOrderTransaction/fetchSalesByCategory', [ShopOrderTransactionController::class, 'fetchSalesByCategory']);
Route::post('/shopOrderTransaction/fetchSortedProductReport', [ShopOrderTransactionController::class, 'fetchSortedProductReport']);
Route::post('/shopOrderTransaction/fetchShopOrderTransactionListReportByDate', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionListReportByDate']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListReport/{id}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListReport']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionList/{id}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionList']);
Route::get('/shopOrderTransaction/fetctProductOrderTransaction/{id}', [ShopOrderTransactionController::class, 'fetctProductOrderTransaction']);
Route::post('/shopOrderTransaction/fetchPendingTransactionList', [ShopOrderTransactionController::class, 'fetchPendingTransactionList']);
Route::post('/shopOrderTransaction/fetchDeliveryTransaction', [ShopOrderTransactionController::class, 'fetchDeliveryTransaction']);
Route::post('/shopOrderTransaction/fetchPendingDeliveryTransaction', [ShopOrderTransactionController::class, 'fetchPendingDeliveryTransaction']);

Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListByIdDate/{id}/{date}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListByIdDate']);
Route::get('/shopOrderTransaction/fetchExpensesList/{id}/{date}', [ShopOrderTransactionController::class, 'fetchExpensesList']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListByDate/{date}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListByDate']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransactionListByStatus/{status}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransactionListByStatus']);
Route::get('/shopOrderTransaction/fetchSortedProduct/{id}', [ShopOrderTransactionController::class, 'fetchSortedProduct']);
Route::get('/shopOrderTransaction/fetchSortedCustomer/{id}', [ShopOrderTransactionController::class, 'fetchSortedCustomer']);
Route::get('/shopOrderTransaction/fetchShopOrderTransactionListByDate/{date}', [ShopOrderTransactionController::class, 'fetchShopOrderTransactionListByDate']);

// branch

Route::get('/shopOrderTransaction/fetchBranchOrder/{id}', [ShopOrderTransactionController::class, 'fetchBranchOrder']);
Route::get('/shopOrderTransaction/fetchShopOrderTransaction/{id}', [ShopOrderTransactionController::class, 'fetchShopOrderTransaction']);
Route::get('/shopOrderTransaction/fetchOnlineShopOrderTransaction/{id}', [ShopOrderTransactionController::class, 'fetchOnlineShopOrderTransaction']);
Route::put('/shopOrderTransaction/updateShopOrderTransactionStatus/{id}', [ShopOrderTransactionController::class, 'updateShopOrderTransactionStatus']);
Route::delete('/shopOrderTransaction/cancel/{shopOrderTransaction}', [ShopOrderTransactionController::class, 'cancel']);
Route::delete('/shopOrderTransaction/deleteShopOrderTransaction/{shopOrderTransaction}', [ShopOrderTransactionController::class, 'deleteShopOrderTransaction']);


Route::resource('/deliveryCustomer', 'App\Http\Controllers\DeliveryCustomerController');
Route::get('/deliveryCustomer/fetchDeliveryById/{id}', [DeliveryCustomerController::class, 'fetchDeliveryById']);
Route::delete('/deliveryCustomer/deleteTransaction/{id}', [DeliveryCustomerController::class, 'deleteTransaction']);

Route::resource('/shopOrder', 'App\Http\Controllers\ShopOrderController');
Route::get('/shopOrder/fetchShopOrderDTO/{id}', [ShopOrderController::class, 'fetchShopOrderDTO']);
Route::get('/shopOrder/fetchShopOrder/{id}', [ShopOrderController::class, 'fetchShopOrder']);
Route::delete('/shopOrder/delete/{user}', [ShopOrderController::class, 'delete']);

Route::resource('/branchStockTransaction', 'App\Http\Controllers\BranchStockTransactionController');
Route::get('/branchStockTransaction/fetchBranchStockWarehouseList/{id}', [BranchStockTransactionController::class, 'fetchBranchStockWarehouseList']);


Route::resource('/shopType', 'App\Http\Controllers\ShopTypeController');

Route::resource('/expenses', 'App\Http\Controllers\ExpensesController');
Route::resource('/expensesType', 'App\Http\Controllers\ExpensesTypeController');
Route::get('/expensesType/fetchExpenseTypeTransaction/{id}', [ExpensesTypeController::class, 'fetchExpenseTypeTransaction']);
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
Route::get('/productSupplier/fetchSupplierByProductId/{id}', [ProductSupplierController::class, 'fetchSupplierByProductId']);


Route::resource('/modeOfPaymentPo', 'App\Http\Controllers\ModeOfPaymentPoController');
Route::get('/modeOfPaymentPo/fetchPaymentTypePoByShopTransactionId/{id}', [ModeOfPaymentPoController::class, 'fetchPaymentTypePoByShopTransactionId']);
Route::get('/modeOfPaymentPo/fetchCreditCardPaymentDTO/{id}', [ModeOfPaymentPoController::class, 'fetchCreditCardPaymentDTO']);
Route::put('/modeOfPaymentPo/setToCompleteCreditCard/{id}', [ModeOfPaymentPoController::class, 'setToCompleteCreditCard']);



Route::resource('/paymentTerm', 'App\Http\Controllers\PaymentTermController');

Route::resource('/paymentTypePo', 'App\Http\Controllers\PaymentTypePoController');
Route::get('/paymentTypePo/findByCategory/{id}', [PaymentTypePoController::class, 'findByCategory']);
Route::get('/paymentTerm/fetchNotCashList/{id}', [PaymentTermController::class, 'fetchNotCashList']);
Route::get('/paymentTerm/fetchCashAndOnline/{id}', [PaymentTermController::class, 'fetchCashAndOnline']);

Route::get('/paymentTerm/fetchPaymentTermCreditCard/{id}', [PaymentTermController::class, 'fetchPaymentTermCreditCard']);

Route::get('/paymentTerm/fetchByPaymentTerm/{id}', [PaymentTermController::class, 'fetchByPaymentTerm']);
Route::get('/paymentTerm/fetchByPaymentTypePo/{id}', [PaymentTermController::class, 'fetchByPaymentTypePo']);
Route::get('/paymentTerm/fetchOrderSupplierByPaymentType/{id}', [PaymentTermController::class, 'fetchOrderSupplierByPaymentType']);
Route::get('/paymentTerm/fetchCreditCardPaymentList/{id}', [PaymentTermController::class, 'fetchCreditCardPaymentList']);
Route::get('/paymentTerm/fetchCreditCardPaymentListV2/{id}', [PaymentTermController::class, 'fetchCreditCardPaymentListV2']);
Route::get('/paymentTerm/fetchCreditCardPaymentListV3/{id}', [PaymentTermController::class, 'fetchCreditCardPaymentListV3']);
Route::get('/paymentTerm/fetchInstallmenttList/{id}', [PaymentTermController::class, 'fetchInstallmenttList']);


Route::resource('/creditCardPayment', 'App\Http\Controllers\CreditCardPaymentController');
Route::get('/creditCardPayment/fetchCreditCardByMOP/{id}', [CreditCardPaymentController::class, 'fetchCreditCardByMOP']);

Route::resource('/installmentPayment', 'App\Http\Controllers\InstallmentPaymentController');
Route::get('/installmentPayment/fetchInstallmentPayment/{id}', [InstallmentPaymentController::class, 'fetchInstallmentPayment']);

Route::resource('/installmentPaymentTransaction', 'App\Http\Controllers\InstallmentPaymentTransactionController');
Route::get('/installmentPaymentTransaction/fetchInstallmentTransactionByMOP/{id}', [InstallmentPaymentTransactionController::class, 'fetchInstallmentTransactionByMOP']);
Route::get('/installmentPaymentTransaction/fetchPromoInstallmentList/{id}', [InstallmentPaymentTransactionController::class, 'fetchPromoInstallmentList']);

Route::resource('/loan', 'App\Http\Controllers\LoanController');
Route::get('/loan/fetchInstallmentList/{id}', [LoanController::class, 'fetchInstallmentList']);

Route::resource('/loanTransaction', 'App\Http\Controllers\LoanTransactionController');
Route::get('/loanTransaction/fetchloanTransactionV2/{id}', [LoanTransactionController::class, 'fetchloanTransactionV2']);

Route::resource('/creditCardDue', 'App\Http\Controllers\CreditCardDueController');
Route::get('/creditCardDue/fetallCreditDueById/{id}', [CreditCardDueController::class, 'fetallCreditDueById']);
Route::get('/creditCardDue/fetchCreditCardDueList/{id}', [CreditCardDueController::class, 'fetchCreditCardDueList']);
Route::get('/creditCardDue/fetchCreditCardPaidList/{id}', [CreditCardDueController::class, 'fetchCreditCardPaidList']);
Route::get('/creditCardDue/fetchChequeDueList/{id}', [CreditCardDueController::class, 'fetchChequeDueList']);
Route::get('/creditCardDue/fetchChequePaidList/{id}', [CreditCardDueController::class, 'fetchChequePaidList']);
Route::post('/creditCardDue/createCreditDueYearly', [CreditCardDueController::class, 'createCreditDueYearly']);

Route::get('/creditCardDue/fetchCreditCardDetail/{id}', [CreditCardDueController::class, 'fetchCreditCardDetail']);
Route::get('/creditCardDue/fetchPaymentTypeDetail/{id}', [CreditCardDueController::class, 'fetchPaymentTypeDetail']);
Route::post('/creditCardDue/saveCreditCardPay', [CreditCardDueController::class, 'saveCreditCardPay']);


Route::resource('/creditCardInstallmentDtails', 'App\Http\Controllers\CreditCardInstallmentDtailsController');
Route::get('/creditCardInstallmentDtails/fetchCreditCardInstallmentDetail/{id}', [CreditCardInstallmentDtailsController::class, 'fetchCreditCardInstallmentDetail']);




Route::resource('/creditCardPay', 'App\Http\Controllers\CreditCardPayController');
Route::get('/creditCardPay/fetchCreditCardPayById/{id}', [CreditCardPayController::class, 'fetchCreditCardPayById']);
Route::get('/creditCardPay/fetchCreditCardPayByPaymentType/{id}', [CreditCardPayController::class, 'fetchCreditCardPayByPaymentType']);
Route::get('/creditCardPay/fetchCreditCardDueByInstallment/{id}', [CreditCardPayController::class, 'fetchCreditCardDueByInstallment']);







Route::resource('/banks', 'App\Http\Controllers\BankController');



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