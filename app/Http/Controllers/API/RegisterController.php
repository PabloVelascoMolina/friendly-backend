<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Avatar;
use Storage;
use DB;
use App\Models\Posts;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ], [
            'email.unique' => 'This unique'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
        $success['email'] =  $user->email;
        $success['id'] =  $user->id;
        $success['avatar'] =  $user->avatar;
        $success['ip'] =  $this->getRealIP();

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->name;
            $success['email'] =  $user->email;
            $success['id'] =  $user->id;
            $success['avatar'] =  $user->avatar;
            $success['ip'] =  $this->getRealIP();

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }

    public function getRealIP()
    {

        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }


    public function user(Request $request) {
        $user = Auth::user();
        return response()->json(compact('user'), 200);
    }

    public function RandomUsers() {
        $user = User::all();
        $numberOfRows = 9;
        $randRows = $user->shuffle()->slice(0, $numberOfRows);
        return response()->json($randRows, 200);
    }

    public function Profile($id) {
        $user = User::find($id);
        return response()->json($user, 200);
    }

    public function UploadPhoto(Request $request) {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $size = $file->getSize();

            $picture = date('His') . '-' . $filename;
            $file->move(public_path('img'), $picture);
            $url = $_ENV['APP_URL'] . '/img/' . $picture;

            $user = Auth::user();

            User::where('email', $user->email)->update(['avatar' => $url]);

            return response()->json(['url' => $url], 200);

        } else {
            return response()->json(['message' => 'No se ha seleccionado ninguna imagen']);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function PostById($id)
    {
        $posts = DB::table('posts')->where('user_id', $id)->orderBy('id', 'DESC')->get();
        if ($posts->isEmpty()) {
            return response()->json(['error' => 'Este usuario aún no ha publicado nada.']);
        }
        return response()->json($posts, 200);
    }

    public function Photos($id) {
        $photos = DB::table('posts')->select('id', 'image')->where('user_id', $id)->orderBy('id', 'DESC')->get();
        if ($photos->isEmpty()) {
            return response()->json(['error' => 'Este usuario aún no tiene fotos.']);
        }

        return response()->json(['data' => $photos], 200);
    }
}
