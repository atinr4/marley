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

class SpotifyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getUserDetails(Request $request)
    {
        $access_token = $request->input('access_token');
        $curlService = new \Ixudra\Curl\CurlService();
        $response = $curlService->to(env('SPOTIFY_API_BASE').'/me')
        ->withHeader('Accept: application/json')
        ->withHeader('Content-Type: application/json')
        ->withHeader('Authorization: Bearer '.$access_token)
        ->get();

        $responseCheck = json_decode($response);      

        if(isset($responseCheck->error) && $responseCheck->error->status == 401)
        {
            return response()->json($responseCheck);
        }

        UserGameSystem::checkUserGameProfile($responseCheck);
        $getUserProfile =  UserGameSystem::getUserProfile($responseCheck->id);
        $getUserProfile->streak =  GameStreak::getStreak($getUserProfile->correct_guess_streak_counter);
        return $getUserProfile;
    }

    public function listGenres(Request $request)
    {
        $access_token = $request->input('access_token');
        $curlService = new \Ixudra\Curl\CurlService();

        $getUserDetails = $this->getUserDetails();
        
        $country = $getUserDetails->country;

        $response = $curlService->to(env('SPOTIFY_API_BASE').'/recommendations/available-genre-seeds')
        ->withData( array( 'country' => $country ) )
        ->withHeader('Accept: application/json')
        ->withHeader('Content-Type: application/json')
        ->withHeader('Authorization: Bearer '.$access_token)
        ->get();
        
        return $response;
    }

    /**
     * List Categories of Spotify
     * return @array
     */
    public function listCategories(Request $request)
    {
        $access_token = $request->input('access_token');
        $curlService = new \Ixudra\Curl\CurlService();

        $getUserDetails = $this->getUserDetails();
        if(isset($getUserDetails->error) && $getUserDetails->error->status == 401)
        {
            return response()->json($getUserDetails);
        }
        $country = $getUserDetails->country;

        $response = $curlService->to(env('SPOTIFY_API_BASE').'/browse/categories')
        ->withData( array( 'country' => $country,'limit' => 10 ) )
        ->withHeader('Accept: application/json')
        ->withHeader('Content-Type: application/json')
        ->withHeader('Authorization: Bearer '.$access_token)
        ->get();

        $result = json_decode($response);
        $listCategory = array();
        foreach ($result->categories->items as $category) {
            $newArray['id'] = $category->id;
            $newArray['name'] = $category->name;
            $newArray['href'] = $category->href;
            $listCategory[] = $newArray;
        }
        return $listCategory;
    }

    public function categoryPlaylist($category, Request $request)
    {
        $access_token = $request->input('access_token');
        $curlService = new \Ixudra\Curl\CurlService();

        $getUserDetails = $this->getUserDetails();
        if(isset($getUserDetails->error) && $getUserDetails->error->status == 401)
        {
            return response()->json($getUserDetails);
        }
        
        $country = $getUserDetails->country;

        $response = $curlService->to(env('SPOTIFY_API_BASE').'/browse/categories/'.$category.'/playlists')
        ->withData( array( 'country' => $country,'limit' => 12,'offset'=> rand(0,20) ) )
        ->withHeader('Accept: application/json')
        ->withHeader('Content-Type: application/json')
        ->withHeader('Authorization: Bearer '.$access_token)
        ->get();
        
        $result = json_decode($response);
        $rand_keys_playlist = array_rand($result->playlists->items, $result->playlists->limit);
        if(count($result->playlists->items) > 0) {
            if(count($result->playlists->items) >= 5)
			    $rand_keys = array_rand($result->playlists->items, 5);
			else
			    $rand_keys = array_rand($result->playlists->items, count($result->playlists->items));

            $tracklist = array();
            for ($i=0;$i<sizeof($rand_keys);$i++) {
                
                $responsePlayListCurl = $curlService->to(env('SPOTIFY_API_BASE').'/playlists/'.$result->playlists->items[$rand_keys[$i]]->id.'/tracks')
                ->withData( array( 'country' => $country,'limit' => 12,'offset'=> rand(0,20) ) )
                ->withHeader('Accept: application/json')
                ->withHeader('Content-Type: application/json')
                ->withHeader('Authorization: Bearer '.$access_token)
                ->get();
                $responsePlayList = json_decode($responsePlayListCurl);
                $rand_keys_playlist = array_rand($responsePlayList->items, count($responsePlayList->items));
                for ($j=0; $j < sizeof($rand_keys_playlist); $j++) {
                    if ($responsePlayList->items[$j]->track->preview_url!=""){
                        $getSongOption = QuizOptions::getSongOption();
                        $option_list = array(
                                            $getSongOption[0]['optionTitle'],
                                            $getSongOption[1]['optionTitle'],
                                            $getSongOption[2]['optionTitle'],
                                            $responsePlayList->items[$j]->track->name
                                        );
                        shuffle($option_list);

                        $track_id = $responsePlayList->items[$j]->track->id;
                        $check_track_exists = Quiz::check_track_exists($track_id);
                        
                        $correct_answer = str_replace("ʼ","\'", str_replace("'","\'", $responsePlayList->items[$j]->track->name)); 	
						$option = implode(",",$option_list);
                        $option = str_replace("ʼ","\'", str_replace("'","\'", $option)); 
                        
                        if ($check_track_exists == 0)
                            Quiz::insert_question($track_id,"What is the name of this song?",$correct_answer,$option);
                        
                        $trackData = array(
										"id" => $responsePlayList->items[$j]->track->id,
						  				"tracks_url" => $responsePlayList->items[$j]->track->preview_url,
										"spotify_url" => $responsePlayList->items[$j]->track->external_urls->spotify,
										"correct_answer" => $responsePlayList->items[$j]->track->name,
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
