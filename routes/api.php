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
    Route::post('mobile/classes/add',[MobileController::class,'add_class']);
    Route::get('mobile/classes/show',[MobileController::class,'show_classes']);
    Route::get('mobile/classes/update',[MobileController::class,'update_class']);
    Route::get('mobile/classes/remove',[MobileController::class,'remove_class']);
    Route::get('mobile/classes/show/specific',[MobileController::class,'show_class']);
    Route::get('mobile/class/students/show',[MobileController::class,'show_class_students']);
    Route::post('mobile/class/students/update',[MobileController::class,'update_class_students']);   //DEL ADD FROM CLASS
    Route::post('mobile/students/add',[MobileController::class,'add_student']);
    Route::post('mobile/students/update',[MobileController::class,'update_student']);
    Route::get('mobile/students/show/specific',[MobileController::class,'show_student']);
    Route::get('mobile/students/show',[MobileController::class,'show_students']);
    Route::get('mobile/students/remove',[MobileController::class,'remove_student']);
    Route::post('mobile/email/all',[MobileController::class,'send_all']);
    Route::post('mobile/email/specific',[MobileController::class,'send_specific']);
    Route::post('mobile/attendance/mark',[MobileController::class,'mark_attendance']);
    #EXTRAS
    Route::get('mobile/calender/date_wise',[MobileController::class,'show_attendance']);
    Route::get('mobile/students/show/wrt_date',[MobileController::class,'specific_students']);
    Route::get('mobile/students/search',[MobileController::class,'search_students']);
    Route::get('mobile/calender/classes/count',[MobileController::class,'classes_count']);
    Route::get('mobile/calender/classes/specific_date',[MobileController::class,'classes_specific']);
});

#default
Route::get('default',function(){
    $str['status']=false;
    $str['message']="USER IS NOT AUTHENTICATED";
    return $str;
})->name('default');
Route::post('mobile/login',[MobileController::class,'login']);
