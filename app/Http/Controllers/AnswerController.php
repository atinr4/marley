<?php

namespace App\Http\Controllers;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\Request;

use App\Quiz;
use App\Spotify;
use App\QuizOptions;
use App\UserAnswer;
use App\UserGameSystem;

class AnswerController extends Controller
{

    public function submitAnswer(Request $request)
    {
        $user_id = $request->get('user_id');
        $user_answer = $request->get('answer');
        
        if($user_id !="" && $user_answer !=""){

            foreach ($user_answer as $answer){
                $get_track_details = Quiz::get_track_details($answer['question_id']);
                
                if (count($get_track_details) > 0) {
                    if ($answer['answer'] == $get_track_details['correct_answer']) {
                        $result = "correct";
                    } else {
                        $result = "incorrect";
                    }
                        
                    $answer_given = str_replace("'","\'", $answer['answer']); 	
                    UserAnswer::insert_user_answer_log($user_id, $get_track_details['id'], $answer_given, $result);
                }
            }
            $userGameProfile = UserGameSystem::getUserProfile($user_id);
            $userGameProfile->streak =  GameStreak::getStreak($userGameProfile->correct_guess_streak_counter);
            $response["ResponseCode"] = 200;
            $response["message"] = "User answer saved";
            $response["user_profile"] = $userGameProfile;
        }else{
            $response["ResponseCode"] = 400;
            $response["message"] = "Request have bad syntex";
        }

        return $response;
    }   
    
}
