<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserGameSystem;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        try {

            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');

            
            $user->password = app('hash')->make($plainPassword);

            $user->save();
            if ($request->has('profile_data'))
            {
                $user->profile_data = $request->input('profile_data');
            }
            $userSystemId = $this->createProfileForAppleUser($user);
            $getUserProfile = UserGameSystem::getUserProfile($userSystemId);
            //return successful response
            return response()->json(['user' => $getUserProfile, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }

    }

    public function createProfileForAppleUser($userObject)
    {
        $checkUserInSystem = UserGameSystem::where('email', $userObject->email)->where('login_using','Apple')->count();
        if($checkUserInSystem == 0) {
            $createProfile = new UserGameSystem();
            $createProfile->user_id = md5($userObject->email);
            $createProfile->name = $userObject->name;
            $createProfile->email = $userObject->email;
            $createProfile->login_using = UserGameSystem::$appleLoginType;
            $createProfile->total_xp = isset($userObject->profile_data)?$userObject->profile_data['total_xp'] : 0;
            $createProfile->correct_guess_streak_counter = isset($userObject->profile_data)?$userObject->profile_data['correct_guess_streak_counter'] : 0;
            $createProfile->lives = isset($userObject->profile_data)?$userObject->profile_data['lives'] : 3;
            $createProfile->level = 1;
            $createProfile->save();

            return $createProfile->user_id;
        }

        return response()->json(['message' => 'User Registration Failed! Email already exsits'], 409);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
          //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


}