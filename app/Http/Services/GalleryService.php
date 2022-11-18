<?php

namespace App\Http\Services;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic;


class GalleryService
{

    public function listGalleries(): \Exception|array
    {
        $galleriesArr = [];
        try {
            $path = storage_path('app/galleries');

        } catch (\Exception $e) {
            return $e;
        }

        $files = File::allFiles($path);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'json') {
                $json = json_decode(file_get_contents($file), true);
                $galleriesArr[] = $json;
            }
        }


        return $galleriesArr;
    }

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
        $imagesArr[] = [
            "gallery" => [
                'path' => $path,
                'name' => $path
            ]
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

        return $imagesArr;
    }


    public function addGallery($name): \stdClass
    {
        $gallery = new \stdClass();
        $gallery->name = $name;
        $gallery->path = rawurlencode($name);
        $tmp = str_replace(' ', '_', $name);
        try {
            mkdir(storage_path("app/galleries/$tmp/images"), 0777, true);
            file_put_contents(storage_path("app/galleries/$tmp/{$gallery->path}.json"), json_encode($gallery));
        } catch (\Exception $exception) {
            throw new \Exception('Unknown Error', 500);
        }

        return $gallery;
    }


    public function deleteGallery($path): void
    {
        $path = str_replace('gallery/', '', $path);
        $file = storage_path('app/galleries' . $path);
        File::deleteDirectory($file);
    }


    public function validateGalleryDel($path): void
    {
        $path = substr(str_replace('gallery/', '', $path), 1);
        //echo $path;

        $file = storage_path('app/galleries/' . $path);

        try {
            $galleries = $this->listGalleries();
        } catch (\Exception $exception) {
            throw new \Exception("Unknown Error", 500);
        }

        if (!is_dir(storage_path('app/galleries/' . $path))) {
            throw new \Exception("Gallery/photo does not exists", 404);
        }

    }

    public function validateGallery($name): void
    {

        if ($name) {

            $name = str_replace(' ', '_', $name);

            try {
                $galleries = $this->listGalleries();
            } catch (\Exception $exception) {
                throw new \Exception('Unknown Error', 500);
            }

            foreach ($galleries as $gallery) {
                if ($gallery["name"] == $name) {
                    throw new \Exception("Gallery with this name already exists", 409);
                }
            }

        } else {
            throw new \Exception('Bad JSON object: u\'name\' is a required property', 400);
        }
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
