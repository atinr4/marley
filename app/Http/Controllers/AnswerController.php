<?php

namespace App\Http\Controllers;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\Request;

use App\Quiz;
use App\Spotify;
use App\QuizOptions;
use App\UserAnswer;

class AnswerController extends Controller
{

    public function submitAnswer(Request $request)
    {
        $user_id = $request->input('user_id');
        $user_answer = $request->input('answer');
        if($user_id !="" && $user_answer !=""){
            $user_answer = json_decode($user_answer, true);
            foreach ($user_answer as $answer){
                $get_track_details = Quiz::get_track_details($answer['question_id']);
                if ($get_track_details>0) {
                    if ($answer['answer'] == $get_track_details['correct_answer']) {
                        $result = "correct";
                    } else {
                        $result = "incorrect";
                    }
                        
                    $answer_given = str_replace("'","\'", $answer['answer']); 	
                    UserAnswer::insert_user_answer_log($user_id,$get_track_details['id'],$answer_given,$result);
                }
            }
            $response["ResponseCode"] = 200;
            $response["message"] = "User answer saved";
        }else{
            $response["ResponseCode"] = 400;
            $response["message"] = "Request have bad syntex";
        }

        return $response;
    }   
    
}
