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
        $userAnswerLog->logDate = date("Y-m-d H:i:s");
        $userAnswerLog->save();
    }

    public static function getUserAnswerDetails($user_id)
    {
        $result = UserAnswer::where('userID', $user_id)->first()->toArray();
        return $result;
    }
}
