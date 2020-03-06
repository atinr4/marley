<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/spotify-user-details', [
    'as' => 'spotify-user-details', 'uses' => 'SpotifyController@getUserDetails'
]);

$router->get('/spotify-genres', [
    'as' => 'spotify-genres', 'uses' => 'SpotifyController@listGenres'
]);

$router->get('/spotify-categories', [
    'as' => 'spotify-categories', 'uses' => 'SpotifyController@listCategories'
]);

$router->get('/spotify-category-playlist/{category}', [
    'as' => 'spotify-category-playlist', 'uses' => 'SpotifyController@categoryPlaylist'
]);

$router->post('/submit-answer', [
    'as' => 'submit-answer', 'uses' => 'AnswerController@submitAnswer'
]);
