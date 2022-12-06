<?php

namespace App\Http\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic;

class ImageService


{
//    private static GalleryService $galleryService;
    private static ErrService $errService;

    public function __construct()
    {
        if (!defined('GAL_PATH')) define('GAL_PATH', 'app/galleries/');
//        self::$galleryService = new GalleryService();
        self::$errService = new ErrService();
    }

    public function getTitleImage($gallery) {
        $path = $gallery->path;
        $fullPath = storage_path(GAL_PATH . $path);
        try {
            $files = File::allFiles($fullPath);
        } catch (\Exception $exception) {
            return null;
        }

        if (isset($files) && isset($files[0])) {
            return $this->handleImageObject($path, $files[0]);
        }
        return null;
    }

    public function handleImageObject($path, $file) {
        $image = new \stdClass();
        $image->path = $file->getFilename();
        $image->fullpath = $path . '/' . $file->getFilename();
        $image->name = $file->getFilenameWithoutExtension();
        $image->modified = date("Y-m-d\Th:i:s",filemtime($file));
        return $image;
    }

    public function listImages($path, $sorting): \stdClass
    {
        if($sorting === 'asc'){ $sorting = 3; } elseif ($sorting === 'desc'){ $sorting =  4;} else {$sorting = 3;}
        $imagesArr = [];
        $path = substr(str_replace('gallery/', '', $path), 1);
        $path = trim($path, '/');
        $fullPath = storage_path(GAL_PATH . $path);


        try {
            $images = File::allFiles($fullPath);
        } catch (\Exception $exception) {
            throw new \Exception("Gallery does not exists", 404);
        }
        $gallery = new \stdClass();
        $gallery->path = $path;
        $gallery->name = rawurldecode($path);


        foreach ($images as $file) {
            $imagesArr[] = $this->handleImageObject($path, $file);
        }

        $out = new \stdClass();
        $out->gallery = $gallery;
        $times = array_column($imagesArr, 'modified');
        array_multisort($times, $sorting, $imagesArr);
        $out->images = $imagesArr;
        return $out;
    }




    public function uploadImage($file, $path): array
    {

        if (!$file || !$file->isValid()) {
            throw new \Exception("File is not valid", 500);
        }


        $path = str_replace('/gallery/', '', $path);

        if (!file_exists(storage_path(GAL_PATH . trim($path, '/') . '/'))) {
            throw new \Exception("Gallery not found.", 404);
        }
        try {
            $filepath = storage_path(GAL_PATH . $path);


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
            'modified' => Carbon::now()->addHour(1)->format("Y-m-d\TH:i:s")
        ];

        return $uploaded;
    }


    public function deleteImage($gallery, $image)
    {
        try {
            $file =  storage_path() . '/' . GAL_PATH . $gallery . '/' . $image;

            self::validateImageDel($file);
            File::delete($file);


        } catch (\Exception $exception){
            return self::$errService->errResponse($exception->getMessage(), $exception->getCode());
        }
        return response()->json([
            'message' => 'Gallery/photo was deleted'
        ], 200);

    }
    public function validateImageDel($image){
        if(!is_file($image)){
            throw new \Exception("Gallery/photo does not exists", 404);
        }
    }

    public function showImage($w, $h, $gallery, $image, $extension): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Exception
    {
        $path = storage_path(GAL_PATH . "${gallery}");
        $image = $image . '.' . $extension;
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

        foreach ($images as $img) {

            if (basename($img) == $image) {


                try {

                    $img = Image::make($img->getRealPath());



                    if ($w == 0 && $h != 0) {
                        $img->resize(null, $h, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($w != 0 && $h == 0) {
                        $img->resize($w, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($w != 0 && $h != 0) {
                        $img->fit($w, $h);
                    }
                } catch (\Exception $exception) {

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
