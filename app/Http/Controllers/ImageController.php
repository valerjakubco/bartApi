<?php

namespace App\Http\Controllers;
use App\Http\Services\ImageService;
use App\Http\Services\ErrService;
use Illuminate\Http\Request;

class ImageController
{

    private static ImageService $imageService;

    public function __construct(){
        self::$imageService = new ImageService();
    }

    public function listImg(Request $request): \Illuminate\Http\JsonResponse|\Exception
    {
        try {
            $path = $request->getRequestUri();
            $images = self::$imageService->listImages($path);
        } catch (\Exception $exception){
            return ErrService::class->errResponse($exception);
        }

        return response()->json([
            'gallery' => $images
        ]);
    }




    public function addImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $path = $request->getRequestUri();
        $file = $request->file('image');
        try {
            $uploaded = self::$imageService->uploadImage($file, $path);
        }
        catch (\Exception $exception) {
            return ErrService::class->errResponse($exception);
        }
        return response()->json([
            'uploaded' => $uploaded
        ], 200);
    }


    public function imgPreview($w, $h, $gallery, $image): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        try {
            $preview = self::$imageService->showImage($w, $h, $gallery, $image);
        } catch (\Exception $exception){
            return ErrService::class->errResponse($exception);
        }
        return $preview;

    }

    public function delImage($gallery, $image): \Illuminate\Http\JsonResponse
    {
    try{
        $deleted = self::$imageService->deleteImage($gallery, $image);
    } catch (\Exception $exception){
        return ErrService::class->errResponse($exception);
    }
        return $deleted;
    }

}
