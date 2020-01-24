<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandsController extends Controller
{
    //

    function getBrands(Request $request) {
        try {
            $brands = Brand::all();
            $skip = $request->has('skip') ? $request->get('skip') : 0;
            $limit = $request->has('limit') ? $request->get('limit') : 20;
            $brands = Brand::offset($skip)->take($limit);
            foreach ($brands as $brand) {
                $brand->images->all();
            }
            return response()->json($brands, 200);
        } catch (\Exception $e) {
            return response()->json(['message'=>'error retrieving brands', 'error' => $e->getMessage()], 400);
        }
    }

    function storeBrand(Request $request) {
        $request['slug'] = str_slug($request['name'],'-');
        try {
            $brand = new Brand($request->all());
            $filename = 'brand-'.$brand->slug.'.'.$request->file('image')->getClientOriginalExtension();
            $fileUrl = env('APP_URL').'/assets/images/brands/'.$filename;
            if ($path = Storage::disk('public')->putFileAs('images/brands', $request->file('image'), $filename)) {
                $image = new Image(['name'=>$filename, 'url'=>$fileUrl]);
                if ($image->save()) {
                    if ($brand->save()) {
                        $brand->images()->save($image);
                        $brand->images->all();
                        return response()->json($brand, 200);
                    } else {
                        Storage::disk('public')->delete($filename);
                        $image->delete();
                        return response()->json(['message'=>'error storing brand'], 400);
                    }
                } else {
                    return response()->json(['message'=>'error adding image to database'], 400);
                }
            } else {
                return response()->json(['message'=>'error uploading image to server'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message'=>'error storing brand', 'error' => $e->getMessage()], 400);
        }
    }

    function getBrand($slug) {
        try {
            $brand = Brand::where('slug', $slug)->first();
            return response()->json($brand, 200);
        } catch (\Exception $e) {
            return response()->json(['message'=>'error getting brand', 'error' => $e->getMessage()], 400);
        }
    }

    function updateBrand($slug, Request $request) {
        $request['slug'] = str_slug($request['name'],'-');
        try {
            $brand = Brand::where('slug', $slug)->first();
            if ($request->hasFile('image')) {
                $image = $brand->images->get(0);
                Storage::disk('public')->delete('images/brands/'.$image->name);

                $filename = 'brand-'.$request['slug'].'.'.$request->file('image')->getClientOriginalExtension();
                $fileUrl = env('APP_URL').'/assets/images/brands/'.$filename;
                if ($path = Storage::disk('public')->putFileAs('images/brands', $request->file('image'), $filename)) {
                    if ($image->update(['name'=>$filename, 'url'=>$fileUrl])) {
                        if ($brand->update(['name'=>$request['name'], 'nameArabic'=>$request['nameArabic'], 'slug'=>$request['slug']])) {
                            $brand->images->all();
                            return response()->json($brand, 200);
                        } else {
                            return response()->json(['message'=>'error updating brand but image uploaded successfully'], 400);
                        }
                    } else {
                        return response()->json(['message'=>'error updating image in the database database'], 400);
                    }
                } else {
                    return response()->json(['message'=>'error uploading image to server'], 400);
                }
            } else {
                if ($brand->update($request->all())) {
                    $brand->images->all();
                    return response()->json($brand, 200);
                } else {
                    return response()->json(['message'=>'error updating brand'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message'=>'error updating brand, please try again later', 'error'=>$e->getMessage()], 400);
        }
    }

    function deleteBrand($slug) {
        try {
            $brand = Brand::where('slug', $slug)->first();
            $brandCars = $brand->cars();
            foreach ($brandCars as $car) {
                $car->delete();
            }
            $image = $brand->images->get(0);
            if ($brand->delete()) {
                Storage::disk('public')->delete('images/brands/'.$image->name);
                $image->delete();
                return response()->json(['message'=>'successfully deleted the brand'], 200);
            } else {
                return response()->json(['message'=>'error deleting brand from database'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message'=>'error deleting brand from database', 'error'=> $e->getMessage()], 400);
        }
    }
}
