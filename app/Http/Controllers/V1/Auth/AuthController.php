<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        // exempt user registration from having to login
        $this->middleware('jwt.verify')->except(['register', 'login']);
    }

    /**
     * Returns user if successfully created
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {

        // create validator for user object
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // check if validator fails
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'message' => $validator->messages()->first(),
                    'status' => 'Fail'
                ]
            ], 422);
        }

        // get registration data
        $user_input = $request->all();

        // create new user
        $user = new User();
        $user->username = $user_input['username'];
        $user->email = $user_input['email'];
        $user->password = bcrypt($user_input['password']);
        $user->save();

        return response()->json([
            'data' => $user,
            'message' => 'Successfully registered user.',
            'status' => 'Success'
        ], 201);
    }


    /**
     * Logs user in and provides an access token
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'username' => 'required',
            'password' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Request is validated
        //Create token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    /**
     * Logs user out
     * @return JsonResponse
     */
    public function logout($request)
    {
        // logout user and blacklist token forever
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me($request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user], 200);
    }
}
