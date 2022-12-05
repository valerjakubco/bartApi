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
    public function __construct()
    {
        if (!defined('GAL_PATH')) define('GAL_PATH', 'app/galleries/');
    }

    public function listGalleries(): \Exception|array
    {
        $galleries = [];
        try {
            $path = storage_path('app/galleries/');
            $files = scandir($path);
            unset($files[0], $files[1]);
        } catch (\Exception $e) {
            throw new \Exception("Unknown error", 500);
        }
        foreach ($files as $file) {
            $gallery = new \stdClass();
            $gallery->path = $file;
            $gallery->name = rawurldecode($file);
            $galleries[] = $gallery;
        }

        if (count($galleries) > 0) {
            try {
                $imageService = new ImageService();
                //pridanie prveho obrazku ku galerii
                foreach ($galleries as $gallery) {
                    $image = $imageService->getTitleImage($gallery);
                    if ($image) {
                        $gallery->image = $image;
                    }
                }
            } catch (\Exception $exception) {
                ;
            }
        }
        return $galleries;
    }



    public function addGallery($name): \stdClass
    {
        $gallery = new \stdClass();
        $gallery->name = $name;
        $gallery->path = rawurlencode($name);
        $tmp = trim($gallery->path, '/');

        try {
            mkdir(storage_path("app/galleries/$tmp"), 0777, true);
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

        if (!is_dir(storage_path(GAL_PATH . $path))) {
            throw new \Exception("Gallery/photo does not exists", 404);
        }

    }

    public function validateGallery($name): void
    {

        if ($name) {

            try {
                $galleries = $this->listGalleries();
            } catch (\Exception $exception) {
                throw new \Exception('Unknown Error', 500);
            }
            $name = rawurlencode($name);
            if(is_dir(storage_path(GAL_PATH . "$name"))){
                throw new \Exception("Gallery with this name already exists", 409);
            }

        } else {
            throw new \Exception('Bad JSON object: u\'name\' is a required property', 400);
        }
    }





}
