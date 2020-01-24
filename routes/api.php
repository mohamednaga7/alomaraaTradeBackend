<?php

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'brands'], function () {
    Route::get('/', 'BrandsController@getBrands');

    Route::get('/{slug}', 'BrandsController@getBrand');

    Route::group(['middleware' => 'jwt-auth'], function () {
        Route::post('/', 'BrandsController@storeBrand');

        Route::put('/{slug}', 'BrandsController@updateBrand');

        Route::delete('/{slug}', 'BrandsController@deleteBrand');
    });
});

Route::group(['prefix'=>'cars'], function () {
    Route::get('/', 'CarsController@getCars');

    Route::get('/{brand}/{model}', 'CarsController@getCar');

    Route::group(['middleware' => 'jwt-auth'], function () {
        Route::post('/', 'CarsController@storeCar');

        Route::put('/{brand}/{model}', 'CarsController@updateCar');

        Route::delete('/{brand}/{model}', 'CarsController@deleteCar');

        Route::delete('/images/{brand}/{model}/{name}', 'CarsController@deleteCarImage');
    });
});

Route::group(['prefix'=>'home-image'], function() {
    Route::get('/', 'HomeController@index');

    Route::post('/', 'HomeController@storeImage');

    Route::group(['middleware' => 'jwt-auth'], function () {

        Route::delete('/{name}', 'HomeController@deleteImage');
    });
});

Route::post('signupuser', 'UserController@signup');

Route::group([

    'middleware' => 'api',
    'prefix' => 'user/auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});

Route::post('/sendemail', function (Request $request) {
    try {
        Mail::to('mohamed.nagah95@gmail.com')->send(new ContactMail($request['name'], $request['email'], $request['message']));
        if (!Mail::failures() || Mail::failures() <1) {
            return response()->json(['message'=>'successfully sent the email'], 200);
        } else {
            return response()->json(['message'=>'error sending message'], 400);
        }
    } catch (Exception $e) {
        return response()->json(['message'=>$e->getMessage()], 400);
    }
});
