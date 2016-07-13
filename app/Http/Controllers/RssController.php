<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimplePie;

use App\Http\Requests;
use Illuminate\Support\Facades\Response;
use League\OAuth2\Client\Provider\GenericProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GRequest;
use GuzzleHttp\Psr7\Response as GResponse;

class RssController extends Controller
{
    public function webhook(Request $request){
        //Getting the POST request from API.AI and decoding it
        $results = json_decode($request->getContent(), true);
        $answer = $this->answer($results);
        $source = 'Not Set';
        $music = isset($answer['music']) ? $answer['music'] : false;

        if(!$music){
            $body = $answer['news']['body'];
            
            if($answer['news']['emotions']){

                $anger = ":@";
                $happy = "(happy)";
                $sad = ";(";
                $disgust = "(puke)";
                $fear = ":S";

                $emotions = array_keys($answer['news']['emotions'], max($answer['news']['emotions']));

                if ($emotions[0] == "anger"){$emotion = $anger;}
                if ($emotions[0] == "disgust"){$emotion = $disgust;}
                if ($emotions[0] == "fear"){$emotion = $fear;}
                if ($emotions[0] == "joy"){$emotion = $happy;}
                if ($emotions[0] == "sadness"){$emotion = $sad;}

                $response = $answer['fulfillment']."\n\n Watson found that this article main emotion is: ".$emotions[0]."\n\n".$emotion."\n\n".$answer['news']['title']."\n\n".$body."\n\nRead more: ".$answer['news']['permalink'];
                $source = 'Watson and the Web';
            } else {
                $response = $answer['news']['title']."\n\n".$body."\n\nRead more: ".$answer['news']['permalink'];
                $source = 'Local news, Blick.ch';
            }
        } else {
            $response = $answer['fulfillment'].": \n\n (music) \n\n".$answer['music'];
        }

        if(isset($answer['news']['title']) || $music){
                $speech = $response;
                $text = $response;
        } else {
                $speech = $answer['speech'];
                $text = $answer['speech'];
            }

        //this is a valid response for API.AI 
        return Response::json([
                    'speech'   => $speech,
                    'displayText' => $text,
                    'data' => ['newsAgent' => $answer],
                    'contextOut' => [],
                    'source' => $source
            ], 200);

    }


    public function apiAi(Request $request){

        $results = $this->sendRequest($request);
         if($request->path() == 'api/api-ai'){
            $results['result']['metadata']['webhookUsed'] = "false";
            $results['webapp'] = true;
         }

        $answer = $this->answer($results);

        //Here we format the response for the JS on the frontend
        return Response::json([
                'news'  => isset($answer['news']) ? $answer['news'] : null,
                'music' => isset($answer['music']) ? $answer['music'] : null,
                'speech'   => $answer['speech'],
                'action' => $answer['action'],
                'subject' => $answer['subject'],
                'contexts' => $answer['contexts'],
                'intent' => $answer['intent'],
                'adjective' => $answer['adjective']
            ], 200);
    }

