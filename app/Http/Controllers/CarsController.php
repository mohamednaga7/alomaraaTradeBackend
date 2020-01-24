<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Car;
use App\Feature;
use App\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class CarsController extends Controller
{
    //
    function getCars() {
        try {
            $cars = Car::all();
            foreach ($cars as $car) {
                $car->images->all();
                $car->features;
            }
            return response()->json($cars, 200);
        } catch (\Exception $e) {
            return response()->json(['message'=>'error retrieving cars', 'error' => $e->getMessage()], 400);
        }
    }

    function storeCar(Request $request) {
        $features = json_decode($request['features']);
        $request['slug'] = str_slug($request['model'], '-');
        try {
            $brand = Brand::where('slug',$request['brand'])->first();
            $car = new Car($request->all());
            $car->brand_id = $brand->id;
            $files = $request->allFiles();
            $storedImages = [];
            $allImagesStored = true;
            $count = 0;

            foreach ($files as $i=>$file) {
                $filename = 'car-'.$car->slug.'-image'.$count.'.'.$files[$i]->getClientOriginalExtension();
                $fileUrl = env('APP_URL').'/assets/images/cars/'.$request['brand'].'/'.$car->slug.'/'.$filename;
                if (Storage::disk('public')->putFileAs('images/cars/'.$request['brand'].'/'.$car->slug.'/', $files[$i], $filename)) {
                    $image = new Image(['name'=>$filename, 'url'=>$fileUrl]);
                    if ($image->save()) {
                        array_push($storedImages, $image);
                    } else {
                        $allImagesStored = false;
                    }
                }
                $count++;
            }

            if ($allImagesStored) {
                if ($car->save()) {
                    foreach ($storedImages as $storedImage) {
                        $car->images()->save($storedImage);
                    }
                    foreach ($features as $feature) {
                        $storedFeature = new Feature();
                        $storedFeature->car_id = $car->id;
                        $storedFeature->englishFeature = $feature->englishFeature;
                        $storedFeature->arabicFeature = $feature->arabicFeature;
                        $storedFeature->save();
                    }
                    $car->images->all();
                    $car->features;
                    return response()->json($car, 200);
                } else {
                    foreach ($storedImages as $storedImage) {
//                        'images/cars/'.$request['brand'].'/'.$car->slug.'/', $files[$i], $filename
                        Storage::disk('public')->delete('images/cars/'.$request['brand'].'/'.$car->slug.'/'.$storedImage->name);
                        $storedImage->delete();
                    }
                    return response()->json(['message'=>'error storing car'], 400);
                }
            } else {
                foreach ($storedImages as $storedImage) {
                    Storage::disk('public')->delete('images/cars/'.$request['brand'].'/'.$car->slug.'/'.$storedImage->name);
                    $storedImage->delete();
                }
                return response()->json(['message'=>'error uploading images'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message'=>'error storing car', 'error' => $e->getMessage()], 400);
        }
    }

    function updateCar(Request $request, $brand, $model) {
        $features = json_decode($request['features']);
        $request['slug'] = str_slug($request['model'], '-');
        try {
            $car = car::where('slug', $model)->first();
            $carFeatures = $car->features;
            $images = $car->images->all();

            $imtrial = [];

            if ($request->allFiles()) {
                if (count($request->allFiles()) > 0) {
                    $allImagesDeleted = true;
                    foreach ($images as $image) {
                        try {
                            Storage::disk('public')->delete('images/cars/'.$brand.'/'.$model.'/'.$image->name);
                            if (!$image->delete()) {
                                $allImagesDeleted = false;
                            }
                        } catch (Exception $e) {
                            return response()->json(['message'=>$e->getMessage()]);
                        }
                    }

                    if ($allImagesDeleted) {
                        $files = $request->allFiles();
                        $storedImages = [];
                        $allImagesStored = true;
                        $count = 0;

                        foreach ($files as $i=>$file) {
                            $filename = 'car-'.$car->slug.'-image'.$count.'.'.$files[$i]->getClientOriginalExtension();
                            $fileUrl = env('APP_URL').'/assets/images/cars/'.$request['brand'].'/'.$car->slug.'/'.$filename;
                            if (Storage::disk('public')->putFileAs('images/cars/'.$request['brand'].'/'.$car->slug.'/', $files[$i], $filename)) {
                                $image = new Image(['name'=>$filename, 'url'=>$fileUrl]);
                                if ($image->save()) {
                                    array_push($storedImages, $image);
                                } else {
                                    $allImagesStored = false;
                                }
                            }
                            $count++;
                        }

                        if ($allImagesStored) {
                            foreach ($storedImages as $storedImage) {
                                $car->images()->save($storedImage);
                            }
                        } else {
                            return response()->json(['message'=>'error uploading images'], 400);
                        }
                    } else {
                        return response()->json(['message'=>'error deleting old images'], 400);
                    }
                }
            }

            if ($car->update($request->all())) {
                if (count($features) >= count($carFeatures)) {
                    for ($i = 0 ; $i < count($features) ; $i++) {
                        $feature = $features[$i];
                        if ($i >= count($carFeatures)) {
                            $storedFeature = new Feature();
                            $storedFeature->car_id = $car->id;
                            $storedFeature->englishFeature = $feature->englishFeature;
                            $storedFeature->arabicFeature = $feature->arabicFeature;
                            $storedFeature->save();
                        } else {
                            $carFeatures[$i]->englishFeature = $features[$i]->englishFeature;
                            $carFeatures[$i]->arabicFeature = $features[$i]->arabicFeature;
                            if (!$carFeatures[$i]->save()) {
                                return response()->json(['message' => 'error updating features', 'feature' => $features[$i]], 400);
                            }
                        }
                    }
                } else {
                    for ($i = 0 ; $i < count($carFeatures) ; $i++) {
                        if ($i >= count($features)) {
                            $carFeatures[$i]->delete();
                        } else {
                            $carFeatures[$i]->englishFeature = $features[$i]->englishFeature;
                            $carFeatures[$i]->arabicFeature = $features[$i]->arabicFeature;
                            if (!$carFeatures[$i]->save()) {
                                return response()->json(['message' => 'error updating features', 'feature' => $features[i]], 400);
                            }
                        }
                    }
                }
            } else {
                return response()->json(['message'=>'error storing car'], 400);
            }
            $car->images->all();
            $car->features;
            return response()->json($car, 200);
        } catch (Exception $e) {
            return response()->json(['message'=>'error deleting car from database', 'error'=> $e->getMessage()], 400);
        }
    }

    function deleteCar(Request $request, $brand, $model) {
        try {
            $car = car::where('slug', $model)->first();
            $images = $car->images->all();
            $allImagesDeleted = true;
            if ($car->delete()) {
                foreach ($images as $image) {
                    if (Storage::disk('public')->delete('images/cars/'.$brand.'/'.$model.'/'.$image->name)) {
                        if (!$image->delete()) {
                            $allImagesDeleted = false;
                        }
                    } else {
                        $allImagesDeleted = false;
                    }
                }
                if ($allImagesDeleted) {
                    if (Storage::disk('public')->deleteDirectory('images/cars/'.$brand.'/'.$model)) {
                        return response()->json(['message' => 'successfully deleted the car'], 200);
                    } else {
                        return response()->json(['message'=>'error deleting images'], 400);
                    }
                } else {
                    return response()->json(['message'=>'error deleting images'], 400);
                }
            } else {
                return response()->json(['message'=>'error deleting car from database'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['message'=>'error deleting car from database', 'error'=> $e->getMessage()], 400);
        }
    }

    function deleteCarImage(Request $request, $brand, $model, $name) {
        try {
            $image = Image::where('name', $name);
            if (Storage::disk('public')->delete('images/cars/'.$brand.'/'.$model.'/'.$name)) {
                if ($image->delete()) {
                    return response()->json(['message' => 'image successfully deleted'], 200);
                } else {
                    return response()->json(['message' => 'error erasing image from database'], 400);
                }
            } else {
                return response()->json(['message' => 'error deleting image from server'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
