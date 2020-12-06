<?php

namespace App\Http\Controllers\API\Likes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Posts;
use App\Models\Likes;
use App\Models\User;
use App\Http\Controllers\API\BaseController as BaseController;
use DB;

class LikesController extends BaseController
{
    public function GenerateLike($id) {
        $post = Posts::find($id);
        $user = Auth::user();

        if ($post !== null) {

            $likes = Likes::firstOrCreate(['post_id' => $post->id, 'user_id' => $user->id]);
            if ($likes->wasRecentlyCreated) {
                return $this->sendResponse($likes, 'Like successfully.');
            } else {
                return $this->sendResponse(null, 'Failed like post.');
            }

        } else {
            return $this->sendResponse(null, 'Failed like post.');
        }

    }

    public function DeleteLike($id) {

        $post = Posts::find($id);
        $user = Auth::user();

        if ($post !== null) {

            $likes = DB::table('posts_likes')->where('post_id', $post->id)->where('user_id', $user->id)->delete();
            return $this->sendResponse($likes, 'Like successfully.');

        } else {
            return $this->sendResponse(null, 'Failed like post.');
        }

    }

    public function Counter($id) {
        $post = Posts::find($id);
        if ($post !== null) {

            $likes = DB::table('posts_likes')->where('post_id', $post->id)->where('liked', 1)->count();
            $arr = [
                'likes' => $likes,
                'post_id' => $post->id
            ];
            return $this->sendResponse($arr, 'Like successfully.');

        } else {
            return $this->sendResponse(null, 'Failed like post.');
        }

    }

    public function DetectLikedPost($id) {
        $user = Auth::user();
        $post = Posts::find($id);
        if ($post !== null) {

            $likes = DB::table('posts_likes')->where('post_id', $post->id)->where('user_id', $user->id)->where('liked', 1)->first();

            if ($likes !== null) {
                $arr = [
                    'liked' => $likes->liked,
                    'post_id' => $post->id
                ];
                return $this->sendResponse($arr, 'Like successfully.');
            } else {
                return $this->sendResponse(null, 'Failed like post.');
            }

        } else {
            return $this->sendResponse(null, 'Failed like post.');
        }

    }
}
