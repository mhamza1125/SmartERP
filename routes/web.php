<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;

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
Route::post('/image/{id}', [ImageController::class, 'destroy'])->name('image.delete');

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
