<?php

use App\Brand;
use App\Car;
use App\Feature;
use App\Image;
use App\HomeImage;

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

// Route::get('/backup-data', function () {
//     $cars = Car::all()->toArray();
//     $newCars = [];
//     foreach ($cars as $car) {
//         $newCars[] = array_merge($car, ['categoryId' => 1]);
//     }
//     $newCarsJson = json_encode($newCars);
//     file_put_contents('cars.json', $newCarsJson);
    
//     $brands = Brand::all()->toArray();
//     $brandsJson = json_encode($brands);
//     file_put_contents('brands.json', $brandsJson);
    
//     $images = Image::all()->toArray();
//     $imagesJson = json_encode($images);
//     file_put_contents('images.json', $imagesJson);
    
//     $homeImages = HomeImage::all()->toArray();
//     $homeImagesJson = json_encode($homeImages);
//     file_put_contents('homeImages.json', $homeImagesJson);
    
//     $features = Feature::all()->toArray();
//     $featuresJson = json_encode($features);
//     file_put_contents('features.json', $featuresJson); 
    
//     return response()->json(['message' => 'successfully stored all data'], 200);
// });



//  A function for reading the backup json files
 Route::get('/brands', function () {
     $brandsFile = file_get_contents('brands.json');
     $brandsJson = json_decode($brandsFile);
     foreach($brandsJson as $fileBrand) {
         $brand = new Brand((array) $fileBrand);
         $brand->save();
     }
     return response()->json(['brands' => $brandsJson], 200);
 });


Route::get('/read_backup', function () {
    $carsFile = file_get_contents('cars.json');
    $carsJson = json_decode($carsFile);
    foreach($carsJson as $fileCar) {
        $car = new Car((array) $fileCar);
        $car->save();
    }

    $brandsFile = file_get_contents('features.json');
    $brandsJson = json_decode($brandsFile);
    foreach($brandsJson as $fileBrand) {
        $brand = new Feature((array) $fileBrand);
        $brand->save();
    }

    $brandsFile = file_get_contents('images.json');
    $brandsJson = json_decode($brandsFile);
    foreach($brandsJson as $fileBrand) {
        $brand = new Image((array) $fileBrand);
        $brand->save();
    }

    $brandsFile = file_get_contents('homeImages.json');
    $brandsJson = json_decode($brandsFile);
    foreach($brandsJson as $fileBrand) {
        $brand = new HomeImage((array) $fileBrand);
        $brand->save();
    }





    return response()->json(['message' => 'successful'], 200);
});

Route::get('/', function () {
    return view('welcome');
});
