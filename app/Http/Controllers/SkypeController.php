<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class SkypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Send a message to the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function send($id)
    {
        $redirectUrl = urlencode('https://news-agent.idealley.ch/skype');

        $client = new Client();

        $response = $client->request('POST','https://login.microsoftonline.com/common/oauth2/v2.0/token', ["form_params" => [
                "client_id" => env('MICROSOFT_APP_ID'),
                "client_secret" => env('MICROSOFT_APP_SECRET'),
                'grant_type' => 'client_credentials',
                'scope' => 'https://graph.microsoft.com/.default'
            ]]);

        $token = json_decode($response->getBody(), true);
        $accessToken = $token['access_token'];

        $username = $id;
//here we could customize messages e.g. do a request to the webhook with some params.
        $send = [
            'headers' => 
                    [
                    'Authorization' => 'Bearer '.$accessToken
                    ],
             'json' => [ 
                    'type' => 'message/card',
                    'attachments' => [
                        [
                            'contentType' => 'application/vnd.microsoft.card.hero', 
                            'content' => [
                                    'title' => 'Here is the latest news',
                                    'subtitle' => "Some body did something somewhere",
                                    'text' => 'And it is very <i>important</i>!!! here <b>is the</b> text',
                                    'thumbnailUrl' => 'http://f3.blick.ch/img/incoming/origs5340272/0950146232-w980-h653/SPO-Embolo-mit-Freundin.jpg',
                                    'fallbackText' => 'Image: http://f3.blick.ch/img/incoming/origs5340272/0950146232-w980-h653/SPO-Embolo-mit-Freundin.jpg',
                                    'images' => [
                                        [
                                        'image' => "http://f3.blick.ch/img/incoming/origs5340272/0950146232-w980-h653/SPO-Embolo-mit-Freundin.jpg",
                                        'alt' => 'Some text about the image'
                                        ]
                                    ]
                            ],
                            'buttons' => [
                                [
                                    'type' => 'openUrl',
                                    'title' => 'website',
                                    'value' => "http://www.blick.ch/sport/fussball/international/bundesliga/das-ist-breels-freundin-diese-frau-macht-embolo-stark-id5340276.html"
                                ]
                            ]
                            
                        ]
                    ]             
  
                ]
            ];  

            //dd(json_encode($send));
        $response = $client->request('POST','https://apis.skype.com/v3/conversations/8:'.$username.'/activities', $send);
 
        return "The message has been sent"; 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
