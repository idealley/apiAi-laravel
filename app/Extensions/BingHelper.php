<?php 

namespace App\Extensions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

use App\Extensions\Helper;
use App\Extensions\WatsonHelper;

class BingHelper {

	public function getNews($query, $offset, $market = 'en-US'){
        //all available markets es-AR,en-AU,de-AT,nl-BE,fr-BE,pt-BR,en-CA,fr-CA,es-CL,da-DK,fi-FI,fr-FR,de-DE,zh-HK,en-IN,en-ID,en-IE,it-IT,ja-JP,ko-KR,en-MY,es-MX,nl-NL,en-NZ,no-NO,zh-CN,pl-PL,pt-PT,en-PH,ru-RU,ar-SA,en-ZA,es-ES,sv-SE,fr-CH,de-CH,zh-TW,tr-TR,en-GB,en-US,es-US
        $client = new Client();
        $response = $client->request('GET','https://api.cognitive.microsoft.com/bing/v5.0/news/search?q='.$query.'&count=1&offset='.$offset.'&mkt='.$market.'&safeSearch=Moderate&originalImg=true', ['headers' => ['Ocp-Apim-Subscription-Key' => env('BING_SEARCH')]]);
        //$response = $client->request('GET','https://api.cognitive.microsoft.com/bing/v5.0/news/search?q='.$query.'&count=1&offset='.$offset.'&safeSearch=Moderate&originalImg=1', ['headers' => ['Ocp-Apim-Subscription-Key' => env('BING_SEARCH')]]);

        $item = json_decode($response->getBody(), true);

        $news = $item['value'][0];

        //Getting the url without the bing redirect
        $url = $this->urlDecode($news['url']);

        $parsed['title'] = $news['name'];
        if(isset($news['image']['contentUrl'])){
            $parsed['image'] = $news['image']['contentUrl'];         
        } else {
            $parsed['image'] = isset($news['image']['thumbnail']['contentUrl']) ? $news['image']['thumbnail']['contentUrl'] : null;
        }
        $parsed['source'] = $news['provider'][0]['name']; 
        $parsed['link'] = $url;
        //Call to Alchemy to get the full body and the emotions
        $watson = new WatsonHelper();
        $results = $watson->getEmotion($url);
        if(!empty($results['body'])){
        	$helper = new Helper();
            $parsed['body'] = $helper->truncate($results['body'], 300);
        } else {
            $parsed['body'] = $news['description'];
        }
        $parsed['language'] = $results['language'];
        $parsed['emotion'] = $results['emotion'];
        $parsed['emoticon'] = $results['emoticon']; 

        return Response::json([
                'item'  => $parsed
            ], 200);
    }

    /**
    * Decode Bing url
    */
    public function urlDecode($url){
        $url = rawurldecode($url);
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        return $query['r'];
    }
}