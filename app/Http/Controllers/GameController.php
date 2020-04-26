<?php

namespace App\Http\Controllers;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\Request;

use App\Quiz;
use App\Spotify;
use App\QuizOptions;
use App\UserAnswer;
use App\UserGameSystem;
use App\GameStreak;

class GameController extends Controller
{

    public function submitAnswer(Request $request)
    {
        $user_id = $request->get('user_id');
        $user_answer = $request->get('answer');
        
        if($user_id !="" && $user_answer !=""){

            foreach ($user_answer as $answer){
                $get_track_details = Quiz::get_track_details($answer['question_id']);

                $answer_given = str_replace("ʼ","\'", str_replace("'","\'", $answer['answer'])); 	
                
                if (count($get_track_details) > 0) {
                    if ($answer_given == $get_track_details['correct_answer']) {
                        $result = "correct";
                    } else {
                        $result = "incorrect";
                    }
                        
                   
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
    
    
    public function leaderBoard()
    {
        $getLeaderBoard = UserGameSystem::where('total_xp','!=',0)->orderBy('total_xp', 'DESC')->limit(5)->get();
        $response["ResponseCode"] = 200;
        $response["leader_board"] = $getLeaderBoard;

        return $response;
    }


    public function addLifeAfterAd(Request $request)
    {
        $user_id = $request->input('user_id');
        $userProfile = UserGameSystem::where('user_id', $user_id)->first();
        if($userProfile->lives >= 0 && $userProfile->lives < 3) {
            $userProfile->lives += 1;
        }
        $userProfile->save();
        return  $userProfile;
    }
    
}
