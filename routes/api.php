<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\PostsController;
use App\Http\Controllers\API\Likes\LikesController;
use App\Http\Resources\Posts;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::get('randomusers', [RegisterController::class, 'RandomUsers']);
Route::get('profile/{id}', [RegisterController::class, 'Profile']);

Route::get('posts-by/{id}', [RegisterController::class, 'PostById']);
Route::get('photos/{id}', [RegisterController::class, 'Photos']);

Route::middleware('auth:api')->group(function () {
    /* Resources */

    Route::resource('products', ProductController::class);
    Route::resource('posts', PostsController::class);

    /* Controllers GET */
    Route::get('user', [RegisterController::class, 'user']);
    Route::get('logout', [RegisterController::class, 'logout']);

    /* Controllers POST */
    Route::post('upload-photo', [RegisterController::class, 'UploadPhoto']);
    Route::post('post-like-add/{id}', [LikesController::class, 'GenerateLike']);
    Route::post('post-like-remove/{id}', [LikesController::class, 'DeleteLike']);
    Route::post('post-like-count/{id}', [LikesController::class, 'Counter']);
    Route::post('post-like-view/{id}', [LikesController::class, 'DetectLikedPost']);
});

Route::get('storage/{filename}', function ($filename) {
    $path = storage_path('public/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});
