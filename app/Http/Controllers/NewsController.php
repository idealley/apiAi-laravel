<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimplePie;

use Log;
use App\Http\Requests;
use Illuminate\Support\Facades\Response;
use League\OAuth2\Client\Provider\GenericProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class NewsController extends Controller
{
    public function webhook(Request $request){
        //Getting the POST request from API.AI and decoding it
        $results = json_decode($request->getContent(), true);
        //Log::debug("API call >>>>>>>>>>>>> ");
        //Log::debug($results);
        
        $answer = $this->answer($results);

        //Log::debug("Processed >>>>>>>>>>>>> ".$results);

        $context = '';

        if($answer['music'] === null){
            //if necessary, we can truncate the length of the body here with the function truncate()
            //use as follow: $body = $this->truncate($answer['news']['body'], 100)
            // 100 is the length you can put what ever, it is smart so it will only cut after full words
            $body = $this->truncate($answer['news']['body'], 200);
            $response = $answer['news']['title']."\n\n".$body."\n\nRead more: ".$answer['news']['link'];
            $displayText = null;
            $source = $answer['news']['source'];
            if($answer['intent'] == "More info") { 
                $context = ['name' => 'next-news', 'lifespan' => 5, 'parameters' => ['offset-news' => $answer['offset-news']]];
            }

            if($answer['news']['emotion'] !== null){
                /**
                | For demo purposes uncomment the bellow line to remove emoticons from the response
                | To remove the comment remove // from the begining of the line
                */
                $response = $answer['speech']."\n\n According to Watson the main emotion expressed in the article is: ".$answer['news']['emotion']."\n\n".$answer['news']['title']."\n\n".$body."\n\nRead more: ".$answer['news']['link'];
                
                /**
                | For demo purposes comment the bellow line to remove emoticons from the response
                | To comment add // at the begining of the line
                */
                //$response = $answer['speech']."\n\n According to Watson the main emotion expressed in the article is: ".$answer['news']['emoticon']." ( ".$answer['news']['emotion']." )\n\n  ".$answer['news']['title']."\n\n".$body."\n\nRead more: ".$answer['news']['link'];
                $displayText = $answer['speech'].". According to Watson the main emotion expressed in the article is: ".$answer['news']['emotion'];
            }
        } else {
            $response = $answer['speech'].": \n\n".$answer['music']['title']."\n\n(music)\n\n".$answer['music']['url']."\n\nlisten to the full song here: ".$answer['music']['full'];
            $displayText = $answer['speech']." Title: ".$answer['music']['title'];
            $source = "Spotify";
            if($answer['intent'] == "next song") {    
                $context = ['name' => 'next-song', 'lifespan' => 5, 'parameters' => ['offset-song' => $answer['offset-song']]];
            }
        }

        if(isset($answer['news']['title']) || $answer['music']){
                $speech = $response;
                $text = $displayText;
        } else {
                $speech = $answer['speech'];
                $text = $answer['speech'];
            }


       // Log::debug("to send >>>>>>>>>>>>> ".$context);

        //this is a valid response for API.AI 
        return Response::json([
                    'speech'   => $speech,
                    'displayText' => $text,
                    'data' => ['newsAgent' => $answer],
                    'contextOut' => [$context],
                    'source' => $source
            ], 200);

    }


    public function apiAi(Request $request){

        $results = $this->sendRequest($request);
        if(isset($results['result']['fulfillment']['data'])){
            $result = $results['result']['fulfillment']['data']['newsAgent'];
        } else {
            $result = $results['result']['fulfillment'];
            $result['action'] = isset($results['result']['action']) ? $results['result']['action'] : null;
        }

        //Here we format the response for the JS on the frontend
        return Response::json([
                'news'  => isset($result['news']) ? $result['news'] : null,
                'music' => isset($result['music']) ? $result['music'] : null,
                'speech'   => $result['speech'],
                'action' => $result['action'],
                'subject' => isset($result['subject']) ? $result['subject'] : null,
                'intent' => isset($result['intent']) ? $result['intent'] : null,
                'adjective' => isset($result['adjective']) ? $result['adjective'] : null
            ], 200);
    }

    public function spotify($query, $offset){
        $session = new \SpotifyWebAPI\Session(env('SPOTIFY_CLIENT_ID'), env('SPOTIFY_CLIENT_SECRET'), url('spotify'));
        $api = new \SpotifyWebAPI\SpotifyWebAPI();

        $session->requestCredentialsToken();
        $accessToken = $session->getAccessToken(); // We're good to go!

        // Set the code on the API wrapper
        $api->setAccessToken($accessToken);
        //search for songs
        $tracks = $api->search($query, 'track', array(
            'offset' => $offset,
            'limit' => 1
        ));
        
        $song['url'] = $tracks->tracks->items[0]->preview_url;
        $song['title'] = $tracks->tracks->items[0]->name;
        $song['full'] =  $tracks->tracks->items[0]->external_urls->spotify;
        $song['image'] = $tracks->tracks->items[0]->album->images[0]->url;

        return $song;

    }

    /**
    * Send the Request to API.AI
    * @param object $request
    * @return array
    */
    public function sendRequest($request){
        $apiai_key = env('API_AI_ACCESS_TOKEN');
        $apiai_subscription_key = env('API_AI_DEV_TOKEN');
        
        $query = $request->input('query');
        
        $client = new Client();

        $send = ['headers' => [
                    'Content-Type' => 'application/json;charset=utf-8', 
                    'Authorization' => 'Bearer '.$apiai_key
                    ],
                'body' => json_encode([                
                    'query' => $query, 
                    'lang' => 'en'
                    ])
                ];  

        $response = $client->post('https://api.api.ai/v1/query?v=20150910', $send);

        return json_decode($response->getBody(),true);
    }

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

    /**
    * Parse the results received from API.AI and parse it with some logic to
    * get some news or music to display
    * @param array $results
    * @return array
    */
    public function answer($results){
        //API.AI Fulfillment
        $speech = isset($results['result']['fulfillment']['speech']) ? $results['result']['fulfillment']['speech'] : '';
        $newsSource = isset($results['result']['fulfillment']['source']) ? $results['result']['fulfillment']['source'] : '';
        //$displayText = isset($results['result']['fulfillment']['displayText']) ? $results['result']['fulfillment']['displayText'] : '';;
        //API.AI Result 
        $query = isset($results['result']['resolvedQuery']) ? $results['result']['resolvedQuery'] : false;
        $action = isset($results['result']['action']) ? $results['result']['action'] : false;
        $intent = isset($results['result']['metadata']['intentName']) ? $results['result']['metadata']['intentName'] : false;;
        $apiAiSource = isset($results['result']['source']) ? $results['result']['source'] : false;
        //API.AI Result params
        $subject = isset($results['result']['parameters']['subject']) ? $results['result']['parameters']['subject'] : false;
        $adjective = isset($results['result']['parameters']['adjective']) ? $results['result']['parameters']['adjective'] : false;
        $news = isset($results['result']['parameters']['news']) ? $results['result']['parameters']['news'] : false;
        //API.AI Fulfillment data (News agent data sent back...)
        //$data = isset($results['result']['fulfillment']['data']['newsAgent']) ? $results['result']['fulfillment']['data']['newsAgent'] : null;
        //$title = isset($data['title']) ? $data['title'] : null;
        //$image = isset($data['image']) ? $data['image'] : null;
        //$webSource = isset($data['source']) ? $data['source'] : null;
        //$link = isset($data['link']) ? $data['link'] : null;
        //$body = isset($data['body']) ? $data['body'] : null;
        //$language = isset($data['languange']) ? $data['languange'] : null;
        //$emotion = isset($data['emotion']) ? $data['emotion'] : null;
        //$emoticon = isset($data['emoticon']) ? $data['emoticon'] : null;
        $indexSong = $this->getIndex('next-song', $results['result']['contexts']);
        $indexNews = $this->getIndex('next-news', $results['result']['contexts']);
        $offsetSong = isset($results['result']['contexts'][$indexSong]['parameters']['offset-song']) ? $results['result']['contexts'][$indexSong]['parameters']['offset-song'] : 0;
        if(empty($offsetSong)){
            $offsetSong = 0;
        } 
        $offsetNews = isset($results['result']['contexts'][$indexNews]['parameters']['offset-news']) ? $results['result']['contexts'][$indexNews]['parameters']['offset-news'] : 0;
        if(empty($offsetNews)){
            $offsetNews = 0;
        } 
       if(empty($subject) && empty($adjective)){
            $subject = $query;
        }
        //Response defaults
        $answer['adjective'] = $adjective;
        $answer['subject'] = $subject;
        $answer['intent'] = $intent;
        $answer['action'] = $action;
        //$answer['offset.original'] = $offset;
        $answer['news'] = null;
        $answer['music'] = null;

        //$answer['resolvedQuery'] = $resolvedQuery;
        
        //start formating the response to the app
        $answer['speech'] = $speech;
        // speech response for webhooks call

        if(!$action && $speech == '' && !$subject){
            $answer['speech'] = "Sorry, ".$resolvedQuery." did not return any result";
            $answer['news'] = 'Nothing is happening right now. Check later!';
        } 
  
        if($action == "show.news"){
        //if local -> query needs to be site:blick.ch
                        $query = $subject;
                        if(empty($subject)){
                            $query = $adjective;
                        }
                        if($intent == "More info") {
                            ++$offsetNews;
                            $answer['offset-news'] = $offsetNews;
                        }
                        $market = 'en-US';
                        //let's consider for now that local news come from Blick.ch
                        if($adjective == 'local'){
                            $market = 'de-CH';
                            $query = 'site:blick.ch/news/schweiz/+'.$subject;
                        }

                        if($adjective == 'swiss' || $adjective == 'Swiss'){
                            $market = 'de-CH';
                        }
                        $response = $this->getNews($query, $offsetNews, $market);
                        $news = json_decode($response->getContent(), true);
                        $answer['news'] = $news['item'];
                        //Adding speech for the webapp. $displayText is used because $speech "enriched"
                        //to display more info (emoticons, urls, etc) in skype and other bots as far 
                        //as API.AI uses this key to answer the user.
                        $answer['speech'] = $speech;
        }

        //the domain using this action is not free
        if($action == "news.search"){
                //
        }
        
        if($action == "play.music"){
                if($intent == "next song") {
                        ++$offsetSong;
                        $answer['offset-song'] = $offsetSong;    
                    }
                if(!empty($subject)){
                    $song = $this->spotify($subject, $offsetSong);
                } elseif (!empty($adjective)) {
                    $song = $this->spotify($adjective, $offsetSong);
                } else {
                    $song = $this->spotify($resolvedQuery, $offsetSong);
                }                 
                if($song != null){ 
                    $answer['music'] = $song;
                } else {
                    $answer['music'] = $this->spotify("Opera", $offsetSong);
                }   

        }
        
        //the domain using this action is not free
        if($action == "wisdom.unknown"){
                    if($intent == "next song") {
                        ++$offsetSong;
                        $answer['offset-song'] = $offsetSong;
                    }
                    $answer['speech'] = "Sorry it took me a long time and I did not find any related music, but meanwhile I found this:";
                    $song = $this->spotify('opera', $offset);
                    $answer['music'] = $song;
            }
        return $answer;
    }

    public function truncate($string, $length = 300, $append = "..."){
        $string = trim($string);

        if(strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0]. $append;
        }

        return $string;

    }

    public function getIndex($name, $array){
    foreach($array as $key => $value){
        if(is_array($value) && $value['name'] == $name)
              return $key;
    }
    return null;
    }

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
        $results = $this->getEmotion($url);
        if(!empty($results['body'])){
            $parsed['body'] = $this->truncate($results['body'], 300);
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

    public function skypeChat(Request $request){

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
        //tanushechka.krasotushechka
        //$username = 'samuel.pouyt';
        $username = "tanushechka.krasotushechka";

        $send = [
            'headers' => 
                    [
                    'Content-Type' => 'application/json;charset=utf-8', 
                    'Authorization' => 'Bearer '.$accessToken
                    ],
             'json' => [                
                    'message' => [
                        'content' => "Hi! (wave)\nHere are the latest news that I thought could interest you: 
                        \n (bell) First news
                        \n (bell) Second news
                        \n (bell) Third news"
                        ]
                    ]  
                ];  
        $response = $client->request('POST','https://apis.skype.com/v2/conversations/8:'.$username.'/activities', $send);
 
        return "The message has been sent"; 


    }

}
