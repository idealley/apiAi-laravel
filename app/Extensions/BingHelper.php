<?php 

namespace App\Extensions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

use App\Extensions\Helper;
use App\Extensions\WatsonHelper;

class BingHelper {

	public function getNews($query, $offset, $market = 'en-US', $category = false){
        //all available markets es-AR,en-AU,de-AT,nl-BE,fr-BE,pt-BR,en-CA,fr-CA,es-CL,da-DK,fi-FI,fr-FR,de-DE,zh-HK,en-IN,en-ID,en-IE,it-IT,ja-JP,ko-KR,en-MY,es-MX,nl-NL,en-NZ,no-NO,zh-CN,pl-PL,pt-PT,en-PH,ru-RU,ar-SA,en-ZA,es-ES,sv-SE,fr-CH,de-CH,zh-TW,tr-TR,en-GB,en-US,es-US
        $client = new Client();
        if(!$category){
            $params = '?q='.$query.'&count=1&offset='.$offset.'&mkt='.$market.'&safeSearch=Moderate&originalImg=true';
        } else {
            $market = 'en-US';
            $params = '?q='.$query.'category='.$category.'&count=1&offset='.$offset.'&mkt='.$market.'&safeSearch=Moderate&originalImg=true';
        }
        $response = $client->request('GET','https://api.cognitive.microsoft.com/bing/v5.0/news/search'.$params, ['headers' => ['Ocp-Apim-Subscription-Key' => env('BING_SEARCH')]]);

        $item = json_decode($response->getBody(), true);

        $news = $item['value'][0];

        //Getting the url without the bing redirect
        $url = $this->urlDecode($news['url']);

        $parsed['title'] = $news['name'];

        if(isset($news['image']['contentUrl'])){
            $parsed['image'] = $news['image']['contentUrl'];         
        } else {
            $parsed['image'] = url('img/placeholder.png');
        }

        $parsed['source'] = $news['provider'][0]['name']; 
        $parsed['link'] = $url;
        $parsed['language'] = 'german';
        $parsed['emotion'] = null;
        $parsed['emoticon'] = null; 
        //Call to Alchemy to get the full body and the emotions
        $watson = new WatsonHelper();
        if($parsed['source'] != 'Blick'){
            $results = $watson->getEmotion($url);
        }
        if(!empty($results['body'])){
        	$helper = new Helper();
            $parsed['body'] = preg_replace('/[\s\t\n\r\s]+/', ' ', $helper->truncate($results['body'], 300));
            $parsed['language'] = $results['language'];
            $parsed['emotion'] = $results['emotion'];
            $parsed['emoticon'] = $results['emoticon']; 
        } else {
            $parsed['body'] = $news['description'];
        }

        return [ 'item'  => $parsed ];
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