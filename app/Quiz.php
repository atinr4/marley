<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Quiz extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'quiz';

    public static function check_track_exists($id)
    {
        $result = Quiz::where('music_api_id',$id)->count();
        return $result;
    }

    public static function get_track_details($track_id)
    {
		$result = Quiz::where('music_api_id',$track_id)->count();
        return $result;
    }

    public static function insert_question($id, $question_title, $correct_answer, $option_list)
    {
        $quiz= new Quiz;
        $quiz->music_api_id = $id;
        $quiz->question = $question_title;
        $quiz->option = $option_list;
        $quiz->correct_answer = $correct_answer;
        $quiz->save();
        return true;
    }
}
