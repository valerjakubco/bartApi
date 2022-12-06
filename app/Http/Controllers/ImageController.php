<?php

namespace App\Http\Controllers;
use App\Http\Services\ImageService;
use App\Http\Services\ErrService;
use FastRoute\Route;
use Illuminate\Http\Request;

class ImageController
{

    private static ImageService $imageService;
    private static ErrService $errService;


    public function __construct(){
        self::$imageService = new ImageService();
        self::$errService = new ErrService();

    }

    public function listImg(Request $request): \Illuminate\Http\JsonResponse|\Exception
    {
        $sorting = $request->get('sort');

        try {
            $path = $request->getPathInfo();
            $images = self::$imageService->listImages($path, $sorting);
        } catch (\Exception $exception){
            return self::$errService->errResponse($exception->getMessage(), $exception->getCode());

        }

        return response()->json($images);
    }




    public function addImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $path = $request->getRequestUri();
        $file = $request->file('image');



        try {

            $uploaded = self::$imageService->uploadImage($file, $path);
        }
        catch (\Exception $exception) {
            return self::$errService->errResponse($exception->getMessage(), $exception->getCode());

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
            return self::$errService->errResponse($exception->getMessage(), $exception->getCode());

        }
        return $preview;

    }

    public function delImage($gallery, $image, $extension): \Illuminate\Http\JsonResponse
    {
        try{
            $image = $image . '.' . $extension;
            $deleted = self::$imageService->deleteImage($gallery, $image);
        } catch (\Exception $exception){
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage()
                ]
            ], $exception->getCode());
        }
        return $deleted;
    }

}
