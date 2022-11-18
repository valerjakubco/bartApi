<?php

namespace App\Http\Controllers;

use App\Http\Services\GalleryService;
use http\Env\Response;
use Illuminate\Http\Request;
use function Illuminate\Events\queueable;

class GalleryController
{
    private static $galleryService;

    public function __construct(){
        self::$galleryService = new GalleryService();
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $galleries = self::$galleryService->listGalleries();
        } catch (\Exception $e){
            return response()->json([
                'galleries' => []
            ], 200);
        }

        return response()->json([
            'galleries' => $galleries
        ],200);
    }

    public function listImg(Request $request): \Illuminate\Http\JsonResponse|\Exception
    {
        try {
            $path = $request->getRequestUri();
            $images = self::$galleryService->listImages($path);
        } catch (\Exception $exception){
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage()
                ]
            ], $exception->getCode());
        }

        return response()->json([
            'images' => $images
        ]);
    }


    public function addGall(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $name = $request->input('name');
            self::$galleryService->validateGallery($name);
            $newGallery = self::$galleryService->addGallery($name);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage()
                ]
            ], $exception->getCode());
        }

        return response()->json($newGallery, 201);
    }


    public function delGall(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $path = $request->getRequestUri();
            self::$galleryService->validateGalleryDel($path);
            self::$galleryService->deleteGallery($path);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage()
                ]
            ], $exception->getCode());
        }

        return response()->json([
            'message' => 'Gallery successfuly deleted'
        ], 200);
    }


    public function addImage(Request $request)
    {
        $path = $request->getRequestUri();
        $file = $request->file('image');
        try {
            $uploaded = self::$galleryService->uploadImage($file, $path);
        }
         catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], $e->getCode());
        }
        return response()->json([
            'uploaded' => $uploaded
        ], 200);
    }



    public function imgPreview($w, $h, $gallery, $image){
        try {
           $preview = self::$galleryService->showImage($w, $h, $gallery, $image);
        } catch (\Exception $e){
            return response()->json([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], $e->getCode());
        }
         return $preview;

    }
}
