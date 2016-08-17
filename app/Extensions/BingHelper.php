<?php 

namespace App\Extensions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

use App\Extensions\Helper;
use App\Extensions\WatsonHelper;
use App\Extensions\GoogleTranslate;

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
            //$parsed['image'] = url('img/placeholder.png');
            $parsed['image'] = null;
        }

        $parsed['source'] = $news['provider'][0]['name']; 
        $parsed['link'] = $url;
        $parsed['language'] = 'german';
        $parsed['body'] = $news['description'];
        $parsed['emotion'] = null;
        $parsed['emoticon'] = null; 
        //Call to Alchemy to get the full body and the emotions
        $watson = new WatsonHelper();
        if($parsed['source'] != 'Blick'){
            $results = $watson->getEmotionByUrl($url);
            $parsed['language'] = $results['language'];
            $helper = new Helper();
            $parsed['body'] = preg_replace('/[\s\t\n\r\s]+/', ' ', $helper->truncate($results['body'], 300));
            $parsed['emotion'] = $results['emotion'];
            $parsed['emoticon'] = $results['emoticon']; 
        }

        if($parsed['source'] == 'Blick'){
            //Getting the translation
            $t = new GoogleTranslate();
            $response = $t->getTranslation($news['description']);
            $results = $watson->getEmotionByText($response);

            if(!empty($results['emotion'])){
                //let translate the emotion
                    if ($results['emotion'] == "anger"){$emotion = "Zorn";}
                    if ($results['emotion'] == "disgust"){$emotion = "Ekel";}
                    if ($results['emotion'] == "fear"){$emotion = "Angst";}
                    if ($results['emotion'] == "joy"){$emotion = "Freude";}
                    if ($results['emotion'] == "sadness"){$emotion = "Traurigkeit";}
                $parsed['emotion'] = $emotion;
                $parsed['emoticon'] = $results['emoticon']; 
            }
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