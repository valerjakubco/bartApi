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





}
