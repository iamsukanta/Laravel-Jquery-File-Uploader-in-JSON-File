<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ImageUploadController extends Controller
{
    public function index(Request $request)
    {
        return view('image');
    }

    public function uploadImage(Request $request) {
        $file = $request->file('image');
        $id = rand(1, 1000000);
        $fileName = $id.'.'.$file->getClientOriginalExtension();

        $file->move(public_path('uploads'), $fileName);

        $title = $request->image_title;
        $time = Carbon::now();
        $created_at = $time->toDateTimeString();
        $id = rand(1, 1000000);

        $jsonString = file_get_contents('data/imagedata.json');
        $data = json_decode($jsonString, true);
        array_push($data, array('id' => $id, 'title' => $title, 'image' => $fileName, 'created_at' => $created_at));
        $newJsonString = json_encode($data);
        file_put_contents('data/imagedata.json', $newJsonString);
        return response()->json(["data" => $data], 200);


    }

    public function getImageData()
    {
        $jsonString = file_get_contents('data/imagedata.json');
        $data = json_decode($jsonString, true);
        usort($data, function ($a, $b) {
            return (strtotime($a['created_at']) < strtotime($b['created_at']) -1);
        });
        return response()->json(["data" => $data], 200);
    }

    public function deleteImageData($imageId, $fileName)
    {
        $path = public_path() . "/uploads/".$fileName;
        unlink($path);
        $jsonString = file_get_contents('data/imagedata.json');
        $data = json_decode($jsonString, true);

        foreach ($data as $key => $value) {
            if (in_array($imageId, $value)) {
                unset($data[$key]);
            }
        }

        $newJsonString = json_encode($data);
        file_put_contents('data/imagedata.json', $newJsonString);
        return response()->json(["data" => $data], 200);
    }

}
