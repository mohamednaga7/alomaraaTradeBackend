<?php

namespace App\Http\Controllers;

use App\HomeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    //
    public function index() {
        try {
            $images = HomeImage::all();
            return response()->json($images, 200);
        } catch (\Exception $e) {
            return response()->json(['message'=>'error retrieving Home Images', 'error' => $e->getMessage()], 400);
        }
    }


    function storeImage(Request $request) {
        try {
            date_default_timezone_set('Africa/Cairo');
            $date = date('m/d/Y h:i:s a', time());
            $filename = 'homeImage-'.str_slug($date).'.'.$request->file('image')->getClientOriginalExtension();
            $fileUrl = env('APP_URL').'/assets/images/homeImages/'.$filename;

            if ($path = Storage::disk('public')->putFileAs('images/homeImages', $request->file('image'), $filename)) {
                $image = new HomeImage(['name'=>$filename, 'url'=>$fileUrl]);
                if ($image->save()) {
                    return response()->json($image, 200);
                } else {
                    return response()->json(['message'=>'error adding image to database'], 400);
                }
            } else {
                return response()->json(['message'=>'error uploading image to server'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message'=>'error storing image', 'error' => $e->getMessage()], 400);
        }
    }


    function deleteImage($name) {
        try {
            $image = HomeImage::where('name', $name)->first();
            Storage::disk('public')->delete('images/homeImages/'.$image->name);
            $image->delete();
            return response()->json(['message'=>'successfully deleted the brand'], 200);
        } catch (\Exception $e) {
            return response()->json(['message'=>'error deleting brand from database', 'error'=> $e->getMessage()], 400);
        }
    }
}
