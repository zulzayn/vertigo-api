<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Login
Route::post('/login' , 'Api\AuthController@login');

Route::middleware('auth:api')->group(function () {
    //User
    Route::resource('user', 'Api\UserController');
    //Register
    Route::post('/register' , 'Api\AuthController@register');
    //Role
    Route::resource('role', 'Api\RoleController');
    //Equipment
    Route::get('/equipment/getEquimentCategories', 'Api\EquipmentController@getEquimentCategories')->name('equipment.getEquimentCategories');
    Route::get('/equipment/getAvailableEquipment', 'Api\EquipmentController@getAvailableEquipment')->name('equipment.getAvailableEquipment');
    Route::resource('equipment', 'Api\EquipmentController');
    //Transport
    Route::get('/transport/getTransportCategories', 'Api\TransportController@getTransportCategories')->name('transport.getTransportCategories');
    Route::get('/transport/getAvailableTransport', 'Api\TransportController@getAvailableTransport')->name('transport.getAvailableTransport');
    Route::resource('transport', 'Api\TransportController');
    //SAS
    Route::post('/sas/endTask/{id}', 'Api\SASController@endTask')->name('sas.endTask');
    Route::post('/sas/updateProgress/{id}', 'Api\SASController@updateProgress')->name('sas.updateProgress');
    Route::post('/sas/startTask/{id}', 'Api\SASController@startTask')->name('sas.startTask');
    Route::get('/sas/acknowledge/{id}', 'Api\SASController@acknowledge')->name('sas.acknowledge');
    Route::get('/sas/approve/{id}', 'Api\SASController@approve')->name('sas.approve');
    Route::get('/sas/reject/{id}', 'Api\SASController@reject')->name('sas.reject');
    Route::get('/sas/getAvailableStaff/{date_start}/{date_end}', 'Api\SASController@getAvailableStaff')->name('sas.getAvailableStaff');
    Route::post('/sas/addNewTask', 'Api\SASController@addNewTask')->name('sas.addNewTask');
    Route::resource('sas', 'Api\SASController');
    //EBS
    Route::post('/ebs/startBooking/{id}', 'Api\EBSController@startBooking')->name('ebs.startBooking');
    Route::post('/ebs/updateProgress/{id}', 'Api\EBSController@updateProgress')->name('ebs.updateProgress');
    Route::post('/ebs/endBooking/{id}', 'Api\EBSController@endBooking')->name('ebs.endBooking');
    Route::resource('ebs', 'Api\EBSController');
    //TBS
    Route::post('/tbs/startBooking/{id}', 'Api\TBSController@startBooking')->name('tbs.startBooking');
    Route::post('/tbs/updateProgress/{id}', 'Api\TBSController@updateProgress')->name('tbs.updateProgress');
    Route::post('/tbs/endBooking/{id}', 'Api\TBSController@endBooking')->name('tbs.endBooking');
    Route::resource('tbs', 'Api\TBSController');
    //MSS
    Route::post('/mss/endMaintenance/{id}', 'Api\MSSController@endMaintenance')->name('mss.endMaintenance');
    Route::post('/mss/updateProgress/{id}', 'Api\MSSController@updateProgress')->name('mss.updateProgress');
    Route::post('/mss/startMaintenance/{id}', 'Api\MSSController@startMaintenance')->name('mss.startMaintenance');
    Route::get('/mss/acknowledge/{id}', 'Api\MSSController@acknowledge')->name('mss.acknowledge');
    Route::get('/mss/getAvailableStaff/{date_start}/{date_end}', 'Api\MSSController@getAvailableStaff')->name('mss.getAvailableStaff');
    Route::post('/mss/addNewMaintenance', 'Api\MSSController@addNewMaintenance')->name('mss.addNewMaintenance');
    Route::resource('mss', 'Api\MSSController');
    //TMS
    Route::get('/tms/taskVerifyManager/{id_tms}', 'Api\TMSController@taskVerifyManager')->name('tms.taskVerifyManager');
    Route::get('/tms/taskVerifyClerk/{id_tms}', 'Api\TMSController@taskVerifyClerk')->name('tms.taskVerifyClerk');
    Route::post('/tms/taskCompletion/{id_tms}', 'Api\TMSController@taskCompletion')->name('tms.taskCompletion');
    Route::post('/tms/startVisit/{id_tms}', 'Api\TMSController@startVisit')->name('tms.startVisit');
    Route::get('/tms/acknowledge/{id_tms}', 'Api\TMSController@acknowledge')->name('tms.acknowledge');
    Route::post('/tms/addNewSession/{id_tms}', 'Api\TMSController@addNewSession')->name('tms.addNewSession');
    Route::post('/tms/addNewInquiry', 'Api\TMSController@addNewInquiry')->name('tms.addNewInquiry');
    Route::resource('tms', 'Api\TMSController');

});

