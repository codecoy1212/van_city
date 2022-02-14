<?php

use App\Http\Controllers\MobileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => 'auth:sanctum'], function(){
    //All secure URL's
    #MAIN
    Route::post('mobile/logout',[MobileController::class,'log_out']);
    Route::post('mobile/add_class',[MobileController::class,'add_class']);
    Route::get('mobile/show_classes',[MobileController::class,'show_classes']);
    Route::post('mobile/update_class',[MobileController::class,'update_class']);
    Route::post('mobile/remove_class',[MobileController::class,'remove_class']);
    Route::get('mobile/show_specific_class',[MobileController::class,'show_class']);
    Route::get('mobile/show_class_students',[MobileController::class,'show_class_students']);
    Route::post('mobile/update_class_students',[MobileController::class,'update_class_students']);   //DEL ADD FROM CLASS
    Route::post('mobile/add_student',[MobileController::class,'add_student']);
    Route::post('mobile/update_student',[MobileController::class,'update_student']);
    Route::get('mobile/show_specific_student',[MobileController::class,'show_student']);
    Route::get('mobile/show_students',[MobileController::class,'show_students']);
    Route::post('mobile/remove_student',[MobileController::class,'remove_student']);
    Route::post('mobile/email_all',[MobileController::class,'send_all']);
    Route::post('mobile/email_specific',[MobileController::class,'send_specific']);
    Route::post('mobile/mark_attendance',[MobileController::class,'mark_attendance']);
    Route::post('mobile/delete_class_students',[MobileController::class,'delete_class_students']);
    Route::post('mobile/add_payment',[MobileController::class,'add_payment']);
    Route::get('mobile/payment_history',[MobileController::class,'payment_history']);
    Route::post('mobile/payment_collected',[MobileController::class,'payment_collected']);
    Route::post('mobile/email_students',[MobileController::class,'email_students']);
    Route::post('mobile/email_class',[MobileController::class,'email_class']);
    Route::get('mobile/get_notifications',[MobileController::class,'get_notifications']);
    #EXTRAS
    Route::get('mobile/show_attendance',[MobileController::class,'show_attendance']);
    Route::get('mobile/search_students',[MobileController::class,'search_students']);
    Route::get('mobile/calender_classes',[MobileController::class,'classes_count']);
});

#default
Route::get('default',function(){
    $str['status']=false;
    $str['message']="USER IS NOT AUTHENTICATED";
    return $str;
})->name('default');
Route::post('mobile/login',[MobileController::class,'login']);

