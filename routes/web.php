<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiveController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\ProductMaterialController;

// --------------------------------------
// ---------- Auth Controllers ----------
// --------------------------------------
Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ---------------------------------------
// ---------- Other Controllers ----------
// ---------------------------------------

Route::get('/admin', [AdminController::class, 'index'])->name('admin');
Route::post('/image/{id}/{dir}', [ImageController::class, 'destroy'])->name('image.delete');

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
Route::get('/customerDetail/{id}', [CustomerController::class, 'detail'])->name('customer.detail');

// Employee
Route::get('/employee', [EmployeeController::class, 'index'])->name('employee');
Route::get('/addEmployee', [EmployeeController::class, 'create'])->name('employee.add');
Route::post('/employee', [EmployeeController::class, 'store'])->name('employee.store');
Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
Route::get('/editEmployee/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
Route::post('/employee/{id}', [EmployeeController::class, 'update'])->name('employee.update');
Route::get('/employeeDetail/{id}', [EmployeeController::class, 'detail'])->name('employee.detail');

// Vendor
Route::get('/vendor', [VendorController::class, 'index'])->name('vendor');
Route::get('/addVendor', [VendorController::class, 'create'])->name('vendor.add');
Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
Route::get('/vendor/{id}', [VendorController::class, 'show'])->name('vendor.show');
Route::get('/editVendor/{id}', [VendorController::class, 'edit'])->name('vendor.edit');
Route::post('/vendor/{id}', [VendorController::class, 'update'])->name('vendor.update');
Route::get('/vendorDetail/{id}', [VendorController::class, 'detail'])->name('vendor.detail');

// Material
Route::get('/material', [MaterialController::class, 'index'])->name('material');
Route::get('/addMaterial', [MaterialController::class, 'create'])->name('material.add');
Route::post('/material', [MaterialController::class, 'store'])->name('material.store');
Route::get('/material/{id}', [MaterialController::class, 'show'])->name('material.show');
Route::get('/editMaterial/{id}', [MaterialController::class, 'edit'])->name('material.edit');
Route::post('/material/{id}', [MaterialController::class, 'update'])->name('material.update');

// Product
Route::get('/product', [ProductController::class, 'index'])->name('product');
Route::get('/addProduct', [ProductController::class, 'create'])->name('product.add');
Route::post('/product', [ProductController::class, 'store'])->name('product.store');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::get('/editProduct/{id}', [ProductController::class, 'edit'])->name('product.edit');
Route::post('/product/{id}', [ProductController::class, 'update'])->name('product.update');

// Box
Route::get('/box', [BoxController::class, 'index'])->name('box');
Route::get('/addBox', [BoxController::class, 'create'])->name('box.add');
Route::post('/box', [BoxController::class, 'store'])->name('box.store');
Route::get('/box/{id}', [BoxController::class, 'show'])->name('box.show');
Route::get('/editBox/{id}', [BoxController::class, 'edit'])->name('box.edit');
Route::post('/box/{id}', [BoxController::class, 'update'])->name('box.update');

// Order
Route::get('/order', [OrderController::class, 'index'])->name('order');
Route::get('/addOrder', [OrderController::class, 'create'])->name('order.add');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
Route::get('/orderEst/{id}', [OrderController::class, 'estimate'])->name('order.estimate');
Route::get('/editOrder/{id}', [OrderController::class, 'edit'])->name('order.edit');
Route::post('/order/{id}', [OrderController::class, 'update'])->name('order.update');
Route::get('/orderStatus/{id}/{status}', [OrderController::class, 'updateStatus'])->name('order.updateStatus');

// Product Material
Route::get('/productMaterial', [ProductMaterialController::class, 'index'])->name('productMaterial');
// Route::get('/addProductMaterial', [ProductMaterialController::class, 'create'])->name('productMaterial.add');
Route::get('/addProductMaterial/{id}', [ProductMaterialController::class, 'create2'])->name('productMaterial.add');
Route::post('/addProductMaterial/{id}', [ProductMaterialController::class, 'create2'])->name('productMaterial.add');
Route::post('/productMaterial', [ProductMaterialController::class, 'store'])->name('productMaterial.store');
Route::get('/productMaterial/{id}', [ProductMaterialController::class, 'show'])->name('productMaterial.show');
Route::get('/editProductMaterial/{id}', [ProductMaterialController::class, 'edit'])->name('productMaterial.edit');
Route::post('/productMaterial/{id}', [ProductMaterialController::class, 'update'])->name('productMaterial.update');

// Purchase
Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase');
Route::get('/addPurchase', [PurchaseController::class, 'create'])->name('purchase.add');
Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
Route::get('/editPurchase/{id}', [PurchaseController::class, 'edit'])->name('purchase.edit');
Route::post('/purchase/{id}', [PurchaseController::class, 'update'])->name('purchase.update');

// Purchase Receive
Route::get('/receive', [ReceiveController::class, 'index'])->name('receive');
Route::get('/addReceive/{id}', [ReceiveController::class, 'create'])->name('receive.add');
Route::post('/receive', [ReceiveController::class, 'store'])->name('receive.store');
Route::get('/receive/{id}', [ReceiveController::class, 'show'])->name('receive.show');
Route::get('/editReceive/{id}', [ReceiveController::class, 'edit'])->name('receive.edit');
Route::post('/receive/{id}', [ReceiveController::class, 'update'])->name('receive.update');
Route::get('/receiveStatus/{id}/{status}', [ReceiveController::class, 'updateStatus'])->name('receive.updateStatus');

// Purchase Return
Route::get('/return', [ReturnController::class, 'index'])->name('return');
Route::get('/addReturn/{id}', [ReturnController::class, 'create'])->name('return.add');
Route::post('/return', [ReturnController::class, 'store'])->name('return.store');
Route::get('/return/{id}', [ReturnController::class, 'show'])->name('return.show');
Route::get('/editReturn/{id}', [ReturnController::class, 'edit'])->name('return.edit');
Route::post('/return/{id}', [ReturnController::class, 'update'])->name('return.update');

// Stock / Issuance
Route::get('/stock', [StockController::class, 'index'])->name('stock');
Route::get('/addIssue', [StockController::class, 'create'])->name('stock.add');
Route::post('/issue', [StockController::class, 'store'])->name('stock.store');
Route::get('/issue/{id}', [StockController::class, 'show'])->name('stock.show');
Route::get('/editIssue/{id}', [StockController::class, 'edit'])->name('stock.edit');
Route::post('/issue/{id}', [StockController::class, 'update'])->name('stock.update');
Route::get('/ajaxPM', [StockController::class, 'ajaxPM'])->name('ajaxPM'); //Product Material
Route::get('/ajaxPT', [StockController::class, 'ajaxPT'])->name('ajaxPT'); //Product Type
Route::get('/ajaxPC', [StockController::class, 'ajaxPC'])->name('ajaxPC'); //Product Cost

// Receive Issuance
Route::get('/issue', [StockController::class, 'issue'])->name('issue');
Route::get('/receiveIssue', [StockController::class, 'rIssue'])->name('receiveIssue');
Route::get('/addReceiveIssue/{id}', [StockController::class, 'rCreate'])->name('rstock.add');
Route::post('/addReceiveIssue/{id}', [StockController::class, 'rCreate'])->name('rstock.add');
Route::get('/receiveIssue/{id}', [StockController::class, 'rShow'])->name('rstock.show');
Route::get('/editReceiveIssue/{id}', [StockController::class, 'rEdit'])->name('rstock.edit');

// Receive Issuance
Route::get('/wages', [StockController::class, 'wages'])->name('wages');
Route::get('/wages/{id}', [StockController::class, 'wShow'])->name('wages.show');

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
Route::get('/vPayment/{id}', [TransactionController::class, 'showVPayment'])->name('transaction.showVPayment');
Route::get('/addVPayment', [TransactionController::class, 'createVPayment'])->name('transaction.addVPayment');
Route::get('/editVPayment/{id}', [TransactionController::class, 'editVPayment'])->name('transaction.editVPayment');
// Transaction Expense
Route::get('/expense', [TransactionController::class, 'expense'])->name('expense');
Route::get('/addExpense', [TransactionController::class, 'createExpense'])->name('transaction.addExpense');
Route::get('/expense/{id}', [TransactionController::class, 'showExpense'])->name('transaction.showExpense');
Route::get('/editExpense/{id}', [TransactionController::class, 'editExpense'])->name('transaction.editExpense');
// Bank Balance
Route::get('/bankBalance', [TransactionController::class, 'bankBalance'])->name('bankBalance');
Route::get('/bankBalance/{id}', [TransactionController::class, 'showBBalance'])->name('transaction.showBBalance');
Route::get('/cashBalance', [TransactionController::class, 'cashBalance'])->name('cashBalance');
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
