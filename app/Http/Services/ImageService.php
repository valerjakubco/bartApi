<?php

namespace App\Http\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic;

class ImageService
{


    public function listImages($path): array|string
    {

        $imagesArr = [];
        $path = substr(str_replace('gallery/', '', $path), 1);
        $path = trim($path, '/');
        $fullPath = storage_path("app/galleries/" . $path . "/images");

        try {
            $images = File::allFiles($fullPath);
        } catch (\Exception $exception) {
            throw new \Exception("Gallery does not exists", 404);
        }
        $gallery[] = [
            'path' => $path,
            'name' => $path
        ];
        foreach ($images as $file) {
            $image = new \stdClass();
            $image->name = $file->getFilename();
            $image->path = $path . '/images/' . $file->getFilename();

            $imagesArr[] = [
                'path' => $image->name,
                'fullpath' => $image->path,
                'name' => ucfirst(pathinfo($image->name, PATHINFO_FILENAME))
            ];
        }
        $gallery[] = $imagesArr;
        return $gallery;
    }




    public function uploadImage($file, $path): array
    {

        if (!$file || !$file->isValid()) {
            throw new \Exception("File is not valid");
        }
        $path = str_replace('/gallery/', '', $path);
        if (!file_exists(storage_path('app/galleries/' . trim($path, '/') . '/'))) {
            throw new \Exception("Gallery not found.", 404);
        }
        try {
            $filepath = storage_path('app/galleries/' . $path . '/images/');
        } catch (\Exception $exception) {
            throw new \Exception("Gallery not found", 404);
        }
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $file->clientExtension();


        $file->move($filepath, $filename);
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $uploaded = [];
        $uploaded[] = [
            'path' => $filename,
            'fullpath' => "${path}/${filename}",
            'name' => $name,
            'modified' => Carbon::now()->addHours(2)
        ];

        return $uploaded;
    }


    public function deleteImage($gallery, $image): \Illuminate\Http\JsonResponse
    {
        $path = storage_path("app/galleries/${gallery}/images/");
        $files = File::allFiles($path);
        foreach ($files as $file) {
            if (pathinfo(storage_path($file), PATHINFO_FILENAME) == $image) {
                if (!File::delete($file)) {
                    return response()->json([
                        'message' => 'Image deletion was not successful'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Image deletion was successful'
                    ], 200);
                }
            }

        }

        return response()->json([
            'message' => 'Image deletion was not successful'
        ], 200);

    }


    public function showImage($w, $h, $gallery, $image)
    {
        $path = storage_path("app/galleries/${gallery}/images");

        $images = File::allFiles($path);

        if (ctype_digit($w)) {
            $w = intval($w);
            if ($w < 0 || $w > 9000) {
                return response()->json([
                    "error" => [
                        "message" => "The photo preview can't be generated."
                    ]
                ], 500);
            }
        } else {
            return response()->json([
                "error" => [
                    "message" => "The photo preview can't be generated."
                ]
            ], 500);
        }

        if (ctype_digit($h)) {
            $h = intval($h);
            if ($h < 0 || $h > 9000) {
                return response()->json([
                    "error" => [
                        "message" => "The photo preview can't be generated."
                    ]
                ], 500);
            }
        } else {
            return response()->json([
                "error" => [
                    "message" => "The photo preview can't be generated."
                ]
            ], 500);
        }

        foreach ($images as $img){

            if (pathinfo(storage_path($img), PATHINFO_FILENAME) == pathinfo(storage_path($image), PATHINFO_FILENAME)){
                $img = Image::make($img->getRealPath());
                try {
                    if($w == 0 && $h != 0){
                        $img->resize(null, $h, function ($constraint){
                            $constraint->aspectRatio();
                        });
                    } elseif ($w != 0 && $h == 0){
                        $img->resize($w, null, function ($constraint){
                            $constraint->aspectRatio();
                        });
                    } elseif ($w != 0 && $h != 0){
                        $img->fit($w, $h);
                    }
                } catch (\Exception $exception){
                    return response()->json([
                        'error' => [
                            'message' => "The photo preview can't be generated."
                        ]
                    ], 500);
                }

                return Response::make(ImageManagerStatic::make($img)->encode('jpg'), 200, ['Content-Type' => 'image/jpeg']);

            } else {
                return Response::json([
                    "error" => [
                        "message" => "Photo not found"
                    ]
                ], 404);
            }
        }

        return Response::json([
            "error" => [
                "message" => "Unknown error"
            ]
        ], 500);

    }
}
