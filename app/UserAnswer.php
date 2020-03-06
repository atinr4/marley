<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class UserAnswer extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_answer_log';

    public static function insert_user_answer_log($user_id, $quiz_id, $answer_given, $result)
    {      
        $userAnswerLog= new UserAnswer;
        $userAnswerLog->userID = $user_id;
        $userAnswerLog->quiz_id = $quiz_id;
        $userAnswerLog->answer_given = $answer_given;
        $userAnswerLog->result = $result;
        $userAnswerLog->save();

        $getUserGameDetails = UserGameSystem::getUserProfile($user_id);
        if($result == 'correct') {
            $getUserGameDetails->total_xp = $getUserGameDetails->total_xp + 50;
            $getUserGameDetails->correct_guess_streak_counter = $getUserGameDetails->correct_guess_streak_counter + 1;

            if($getUserGameDetails->correct_guess_streak_counter >= 20 && $getUserGameDetails->correct_guess_streak_counter < 50) {
                $getUserGameDetails->total_xp = $getUserGameDetails->total_xp + 20;
            } else if($getUserGameDetails->correct_guess_streak_counter >= 50 && $getUserGameDetails->correct_guess_streak_counter < 100) {
                $getUserGameDetails->total_xp = $getUserGameDetails->total_xp + 50;
            } else if($getUserGameDetails->correct_guess_streak_counter >= 100) {
                $getUserGameDetails->total_xp = $getUserGameDetails->total_xp + 100;
            }
        } else {
            $getUserGameDetails->correct_guess_streak_counter = 0;
            $getUserGameDetails->lives = $getUserGameDetails->lives - 1;
        }

        $getUserGameDetails->level = ceil($getUserGameDetails->total_xp / 1000);
        $getUserGameDetails->save();
    }

    public static function getUserAnswerDetails($user_id)
    {
        $result = UserAnswer::where('userID', $user_id)->first()->toArray();
        return $result;
    }
}
