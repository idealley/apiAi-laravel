<?php

namespace App\Extensions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class WatsonHelper {
    public function getEmotion($url){

        $client = new Client();
        $response = $client->request('GET','https://gateway-a.watsonplatform.net/calls/url/URLGetEmotion?apikey='.env('WATSON_ALCHEMY_API_KEY').'&url='.$url.'&showSourceText=1&sourceText=cleaned&outputMode=json');
        
        $item = json_decode($response->getBody(), true);
        $news['language'] = $item['language'];
        $news['body'] = $item['text'];
        $news['emotion'] = null;
        $news['emoticon'] = null;

        if($item['status'] == 'OK'){
            //Emoticons for sykpe
            $anger = ":@";
            $happy = "(happy)";
            $sad = ";(";
            $disgust = "(puke)";
            $fear = ":S";

            //Finding the predominant emotion
            arsort($item['docEmotions']);
            reset($item['docEmotions']);
            $emotion = key($item['docEmotions']);

            //Matching the emoticon with the emotion
            if ($emotion == "anger"){$emoticon = $anger;}
            if ($emotion == "disgust"){$emoticon = $disgust;}
            if ($emotion == "fear"){$emoticon = $fear;}
            if ($emotion == "joy"){$emoticon = $happy;}
            if ($emotion == "sadness"){$emoticon = $sad;}

            $news['emotion'] = $emotion;
            $news['emoticon'] = $emoticon;

        }
        return $news;        
    } 
}    