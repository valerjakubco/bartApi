<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('/gallery', 'GalleryController@index');
$router->post('/gallery', 'GalleryController@addGall');

$router->get('/gallery/{path}', 'ImageController@listImg');
$router->delete('/gallery/{path}', 'GalleryController@delGall');
$router->post('/gallery/{path}', 'ImageController@addImage');

$router->delete('/gallery/{gallery}/{image}', 'ImageController@delImage');

$router->get('/images/{w}x{h}/{gallery}/{image}', 'ImageController@imgPreview');

