<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\ProductMaterialController;
use App\Http\Controllers\ReceiveMaterialController;

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

// Employee
Route::get('/employee', [EmployeeController::class, 'index'])->name('employee');
Route::get('/addEmployee', [EmployeeController::class, 'create'])->name('employee.add');
Route::post('/employee', [EmployeeController::class, 'store'])->name('employee.store');
Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
Route::get('/editEmployee/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
Route::post('/employee/{id}', [EmployeeController::class, 'update'])->name('employee.update');

// Vendor
Route::get('/vendor', [VendorController::class, 'index'])->name('vendor');
Route::get('/addVendor', [VendorController::class, 'create'])->name('vendor.add');
Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
Route::get('/vendor/{id}', [VendorController::class, 'show'])->name('vendor.show');
Route::get('/editVendor/{id}', [VendorController::class, 'edit'])->name('vendor.edit');
Route::post('/vendor/{id}', [VendorController::class, 'update'])->name('vendor.update');

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

// Product Material
Route::get('/productMaterial', [ProductMaterialController::class, 'index'])->name('productMaterial');
Route::get('/addProductMaterial', [ProductMaterialController::class, 'create'])->name('productMaterial.add');
Route::post('/productMaterial', [ProductMaterialController::class, 'store'])->name('productMaterial.store');
Route::get('/productMaterial/{id}', [ProductMaterialController::class, 'show'])->name('productMaterial.show');
Route::get('/editProductMaterial/{id}', [ProductMaterialController::class, 'edit'])->name('productMaterial.edit');
Route::post('/productMaterial/{id}', [ProductMaterialController::class, 'update'])->name('productMaterial.update');

// Purchase
Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase');
Route::get('/addPurchase', [PurchaseController::class, 'create'])->name('purchase.add');
Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
Route::get('/editpurchase/{id}', [PurchaseController::class, 'edit'])->name('purchase.edit');
Route::post('/purchase/{id}', [PurchaseController::class, 'update'])->name('purchase.update');

// Purchase Receive
Route::get('/receive', [ReceiveMaterialController::class, 'index'])->name('receive');
Route::get('/addReceive/{id}', [ReceiveMaterialController::class, 'create'])->name('receive.add');
Route::post('/receive', [ReceiveMaterialController::class, 'store'])->name('receive.store');
Route::get('/receive/{id}', [ReceiveMaterialController::class, 'show'])->name('receive.show');

// Order
Route::get('/order', [OrderController::class, 'index'])->name('order');
Route::get('/addOrder', [OrderController::class, 'create'])->name('order.add');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
Route::get('/editOrder/{id}', [OrderController::class, 'edit'])->name('order.edit');
Route::post('/order/{id}', [OrderController::class, 'update'])->name('order.update');
Route::get('/orderStatus/{id}/{status}', [OrderController::class, 'updateStatus'])->name('order.updateStatus');

