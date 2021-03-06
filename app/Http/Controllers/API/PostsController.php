<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\Posts;
use Illuminate\Http\Request;
use App\Http\Resources\Posts as PostsResource;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Likes;
use DB;

class PostsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

		$posts = Posts::all()->sortByDesc("id");

		$collection = PostsResource::collection($posts);

		return $this->sendResponse($collection, 'Posts retrieved successfully.');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'description' => 'required',
            'category' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $post = Posts::create([
            'description' => $input['description'],
            'category' => $input['category'],
            'user_id' => $input['user_id'],
            'image' => $input['image'] == "null" ? null : $input['image']
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $size = $file->getSize();

            $picture = date('His') . '-' . $filename;
            $file->move(public_path('img'), $picture);
            $url = $_ENV['APP_URL'] . '/img/' . $picture;

            Posts::where('id', $post->id)->update(['image' => $url]);

            return $this->sendResponse($url, 'Post created successfully.');
        }

        return $this->sendResponse(new PostsResource($post), 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Posts::find($id);

        if (is_null($post)) {
            return $this->sendError('Post not found.');
        }

        return $this->sendResponse(new PostResource($post), 'Post retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $posts = Posts::find($id);
        $input = $request->all();

        $validator = Validator::make($input, [
            'description' => 'required',
            'category' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $posts->description = $input['description'];
        $posts->category = $input['category'];
        $posts->image = $input['image'];
        $posts->user_id = $input['user_id'];
        $posts->update($request->all());

        return $this->sendResponse(new PostsResource($posts), 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $posts = Posts::find($id);
        Likes::where('post_id', '=', $id)->delete();

        if (is_null($posts)) {
            return $this->sendError('Post not found.');
        }

        $posts->delete($id);

        return $this->sendResponse(new PostsResource($posts), 'Post deleted successfully.');
    }
}
