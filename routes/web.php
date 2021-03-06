<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('login', function () {
    return view('login');
})->name('login');;




Route::get('/', function () {
    if(auth()->user())
    {
        return redirect('dashboard2');
    }
    else
    {
        return view('login');
    }
});

Route::post('login', 'Auth\LoginController@login');


Route::middleware('auth')->group(function () {

            Route::get('logout', 'Auth\LoginController@logout');

            Route::get('dashboard2', 'DashboardController@dashboard');
            Route::post('searchDashboard', 'DashboardController@searchDashboard');

            Route::get('staff2', 'DashboardController@staff');
            Route::get('sasstaffassign/{id}', 'DashboardController@showSAS');
            Route::post('searchSAS', 'DashboardController@searchSAS');

            Route::get('equipment2', 'DashboardController@equipment');
            Route::get('ebs/{id}', 'DashboardController@showEBS');    
            Route::post('searchEBS', 'DashboardController@searchEBS');

            Route::get('transport2', 'DashboardController@transport');
            Route::get('tbs/{id}', 'DashboardController@showTBS');  
            Route::post('searchTBS', 'DashboardController@searchTBS'); 

            Route::get('maintenance2', 'DashboardController@maintenance');
            Route::get('mss/{id}', 'DashboardController@showMSS');   
            Route::post('searchMSS', 'DashboardController@searchMSS'); 

            Route::get('tender2', 'DashboardController@tender');
            Route::get('tms/{id}', 'DashboardController@showTMS');   
            Route::post('searchTMS', 'DashboardController@searchTMS'); 

    

            Route::get('/dashboard', function () {
                return view('dashboard');
            });

            Route::get('/staff', function () {
                return view('staffAssignmentSystem');
            });

            Route::get('/equipment', function () {
                return view('equipment');
            });

            Route::get('/transport', function () {
                return view('transport');
            });

            Route::get('/maintenance', function () {
                return view('maintenance');
            });

            Route::get('/tender', function () {
                return view('tender');
            });

});
