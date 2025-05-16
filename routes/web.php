<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\IGroupController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MProcessController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\ProductMaterialController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceiveController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

// --------------------------------------
// ---------- Auth Controllers ----------
// --------------------------------------
Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::group(['middleware' => 'auth'], function () {
    Route::group([
        'middleware' => ['auth', 'is_admin'],
    ], function () {
        // Add Routes Here
    });
});

// ---------------------------------------
// ---------- Other Controllers ----------
// ---------------------------------------

// General / Admin
Route::get('/admin', [AdminController::class, 'index'])->name('admin');
Route::get('/user', [AdminController::class, 'user'])->name('user');
Route::post('/user', [AuthController::class, 'store'])->name('user.store');
Route::post('/user/{id}', [AuthController::class, 'update'])->name('user.update');
Route::post('/image/{id}/{dir}', [ImageController::class, 'destroy'])->name('image.delete');

// Roles & Permissions
Route::get('/role', [AdminController::class, 'role'])->name('role');
Route::get('/addRole', [AdminController::class, 'create'])->name('role.add');
Route::post('/role', [AdminController::class, 'store'])->name('role.store');
Route::get('/editRole/{id}', [AdminController::class, 'edit'])->name('role.edit');
Route::post('/role/{id}', [AdminController::class, 'update'])->name('role.update');

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
Route::post('/attendance', [AttendanceController::class, 'index'])->name('attendance.filter');
Route::get('/asummary', [AttendanceController::class, 'asummary'])->name('attendance.summary');
Route::post('/asummary', [AttendanceController::class, 'asummary'])->name('attendance.summary');
// Work Time
Route::get('/workTime', [AttendanceController::class, 'workTime'])->name('workTime');
Route::get('/workHoliday', [AttendanceController::class, 'workHoliday'])->name('workHoliday');
Route::post('/work', [AttendanceController::class, 'store'])->name('work.store');
Route::post('/work/{id}', [AttendanceController::class, 'update'])->name('work.update');

// Head
Route::get('/head', [HeadController::class, 'index'])->name('head');
Route::get('/headType', [HeadController::class, 'headType'])->name('head.headType');
Route::get('/addHead', [HeadController::class, 'create'])->name('head.add');
Route::post('/head', [HeadController::class, 'store'])->name('head.store');
Route::post('/head/{id}', [HeadController::class, 'update'])->name('head.update');

// Category
Route::get('/category', [CategoryController::class, 'index'])->name('category');
Route::get('/addCategory', [CategoryController::class, 'create'])->name('category.add');
Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
Route::post('/category/{id}', [CategoryController::class, 'update'])->name('category.update');

// Customer
Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
Route::get('/addCustomer', [CustomerController::class, 'create'])->name('customer.add');
Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
Route::get('/customer/{id}', [CustomerController::class, 'show'])->name('customer.show');
Route::get('/editCustomer/{id}', [CustomerController::class, 'edit'])->name('customer.edit');
Route::post('/customer/{id}', [CustomerController::class, 'update'])->name('customer.update');
Route::get('/cLedger/{id}', [CustomerController::class, 'detail'])->name('customer.detail');
Route::post('/cLedger/{id}', [CustomerController::class, 'detail'])->name('customer.filter');

