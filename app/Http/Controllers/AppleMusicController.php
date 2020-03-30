<?php

namespace App\Http\Controllers;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PouleR\AppleMusicAPI\AppleMusicAPITokenGenerator;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Quiz;
use App\Spotify;
use App\QuizOptions;
use App\UserAnswer;
use App\UserGameSystem;
use App\GameStreak;


class AppleMusicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generatDeveloperToken()
    {
        $tokenGenerator = new AppleMusicAPITokenGenerator();
        $p8File = Storage::path(env('APPLE_AUTH_P8'));
        $jwtToken = $tokenGenerator->generateDeveloperToken( 
            env('APPLE_MUSIC_TEAM_ID'),
            env('APPLE_MUSIC_KEY_ID'),
            $p8File
        );

        return $jwtToken;
    }

    public function getUserDetails(Request $request)
    {
        $user = Auth::user();
        $getUserProfile = UserGameSystem::where('email', $user->email)->where('login_using','Apple')->first();
        $getUserProfile->streak =  GameStreak::getStreak($getUserProfile->correct_guess_streak_counter);
        return $getUserProfile;

    } 

    public function listCategories(Request $request)
    {
        $developerToken = $this->generatDeveloperToken();
        
        $curlService = new \Ixudra\Curl\CurlService();
        $response = $curlService->to(env('APPLE_MUSIC_API')."/catalog/us/apple-curators")
        ->withData( array( 'ids' => '988656348,988556214,976439528,989066661,989071074,976439534,989074778,1441811365,989076708,989061185,976439542,988658197,988658201,982347996,988588080,988583890,976439552,988581516,988578699,988578275,97643958') )
        ->withHeader('Accept: */*')
        ->withHeader('Content-Type: application/json')
        ->withHeader('Authorization: Bearer '.$developerToken)
        ->get();

        $result = json_decode($response);
        
        $listCategory = array();
        foreach ($result->data as $category) {
            $newArray['id'] = $category->id;
            $newArray['name'] = $category->attributes->name;
            $newArray['href'] = $category->attributes->url;
            $listCategory[] = $newArray;
        }

        return $listCategory;
    }

    public function categoryPlaylist($category_id, Request $request)
    {
        $developerToken = $this->generatDeveloperToken();

        $curlService = new \Ixudra\Curl\CurlService();
        $response = $curlService->to(env('APPLE_MUSIC_API')."/catalog/us/apple-curators")
        ->withData( array( 'ids' => $category_id) )
        ->withHeader('Accept: */*')
        ->withHeader('Content-Type: application/json')
        ->withHeader('Authorization: Bearer '.$developerToken)
        ->get();

        $result = json_decode($response);
        $rand_keys_playlist = array_rand($result->data[0]->relationships->playlists->data, 10);
        if(count($result->data[0]->relationships->playlists->data) > 0) {
            if(count($result->data[0]->relationships->playlists->data) >= 5)
			    $rand_keys = array_rand($result->data[0]->relationships->playlists->data, 5);
			else
                $rand_keys = array_rand($result->data[0]->relationships->playlists->data, count($result->data[0]->relationships->playlists->data));

            $tracklist = array();
            for ($i=0;$i<4;$i++) {
                $responsePlayListCurl = $curlService->to(env('APPLE_MUSIC_API').'/catalog/us/playlists')
                    ->withData( array( 'ids' => $result->data[0]->relationships->playlists->data[$rand_keys[$i]]->id)  )
                    ->withHeader('Accept: */*')
                    ->withHeader('Content-Type: application/json')
                    ->withHeader('Authorization: Bearer '.$developerToken)
                    ->get();
                $responsePlayList = json_decode($responsePlayListCurl);
                
                $rand_keys_playlist = array_rand($responsePlayList->data[0]->relationships->tracks->data, count($responsePlayList->data[0]->relationships->tracks->data));

                for ($j=0; $j < 3; $j++) {
                    if ($responsePlayList->data[0]->relationships->tracks->data[$j]->attributes->previews[0]->url!=""){
                        $getSongOption = QuizOptions::getSongOption();
                        $option_list = array(
                                            $getSongOption[0]['optionTitle'],
                                            $getSongOption[1]['optionTitle'],
                                            $getSongOption[2]['optionTitle'],
                                            $responsePlayList->data[0]->relationships->tracks->data[$j]->attributes->name
                                        );
                        shuffle($option_list);

                        $track_id = $responsePlayList->data[0]->relationships->tracks->data[$j]->id;
                        $check_track_exists = Quiz::check_track_exists($track_id);
                        
                        $correct_answer = str_replace("ʼ","\'", str_replace("'","\'", $responsePlayList->data[0]->relationships->tracks->data[$j]->attributes->name)); 	
						$option = implode(",",$option_list);
                        $option = str_replace("'","\'", $option); 
                        $option = str_replace("ʼ","\'",$option);
                        
                        if ($check_track_exists == 0)
                            Quiz::insert_question($track_id,"What is the name of this song?",$correct_answer,$option);
                        
                        $trackData = array(
										"id" => $responsePlayList->data[0]->relationships->tracks->data[$j]->id,
						  				"tracks_url" => $responsePlayList->data[0]->relationships->tracks->data[$j]->attributes->previews[0]->url,
										"apple_music_url" => $responsePlayList->data[0]->relationships->tracks->data[$j]->attributes->url,
										"correct_answer" => $responsePlayList->data[0]->relationships->tracks->data[$j]->attributes->name,
										"options_title" => 'What is the name of this song?',
										"options" => $option_list
						  );
						array_push($tracklist, $trackData);
                    }
                }
            }
            $responseData["status"] = 200;
			$responseData["tracklist"] = $tracklist;
        }

        return $responseData;
    }
    
}
