<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('home');
});


//Route::get('news-search/{query}/{?offset}/{?market}', 'NewsController@getNews');

//Route::get('api/rss/{rssFeed}', 'RssController@feed');
Route::post('api/api-ai', 'NewsController@apiAi');
//Route::get('api/api-ai', 'NewsController@apiAi');
Route::post('api/webhook', 'NewsController@webhook');
Route::get('api/webhook', 'NewsController@webhook');
Route::get('skype', 'NewsController@skypeChat');

//Route::post('api/emotion', 'RssController@getEmotion');
//Route::get('user/{id}', 'UserController@show');
//Route::get('spotify/{query}', 'NewsController@spotify');
//Route::post('test', 'RssController@sendRequest');

//Route::get('news-search/{query}', 'NewsController@getNews');