    public function spotify($query){
        $session = new \SpotifyWebAPI\Session(env('SPOTIFY_CLIENT_ID'), env('SPOTIFY_CLIENT_SECRET'), url('spotify'));
        $api = new \SpotifyWebAPI\SpotifyWebAPI();

        $session->requestCredentialsToken();
        $accessToken = $session->getAccessToken(); // We're good to go!

        // Set the code on the API wrapper
        $api->setAccessToken($accessToken);
        //search for songs
        $tracks = $api->search($query, 'track', array(
            'limit' => 2,
        ));
        
        $song['playing'] = $tracks->tracks->items[0]->preview_url;
        $song['next'] = $tracks->tracks->items[1]->preview_url;
        
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

        $send = [
        'headers' => 
                [
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

    public function getEmotion(Request $request){
        
        //$url = $request->input('url');
        $results = json_decode($request->getContent(), true);
        $url = rawurldecode($results['url']);
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        $url = $query['r'];

        $client = new Client();

        $response = $client->request('GET','https://gateway-a.watsonplatform.net/calls/url/URLGetEmotion?apikey='.env('WATSON_ALCHEMY_API_KEY').'&url='.$url.'&showSourceText=1&sourceText=cleaned_or_raw&outputMode=json');

        return $response->getBody();

    }

    /**
    * Parse the results received from API.AI and parse it with some logic to
    * get some news or music to display
    * @param array $results
    * @return array
    */
    public function answer($results){
        //setting defaults
        $answer = array();
        $source = isset($results['result']['source']) ? $results['result']['source'] : false;
        $action = isset($results['result']['action']) ? $results['result']['action'] : false;
        $intent = isset($results['result']['metadata']['intentName']) ? $results['result']['metadata']['intentName'] : false;
        $adjective = isset($results['result']['parameters']['adjective']) ? $results['result']['parameters']['adjective'] : false;
        $speech =  isset($results['result']['speech']) ? $results['result']['speech'] : '';
        $fulfillment =  isset($results['result']['fulfillment']['speech']) ? $results['result']['fulfillment']['speech'] : '';
        $subject = isset($results['result']['parameters']['subject']) ? $results['result']['parameters']['subject'] : false;
        $contexts =  isset($results['result']['metadata']['contexts']) ? $results['result']['metadata']['contexts'] : false; // array
        $webhookUsed =  isset($results['result']['metadata']['webhookUsed']) ? $results['result']['metadata']['webhookUsed'] : false; // array
        $resolvedQuery = isset($results['result']['resolvedQuery']) ? $results['result']['resolvedQuery'] : false;
        $webapp = isset($results['webapp']) ? $results['webapp'] : false;
        $data = isset($results['result']['fulfillment']['data']['newsAgent']) ? $results['result']['fulfillment']['data']['newsAgent'] : null;

        //Defaults
        $answer['adjective'] = $adjective;
        $answer['subject'] = $subject;
        $answer['contexts'] = $contexts;
        $answer['intent'] = $intent;
        $answer['action'] = $action;
        $answer['news'] = '';
        //$answer['resolvedQuery'] = $resolvedQuery;
        
        //start formating the response to the app
        $answer['speech'] = $fulfillment;
        // speech response for webhooks call
        $answer['fulfillment'] = $fulfillment;

        If(!$action && $speech == '' && !$subject){
            $answer['speech'] = "Sorry, ".$resolvedQuery." did not return any result";
            $answer['news'] = 'Nothing is happening right now. Check later!';
        } 

        $music = false;
        if($intent == "music" || $intent == "next song"){
                $music = true;
            }
        //Now that we have the webhook implemented all request go through it so we already
        //have the answer. Of course some refactoring is needed...

        //here we just format the news index

        if($data && !$music){
            $answer['news'] = [
                'title' => $data['news']['title'],
                'image' => $data['news']['image'],
                'body' => $data['news']['body'],
                'emotion' => $data['news']['emotions'],
                'permalink' => $data['news']['permalink'],
                'requestFrom' => 'Webapp'
            ];
        }
        if($data && $music){
            $answer['music'] = $data['music'];
        }
        //here we bypass    
        if(!$webapp){
            //with Webhook action is false...

            if($action == "show.news" || ($webhookUsed == "true" && $music !== true)){
                    $local = false;
                    if($adjective == "local" || $adjective == "swiss"){
                        $response = $this->feed($subject);
                        $allNews = json_decode($response->getContent(), true);
                        $answer['news'] = $allNews['item'];
                        $local = true;
                                
                        if($intent == "More info") {
                            $answer['news'] = $allNews['next'];
                        }                    
                    }
                    if(!$local && $action != "request.news"){
                        $response = $this->getNews($subject);
                        $allNews = json_decode($response->getContent(), true);
                        $answer['news'] = $allNews['item'];

                        if($intent == "More info") {
                            $answer['news'] = $allNews['next'];
                        }
                    }

            }
            //the domain using this action is not free
            if($action == "news.search"){
                //
            }

            if($action == "play.music" || ($webhookUsed && $music)){
                if(!empty($subject)){
                    $songs = $this->spotify($subject);
                } elseif (!empty($adjective)) {
                    $songs = $this->spotify($adjective);
                } else {
                    $songs = $this->spotify('opera');
                }
                
                if($songs != null){ 
                    $answer['music'] = $songs['playing'];
                    if($intent == "next song") {
                        $answer['music'] = $songs['next'];
                    }
                }    
            }
            //the domain using this action is not free
            if($action == "wisdom.unknown"){
                    $answer['speech'] = "Sorry it took me a long time and I did not find any related music, but meanwhile I found this:";
                    $songs = $this->spotify('opera');
                    $answer['music'] = $songs['playing'];
                    if($intent == "next song") {
                        $answer['music'] = $songs['next'];
                    }
            }
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

    public function feed($rssFeed) {

        $url = '';

        if($rssFeed == "news" ) { $url = "http://www.blick.ch/news/rss.xml";}
        if($rssFeed == "swiss" || $rssFeed == "Switzerland" || $rssFeed == "switzerland" ) {$url = "http://www.blick.ch/news/schweiz/rss.xml";}
        if($rssFeed == "basel" || $rssFeed == "Basel" ) {$url = "http://www.blick.ch/news/schweiz/basel/rss.xml";}
        if($rssFeed == "bern" || $rssFeed == "Bern" ) {$url = "http://www.blick.ch/news/schweiz/bern/rss.xml";}
        if($rssFeed == "graubuenden" ) {$url = "http://www.blick.ch/news/schweiz/graubuenden/rss.xml";}
        if($rssFeed == "mittelland" ) {$url = "http://www.blick.ch/news/schweiz/mittelland/rss.xml";}
        if($rssFeed == "ostschweiz" ) {$url = "http://www.blick.ch/news/schweiz/ostschweiz/rss.xml";}
        if($rssFeed == "tessin" || $rssFeed == "Ticino") {$url = "http://www.blick.ch/news/schweiz/tessin/rss.xml";}
        if($rssFeed == "westschweiz" ) {$url = "http://www.blick.ch/news/schweiz/westschweiz/rss.xml";}
        if($rssFeed == "zentralschweiz" ) {$url = "http://www.blick.ch/news/schweiz/zentralschweiz/rss.xml";}
        if($rssFeed == "zurich" || $rssFeed == "Zurich" ) {$url = "http://www.blick.ch/news/schweiz/zuerich/rss.xml";}
        if($rssFeed == "foreign" ) {$url = "http://www.blick.ch/news/ausland/rss.xml";}
        if($rssFeed == "economy" ) {$url = "http://www.blick.ch/news/wirtschaft/rss.xml";}
        if($rssFeed == "sport" ) {$url = "http://www.blick.ch/sport/rss.xml";}
        if($rssFeed == "football" ) {$url = "http://www.blick.ch/sport/fussball/rss.xml";}
        if($rssFeed == "hockey" ) {$url = "http://www.blick.ch/sport/eishockey/rss.xml";}
        if($rssFeed == "ski" ) {$url = "http://www.blick.ch/sport/ski/rss.xml";}
        if($rssFeed == "tennis" ) {$url = "http://www.blick.ch/sport/tennis/rss.xml";}
        if($rssFeed == "formula 1" ) {$url = "http://www.blick.ch/sport/formel1/rss.xml";}
        if($rssFeed == "bike" ) {$url = "http://www.blick.ch/sport/rad/rss.xml";}
        if($rssFeed == "people" ) {$url = "http://www.blick.ch/people-tv/rss.xml";}
        if($rssFeed == "life" ) {$url = "http://www.blick.ch/life/rss";}
        if($rssFeed == "fashion" ) {$url = "http://www.blick.ch/life/mode/rss.xml";}
        if($rssFeed == "digital" ) {$url = "http://www.blick.ch/life/digital/rss.xml";}

        $feed = new SimplePie();
        $feed->set_feed_url($url);
        $feed->enable_cache(false);
        $success = $feed->init();
           
        if(!$success){
            $item['title'] = "No news found";
            $items['body'] = '';
            $items['date'] = '';
            $items['permalink'] = null;
           return Response::json([ 
                'item'   => $item,
                'message' => 'No news found :('
            ], 200);  
        }       


        if($success){  
            $feed->handle_content_type(); 
            $items = $feed->get_items();
            $item = head($items);
                $parsed['source'] = "Blick.ch";
                $parsed['title'] = $item->get_title();
                $parsed['body'] = $item->get_content();
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $parsed['image'] = str_replace('"', '', $image[2]);
                $parsed['date'] = $item->get_date('j M Y, g:i a');
                $parsed['emotions'] = false; 
                if ($item->get_permalink()){
                    $parsed['permalink'] = $item->get_permalink();
                }
            $item = array_pull($items, 1); 
                $next['source'] = "Blick.ch";
                $next['title'] = $item->get_title();
                $next['body'] = $item->get_content();
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $next['image'] = str_replace('"', '', $image[2]);
                $next['date'] = $item->get_date('j M Y, g:i a');
                $next['emotions'] = false; 
                if ($item->get_permalink()){
                    $next['permalink'] = $item->get_permalink();
                }
            $item = array_pull($items, 2); 
                $next2['source'] = "Blick.ch";
                $next2['title'] = $item->get_title();
                $next2['body'] = $item->get_content();
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $next2['image'] = str_replace('"', '', $image[2]);
                $next2['date'] = $item->get_date('j M Y, g:i a');
                $next2['emotions'] = false; 
                if ($item->get_permalink()){
                    $next2['permalink'] = $item->get_permalink();
                }  
        }
        return Response::json([
                'item'  => $parsed,
                'next' => $next,
                'last' => $next2,
                'message'   => "Here is the latest news"
            ], 200);
    }

        public function getNews($query){

        $client = new Client();
        $response = $client->request('GET','https://api.cognitive.microsoft.com/bing/v5.0/news/search?q='.$query.'&count=3&offset=0&mkt=en-us&safeSearch=Moderate&originalImg=1', array(
            'headers' => array(
                'Ocp-Apim-Subscription-Key' => env('BING_SEARCH')
            )
            ));

        $items = json_decode($response->getBody(), true);

        $item = head($items['value']);
        $parsed['title'] = $item['name'];
        $parsed['image'] = $item['image']['thumbnail']['contentUrl'];
        //Call to Alchemy to get the full body and the emotions
        $emotion = $client->request('POST', url('api/emotion'), array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => array(
                "url" => $item['url']
            )
            ));
        $results = json_decode($emotion->getBody(),true);
        $parsed['body'] = $results['text'];
        $parsed['emotions'] = $results['docEmotions']; 
        $parsed['permalink'] = $results['url'];

        $item = array_pull($items['value'], 1);
        $next['title'] = $item['name'];
        $next['image'] = $item['image']['thumbnail']['contentUrl'];
        //Call to Alchemy to get the full body and the emotions
        $emotion = $client->request('POST', url('api/emotion'), array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => array(
                "url" => $item['url']
            )
            ));
        $results = json_decode($emotion->getBody(),true);
        $next['body'] = $results['text'];
        $next['emotions'] = $results['docEmotions']; 
        $next['permalink'] = $results['url'];
        //$item = array_pull($items, 1); 

        //$item = array_pull($items, 2); 
        return Response::json([
                'item'  => $parsed,
                'next' => $next,
                //'last' => $next2,
                'message'   => "Here is the latest news"
            ], 200);


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
        $username = 'samuel.pouyt';
        //$username = "tanushechka.krasotushechka";

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
