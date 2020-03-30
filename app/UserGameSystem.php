<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class UserGameSystem extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_game_system';

    public static $spotifyLoginType = 'Spotify';

    public static $appleLoginType = 'Apple';


    public static function checkUserGameProfile($userObject)
    {

        $userProfile = UserGameSystem::where('user_id', $userObject->id)->count();
        
        if($userProfile == 0) {
            $createProfile = new UserGameSystem();
            $createProfile->user_id = $userObject->id;
            $createProfile->name = $userObject->display_name;
            $createProfile->email = $userObject->email;
            $createProfile->login_using = self::$spotifyLoginType;
            $createProfile->total_xp = 0;
            $createProfile->correct_guess_streak_counter = 0;
            $createProfile->lives = 3;
            $createProfile->level = 1;
            $createProfile->save();
        }
        return true;
    }


    public static function getUserProfile($user_id)
    {
        $userProfile = UserGameSystem::where('user_id', $user_id)->first();
        return $userProfile;
    }

    
}
