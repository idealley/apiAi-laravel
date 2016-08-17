<?php

namespace App\Extensions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class WatsonHelper {
    public function getEmotionByUrl($url){

        $client = new Client();
        $response = $client->request('GET','https://gateway-a.watsonplatform.net/calls/url/URLGetEmotion?apikey='.env('WATSON_ALCHEMY_API_KEY').'&url='.$url.'&showSourceText=1&sourceText=cleaned&outputMode=json');
        return $this->parseResponse($response);
    }

    public function getEmotionByText($text){

        $client = new Client();
        $response = $client->request('GET','https://gateway-a.watsonplatform.net/calls/text/TextGetEmotion?apikey='.env('WATSON_ALCHEMY_API_KEY').'&text='.$text.'&outputMode=json');
        return $this->parseResponse($response);
    }
        
    public function parseResponse($response){   
        $item = json_decode($response->getBody(), true);
        $news['language'] = $item['language'];
        if(isset($item['text'])){
            $news['body'] = $item['text'];
        }
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