// Employee
Route::get('/employee', [EmployeeController::class, 'index'])->name('employee');
Route::get('/addEmployee', [EmployeeController::class, 'create'])->name('employee.add');
Route::post('/employee', [EmployeeController::class, 'store'])->name('employee.store');
Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
Route::get('/editEmployee/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
Route::post('/employee/{id}', [EmployeeController::class, 'update'])->name('employee.update');
Route::get('/eLedger/{id}', [EmployeeController::class, 'detail'])->name('employee.detail');
Route::post('/eLedger/{id}', [EmployeeController::class, 'detail'])->name('employee.filter');

// Vendor
// Route::get('/vendor', [VendorController::class, 'index'])->name('vendor');
Route::get('/vendor', [VendorController::class, 'vendor'])->name('vendor');
Route::get('/contractor', [VendorController::class, 'contractor'])->name('contractor');
Route::get('/addVendor', [VendorController::class, 'create'])->name('vendor.add');
Route::get('/addContractor', [VendorController::class, 'create2'])->name('vendor.add2');
Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
Route::get('/vendor/{id}', [VendorController::class, 'show'])->name('vendor.show');
Route::get('/contractor/{id}', [VendorController::class, 'show2'])->name('vendor.show2');
Route::get('/editVendor/{id}', [VendorController::class, 'edit'])->name('vendor.edit');
Route::get('/editContractor/{id}', [VendorController::class, 'edit2'])->name('vendor.edit2');
Route::post('/vendor/{id}', [VendorController::class, 'update'])->name('vendor.update');
Route::get('/vLedger/{id}', [VendorController::class, 'detail'])->name('vendor.detail');
Route::post('/vLedger/{id}', [VendorController::class, 'detail'])->name('vendor.filter');

// Material
Route::get('/material', [MaterialController::class, 'index'])->name('material');
Route::get('/addMaterial', [MaterialController::class, 'create'])->name('material.add');
Route::post('/material', [MaterialController::class, 'store'])->name('material.store');
Route::get('/material/{id}', [MaterialController::class, 'show'])->name('material.show');
Route::get('/editMaterial/{id}', [MaterialController::class, 'edit'])->name('material.edit');
Route::post('/material/{id}', [MaterialController::class, 'update'])->name('material.update');
Route::get('/materialDetail', [MaterialController::class, 'detail'])->name('material.detail');
Route::post('/materialDetail', [MaterialController::class, 'detail'])->name('material.filter');

// Machine
Route::get('/machine', [MachineController::class, 'index'])->name('machine');
Route::get('/addMachine', [MachineController::class, 'create'])->name('machine.add');
Route::post('/machine', [MachineController::class, 'store'])->name('machine.store');
Route::get('/machine/{id}', [MachineController::class, 'show'])->name('machine.show');
Route::get('/editMachine/{id}', [MachineController::class, 'edit'])->name('machine.edit');
Route::post('/machine/{id}', [MachineController::class, 'update'])->name('machine.update');

// Product
Route::get('/product', [ProductController::class, 'index'])->name('product');
Route::get('/addProduct', [ProductController::class, 'create'])->name('product.add');
Route::post('/product', [ProductController::class, 'store'])->name('product.store');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::get('/editProduct/{id}', [ProductController::class, 'edit'])->name('product.edit');
Route::post('/product/{id}', [ProductController::class, 'update'])->name('product.update');

// Order
Route::get('/order', [OrderController::class, 'index'])->name('order');
Route::get('/addOrder', [OrderController::class, 'create'])->name('order.add');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
Route::get('/orderEst/{id}', [OrderController::class, 'estimate'])->name('order.estimate');
Route::get('/orderStatus/{id}', [OrderController::class, 'status'])->name('order.status');
Route::get('/editOrder/{id}', [OrderController::class, 'edit'])->name('order.edit');
Route::post('/order/{id}', [OrderController::class, 'update'])->name('order.update');
Route::get('/orderStatus/{id}/{status}', [OrderController::class, 'updateStatus'])->name('order.updateStatus');

// Delivery
Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery');
Route::get('/addDelivery/{id}', [DeliveryController::class, 'create2'])->name('delivery.add');
Route::post('/addDelivery/{id}', [DeliveryController::class, 'create2'])->name('delivery.add');
Route::post('/delivery', [DeliveryController::class, 'store'])->name('delivery.store');
Route::get('/delivery/{id}', [DeliveryController::class, 'show'])->name('delivery.show');
Route::get('/editDelivery/{id}', [DeliveryController::class, 'edit'])->name('delivery.edit');
Route::post('/delivery/{id}', [DeliveryController::class, 'update'])->name('delivery.update');
Route::get('/deliveryStatus/{id}/{status}', [DeliveryController::class, 'updateStatus'])->name('delivery.updateStatus');

// Product Material
Route::get('/productMaterial', [ProductMaterialController::class, 'index'])->name('productMaterial');
// Route::get('/addProductMaterial', [ProductMaterialController::class, 'create'])->name('productMaterial.add');
Route::get('/addProductMaterial/{id}', [ProductMaterialController::class, 'create2'])->name('productMaterial.add');
Route::post('/addProductMaterial/{id}', [ProductMaterialController::class, 'create2'])->name('productMaterial.add');
Route::post('/productMaterial', [ProductMaterialController::class, 'store'])->name('productMaterial.store');
Route::get('/productMaterial/{id}', [ProductMaterialController::class, 'show'])->name('productMaterial.show');
Route::get('/editProductMaterial/{id}', [ProductMaterialController::class, 'edit'])->name('productMaterial.edit');
Route::post('/productMaterial/{id}', [ProductMaterialController::class, 'update'])->name('productMaterial.update');

// Material Processing
Route::get('/mprocess', [MProcessController::class, 'index'])->name('mprocess');
Route::get('/addMProcess', [MProcessController::class, 'create'])->name('mprocess.add');
Route::post('/mprocess', [MProcessController::class, 'store'])->name('mprocess.store');
Route::get('/mprocess/{id}', [MProcessController::class, 'show'])->name('mprocess.show');
Route::get('/editMProcess/{id}', [MProcessController::class, 'edit'])->name('mprocess.edit');
Route::post('/mprocess/{id}', [MProcessController::class, 'update'])->name('mprocess.update');

// Purchase
Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase');
Route::get('/addPurchase', [PurchaseController::class, 'create'])->name('purchase.add');
Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
Route::get('/editPurchase/{id}', [PurchaseController::class, 'edit'])->name('purchase.edit');
Route::post('/purchase/{id}', [PurchaseController::class, 'update'])->name('purchase.update');
Route::get('/ajaxPMQty', [PurchaseController::class, 'ajaxPMQty'])->name('ajaxPMQty'); //Material Qty
Route::get('/addProductPurchase', [PurchaseController::class, 'create2'])->name('productPurchase.add');
Route::get('/editProductPurchase/{id}', [PurchaseController::class, 'edit2'])->name('productPurchase.edit');

// Purchase Receive
Route::get('/receive', [ReceiveController::class, 'index'])->name('receive');
Route::get('/addReceive/{id}', [ReceiveController::class, 'create'])->name('receive.add');
Route::post('/addReceive/{id}', [ReceiveController::class, 'create'])->name('receive.add');
Route::post('/receive', [ReceiveController::class, 'store'])->name('receive.store');
Route::get('/receive/{id}', [ReceiveController::class, 'show'])->name('receive.show');
Route::get('/editReceive/{id}', [ReceiveController::class, 'edit'])->name('receive.edit');
Route::post('/receive/{id}', [ReceiveController::class, 'update'])->name('receive.update');
Route::get('/receiveStatus/{id}/{status}', [ReceiveController::class, 'updateStatus'])->name('receive.updateStatus');

// Purchase Return
Route::get('/return', [ReturnController::class, 'index'])->name('return');
Route::get('/addReturn/{id}', [ReturnController::class, 'create'])->name('return.add');
Route::post('/addReturn/{id}', [ReturnController::class, 'create'])->name('return.add');
Route::post('/return', [ReturnController::class, 'store'])->name('return.store');
Route::get('/return/{id}', [ReturnController::class, 'show'])->name('return.show');
Route::get('/editReturn/{id}', [ReturnController::class, 'edit'])->name('return.edit');
Route::post('/return/{id}', [ReturnController::class, 'update'])->name('return.update');

// Issuance Group
Route::get('/igroup', [IGroupController::class, 'index'])->name('igroup');
Route::get('/addIGroup', [IGroupController::class, 'create'])->name('igroup.add');
Route::post('/igroup', [IGroupController::class, 'store'])->name('igroup.store');
Route::get('/igroup/{id}', [IGroupController::class, 'show'])->name('igroup.show');
Route::get('/editIGroup/{id}', [IGroupController::class, 'edit'])->name('igroup.edit');
Route::post('/igroup/{id}', [IGroupController::class, 'update'])->name('igroup.update');

// Stock / Issuance
Route::get('/stock', [StockController::class, 'index'])->name('stock');
Route::get('/issue', [StockController::class, 'issue'])->name('issue');
Route::get('/dailyIssue', [StockController::class, 'dailyIssue'])->name('stock.daily');
Route::post('/dailyIssue', [StockController::class, 'dailyIssue'])->name('stock.filter');
Route::get('/addIssue', [StockController::class, 'create'])->name('stock.add');
Route::get('/addGIssue', [StockController::class, 'gcreate'])->name('stock.gadd');
Route::post('/gissue', [StockController::class, 'gstore'])->name('stock.gstore');
Route::post('/issue', [StockController::class, 'store'])->name('stock.store');
Route::get('/issue/{id}', [StockController::class, 'show'])->name('stock.show');
Route::get('/editIssue/{id}', [StockController::class, 'edit'])->name('stock.edit');
Route::post('/issue/{id}', [StockController::class, 'update'])->name('stock.update');
Route::get('/ajaxPM', [StockController::class, 'ajaxPM'])->name('ajaxPM'); //Product Material
Route::get('/ajaxPT', [StockController::class, 'ajaxPT'])->name('ajaxPT'); //Product Type
Route::get('/ajaxPC', [StockController::class, 'ajaxPC'])->name('ajaxPC'); //Product Cost
Route::get('/ajaxPS', [StockController::class, 'ajaxPS'])->name('ajaxPS'); //Product Stage
Route::get('/ajaxIG', [StockController::class, 'ajaxIG'])->name('ajaxIG'); //Issuance Group
Route::get('/ajaxMQty', [StockController::class, 'ajaxMQty'])->name('ajaxMQty'); //Material Qty
Route::get('/ajaxAMQty', [StockController::class, 'ajaxAMQty'])->name('ajaxAMQty'); //Article's MQty
Route::get('/ajaxATMQty', [StockController::class, 'ajaxATMQty'])->name('ajaxATMQty'); //AType MQty

// Machine Material Issuance
Route::get('/issueMM', [StockController::class, 'issue2'])->name('missue');
Route::get('/addIMM', [StockController::class, 'create2'])->name('mstock.add');
Route::get('/issueMM/{id}', [StockController::class, 'show2'])->name('mstock.show');
Route::get('/editIMM/{id}', [StockController::class, 'edit2'])->name('mstock.edit');

// Receive Issuance
Route::get('/receiveIssue', [StockController::class, 'rIssue'])->name('receiveIssue');
Route::get('/dailyReceive', [StockController::class, 'dailyReceive'])->name('rstock.daily');
Route::post('/dailyReceive', [StockController::class, 'dailyReceive'])->name('rstock.filter');
Route::get('/addReceiveIssue/{id}', [StockController::class, 'rCreate'])->name('rstock.add');
Route::post('/addReceiveIssue/{id}', [StockController::class, 'rCreate'])->name('rstock.add');
Route::get('/receiveIssue/{id}', [StockController::class, 'rShow'])->name('rstock.show');
Route::get('/editReceiveIssue/{id}', [StockController::class, 'rEdit'])->name('rstock.edit');

// Wages
Route::get('/wages', [StockController::class, 'wages'])->name('wages');
Route::get('/wages/{id}', [StockController::class, 'wShow'])->name('wages.show');
Route::post('/wages/{id}', [StockController::class, 'wShow'])->name('wages.filter');

// Product Costing
Route::get('/productCost', [ProductCostController::class, 'index'])->name('productCost');
Route::get('/addProductCost', [ProductCostController::class, 'create'])->name('productCost.add');
Route::post('/productCost', [ProductCostController::class, 'store'])->name('productCost.store');
Route::get('/productCost/{id}', [ProductCostController::class, 'show'])->name('productCost.show');
Route::get('/editProductCost/{id}', [ProductCostController::class, 'edit'])->name('productCost.edit');
Route::post('/productCost/{id}', [ProductCostController::class, 'update'])->name('productCost.update');

// Transaction
Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction');
Route::get('/ajaxBank', [TransactionController::class, 'ajaxBank'])->name('ajaxBank'); //Bank Account
Route::post('/transaction', [TransactionController::class, 'store'])->name('transaction.store');
Route::get('/transaction/{id}', [TransactionController::class, 'show'])->name('transaction.show');
Route::post('/transaction/{id}', [TransactionController::class, 'update'])->name('transaction.update');
// Transaction Employee
Route::get('/ePayment', [TransactionController::class, 'ePayment'])->name('ePayment');
Route::get('/addEPayment', [TransactionController::class, 'createEPayment'])->name('transaction.addEPayment');
Route::get('/ePayment/{id}', [TransactionController::class, 'showEPayment'])->name('transaction.showEPayment');
Route::get('/editEPayment/{id}', [TransactionController::class, 'editEPayment'])->name('transaction.editEPayment');
// Transaction Vendor
Route::get('/vPayment', [TransactionController::class, 'vPayment'])->name('vPayment');
Route::get('/cPayment', [TransactionController::class, 'cPayment'])->name('cPayment');
Route::get('/ajaxPurchase', [TransactionController::class, 'ajaxPurchase'])->name('ajaxPurchase'); //Purchases
Route::get('/vPayment/{id}', [TransactionController::class, 'showVPayment'])->name('transaction.showVPayment');
Route::get('/cPayment/{id}', [TransactionController::class, 'showCPayment'])->name('transaction.showCPayment');
Route::get('/addVPayment', [TransactionController::class, 'createVPayment'])->name('transaction.addVPayment');
Route::get('/addCPayment', [TransactionController::class, 'createCPayment'])->name('transaction.addCPayment');
Route::get('/editVPayment/{id}', [TransactionController::class, 'editVPayment'])->name('transaction.editVPayment');
Route::get('/editCPayment/{id}', [TransactionController::class, 'editCPayment'])->name('transaction.editCPayment');
// Transaction Expense
Route::get('/expense', [TransactionController::class, 'expense'])->name('expense');
Route::get('/addExpense', [TransactionController::class, 'createExpense'])->name('transaction.addExpense');
Route::get('/expense/{id}', [TransactionController::class, 'showExpense'])->name('transaction.showExpense');
Route::get('/editExpense/{id}', [TransactionController::class, 'editExpense'])->name('transaction.editExpense');
// Transaction BRS
// Route::get('/brs', [TransactionController::class, 'brs'])->name('brs');
Route::get('/addBRS', [TransactionController::class, 'createBRS'])->name('transaction.addBRS');
Route::get('/BRS/{id}', [TransactionController::class, 'showBRS'])->name('transaction.showBRS');
Route::get('/editBRS/{id}', [TransactionController::class, 'editBRS'])->name('transaction.editBRS');
// Bank Balance
Route::get('/bankBalance', [TransactionController::class, 'bankBalance'])->name('bankBalance');
Route::get('/bankBalance/{id}', [TransactionController::class, 'showBBalance'])->name('transaction.showBBalance');
Route::post('/bankBalance/{id}', [TransactionController::class, 'showBBalance'])->name('bankBalance.filter');
Route::get('/cashBalance', [TransactionController::class, 'cashBalance'])->name('cashBalance');
Route::post('/cashBalance', [TransactionController::class, 'cashBalance'])->name('cashBalance.filter');

// Order Payment
Route::get('/oPayment', [TransactionController::class, 'oPayment'])->name('oPayment');
Route::get('/ajaxOrder', [TransactionController::class, 'ajaxOrder'])->name('ajaxOrder'); //Dynamic Orders
Route::get('/addOPayment', [TransactionController::class, 'createOPayment'])->name('transaction.addOPayment');
Route::get('/oPayment/{id}', [TransactionController::class, 'showOPayment'])->name('transaction.showOPayment');
Route::get('/editOPayment/{id}', [TransactionController::class, 'editOPayment'])->name('transaction.editOPayment');

// Bank
Route::get('/bank', [BankController::class, 'index'])->name('bank');
Route::get('/addBank', [BankController::class, 'create'])->name('bank.add');
Route::post('/bank', [BankController::class, 'store'])->name('bank.store');
Route::get('/bank/{id}', [BankController::class, 'show'])->name('bank.show');
Route::get('/editBank/{id}', [BankController::class, 'edit'])->name('bank.edit');
Route::post('/bank/{id}', [BankController::class, 'update'])->name('bank.update');
