<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;



// Oddiy GET so'rovi
Route::get('products', [ProductController::class, 'index']);
