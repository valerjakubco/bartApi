<?php

namespace App\Http\Controllers;

use App\Http\Services\ErrService;
use App\Http\Services\GalleryService;
use Illuminate\Http\Response;
//use http\Env\Response;
use Illuminate\Http\Request;
use function Illuminate\Events\queueable;

class GalleryController
{
    private static GalleryService $galleryService;

    public function __construct(){
        self::$galleryService = new GalleryService();
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $galleries = self::$galleryService->listGalleries();
        } catch (\Exception $exception){
            return ErrService::class->errResponse($exception);
        }

        return response()->json([
            'galleries' => $galleries
        ],200);
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
            return ErrService::class->errResponse($exception);
        }

        return response()->json([
            'message' => 'Gallery successfully deleted'
        ], 200);
    }



}


