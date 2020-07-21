<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'ImageUploadController@index');

Route::get('/image/data', 'ImageUploadController@getImageData');
Route::post('/image/upload', 'ImageUploadController@uploadImage');
Route::get('/image/delete/{imageId}/{fileName}', 'ImageUploadController@deleteImageData');
