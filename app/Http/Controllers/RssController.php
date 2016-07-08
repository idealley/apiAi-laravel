<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimplePie;

use App\Http\Requests;
use Illuminate\Support\Facades\Response;

use GuzzleHttp\Client;

class RssController extends Controller
{
    public function test(){

    $result = file_get_contents('http://requestb.in/usbje1us');
    dd($result);
    }
    public function webhook(Request $request){
        $results = json_decode($request->instance()->getContent(), true);
   
        $subject = $results->result->parameters->subject;
        $speech = "Here are the latest news about ".$subject;
        $allNews = $this->feed($subject);
        $news = json_decode($allNews->getContent(), true);
        $text = $news['item']['title'];

        return Response::json([
                'speech'   => $speech .' - '.$text,
                'displayText' => $speech.' - '.$text,
                'data' => $subject,
                'contextOut' => [],
                'source' => "Blick.ch",
                'status' => [ "code" => 200, "errorType" => "All good"]
            ], 200);

    }
    
    public function feed($rssFeed) {

        $url = '';

        if($rssFeed == "news" ) { $url = "http://www.blick.ch/news/rss.xml";}
        if($rssFeed == "swiss" || $rssFeed == "Switzerland" ) {$url = "http://www.blick.ch/news/schweiz/rss.xml";}
        if($rssFeed == "basel" ) {$url = "http://www.blick.ch/news/schweiz/basel/rss.xml";}
        if($rssFeed == "bern" ) {$url = "http://www.blick.ch/news/schweiz/bern/rss.xml";}
        if($rssFeed == "graubuenden" ) {$url = "http://www.blick.ch/news/schweiz/graubuenden/rss.xml";}
        if($rssFeed == "mittelland" ) {$url = "http://www.blick.ch/news/schweiz/mittelland/rss.xml";}
        if($rssFeed == "ostschweiz" ) {$url = "http://www.blick.ch/news/schweiz/ostschweiz/rss.xml";}
        if($rssFeed == "tessin" ) {$url = "http://www.blick.ch/news/schweiz/tessin/rss.xml";}
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
        		$parsed['title'] = $item->get_title();
        		$parsed['body'] = $this->truncate(strip_tags($item->get_content()));
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $parsed['image'] = str_replace('"', '', $image[2]);
        		$parsed['date'] = $item->get_date('j M Y, g:i a');
        		if ($item->get_permalink()){
        			$parsed['permalink'] = $item->get_permalink();
        		}
            $item = array_pull($items, 1); 
                $next['title'] = $item->get_title();
                $next['body'] = $this->truncate(strip_tags($item->get_content()));
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $next['image'] = str_replace('"', '', $image[2]);
                $next['date'] = $item->get_date('j M Y, g:i a');
                if ($item->get_permalink()){
                    $next['permalink'] = $item->get_permalink();
                }
            $item = array_pull($items, 2); 
                $next2['title'] = $item->get_title();
                $next2['body'] = $this->truncate(strip_tags($item->get_content()));
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $next2['image'] = str_replace('"', '', $image[2]);
                $next2['date'] = $item->get_date('j M Y, g:i a');
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

        public function truncate($string, $length=500, $append="&hellip;"){
        $string = trim($string);

        if(strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0]. $append;
        }

        return $string;

    }

    public function apiAi(Request $request){

        $client = new Client();

        $apiai_key = env('API_AI_ACCESS_TOKEN');
        $apiai_subscription_key = env('API_AI_DEV_TOKEN');

        $query = $request->input('query');

        //$query = 'Is there any music about it?';
        //$query = 'Next song';
        //$query = 'Any Pop song?';

        $response = $client->post('https://api.api.ai/v1/query', array(
            'headers' => array(
                'Authorization' => "Bearer {$apiai_key}",
                'ocp-apim-subscription-key' => $apiai_subscription_key,
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => array(
                "query" => $query,
                "lang" => "en"
            )
            ));

        $results = $response->json();

        //to see the API.AI response during dev in JSON format
        //return $results; 

        //setting defaults
        $action = isset($results['result']['action']) ? $results['result']['action'] : false;
        $intent = isset($results['result']['metadata']['intentName']) ? $results['result']['metadata']['intentName'] : false;
        $adjective = isset($results['result']['parameters']['adjective']) ? $results['result']['parameters']['adjective'] : false;
        $speech =  $results['result']['speech'];
        $subject = isset($results['result']['parameters']['subject']) ? $results['result']['parameters']['subject'] : false;
        $contexts =  $results['result']['metadata']['contexts']; // array
        $resolvedQuery = isset($results['result']['resolvedQuery']) ? $results['result']['resolvedQuery'] : false;
        
        //start formating the response to the app
        $answer['speech'] = $speech;

        If(!$action && $speech == '' && !$subject){
            $answer['speech'] = "Sorry, ".$resolvedQuery." did not return any result";
        } 

       /* if($results['result']['action'] == "request.news" && 
            (isset($results['result']['parameters']['subject']) 
            && isset($results['result']['parameters']['adjective'])
            ))
        {
            $answer['speech'] = 'Here are the latest '.
                $results['result']['parameters']['adjective'].' '.
                $results['result']['parameters']['subject'];
                
            $response = $this->feed('zurich'); //we could use Google Map api here...
            $news = json_decode($response->getContent(), true);
            $answer['adjective'] = $results['result']['parameters']['adjective'];
        } */

        if($action == "show.news"){
                $response = $this->feed($subject);
                $allNews = json_decode($response->getContent(), true);
                $news = $allNews['item'];
                if($intent == "More info") {
                    $news = $allNews['next'];
                }
        }

        if($action == "news.search"){
            //
        }

        if($action == "play.music"){
            $songs = $this->spotify($subject);
            if($songs != null){ 
                $music = $songs['playing'];
                if($intent == "next song") {
                    $music = $songs['next'];
                }
            }    
        }

        if($action == "wisdom.unknown"){
                $answer['speech'] = "Sorry it took me a long time and I did not find any related music, but meanwhile I found this:";
                $songs = $this->spotify('opera');
                $music = $songs['playing'];
                if($intent == "next song") {
                    $music = $songs['next'];
                }
        } 
 
        return Response::json([
                'news'  => isset($news) ? $news : null,
                'music' => isset($music) ? $music : null,
                'speech'   => $answer['speech'],
                'action' => $action,
                'subject' => $subject,
                'contexts' => $contexts,
                'intent' => $intent,
                'adjective' => $adjective
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
}
