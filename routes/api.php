<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DisCountController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'categories', 'middleware' => ['auth:sanctum', 'throttle:70,1']], function () {

    Route::post('/store', [CategoryController::class , 'store']);
    Route::put('/update/{id}', [CategoryController::class , 'update']);
    Route::get('/all', [CategoryController::class , 'index']);
    Route::get('/show/{id}', [CategoryController::class , 'show']);
    Route::delete('/delete/{id}', [CategoryController::class , 'destroy']);
    Route::get('/item_of_category/{category_id}', [CategoryController::class , 'getItems']);
    Route::get('/get_subcategory_of_category/{category_id}', [CategoryController::class , 'get_subcategory_of_category']);

});


Route::group(['prefix' => 'sub_category', 'middleware' => ['auth:sanctum', 'throttle:70,1']], function () {

    Route::post('/store_for_category/{category_id}', [SubCategoryController::class , 'store_for_category']);
    Route::post('/store_for_sub_category/{sub_category_id}', [SubCategoryController::class , 'store_for_sub_category']);
    Route::put('/update/{sub_category_id}', [SubCategoryController::class , 'update']);
    Route::delete('/delete/{sub_category_id}', [SubCategoryController::class , 'destroy']);

    Route::get('/get_all_item_of_sub_category/{subCategory_id}', [SubCategoryController::class , 'getItems']);
    Route::get('/get_subcategory_of_sub_category/{subCategory_id}', [SubCategoryController::class , 'get_subcategory_of_sub_category']);

});





Route::group(['prefix' => 'item', 'middleware' => ['auth:sanctum', 'throttle:70,1']], function () {

    Route::post('/addItemToCategory/{category_id}', [ItemController::class , 'addItemToCategory']);
    Route::post('/addItemToSubCategory/{SUB_category_id}', [ItemController::class , 'addItemToSubCategory']);
    Route::put('/update/{item_id}', [ItemController::class , 'update']);
    Route::delete('/delete/{item_id}', [ItemController::class , 'destroy']);
    Route::get('/getItemsByCategoryOrSubcategory', [ItemController::class , 'getItemsByCategoryOrSubcategory']);
    Route::get('/getItemsByCategoryOrSubcategory_all', [ItemController::class , 'getItemsByCategoryOrSubcategory_all']);


});



Route::group(['prefix' => 'discount', 'middleware' => ['auth:sanctum', 'throttle:70,1']], function () {

    Route::post('/addDiscountToCategory/{discount_id}', [DisCountController::class , 'addDiscountToCategory']);
    Route::post('/addDiscountToSubCategory/{discount_id}', [DisCountController::class , 'addDiscountToSubCategory']);
    Route::post('/addDiscountToItem/{discount_id}', [DisCountController::class , 'addDiscountToItem']);
    Route::put('/update/{discount_id}', [DisCountController::class , 'update']);
    Route::delete('/delete/{discount_id}', [DisCountController::class , 'destroy']);
    Route::get('/get_all_discount_category_OR_subCategory', [DisCountController::class , 'getDiscounted']);



});



Route::group(['prefix' => 'menus', 'middleware' => ['auth:sanctum', 'throttle:70,1']], function () {


    Route::post('/store', [MenuController::class, 'store']);
    Route::put('/update/{menu_id}', [MenuController::class, 'update']);
    Route::delete('/delete/{menu_id}', [MenuController::class, 'destroy']);
    Route::get('/index', [MenuController::class, 'index']);
    Route::get('/all_category_of_menu/{id}', [MenuController::class, 'all_category_of_menu']);
    Route::get('/menu_with_categories/{menu_id}', [MenuController::class, 'menu_with_categories']);

});



Route::post('register', [UserApiController::class, 'register']);
Route::post('login', [UserApiController::class, 'login'])->name('login');
Route::post('logout', [UserApiController::class, 'logout'])->middleware('auth:sanctum');
