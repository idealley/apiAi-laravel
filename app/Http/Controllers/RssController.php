<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimplePie;

use App\Http\Requests;
use Illuminate\Support\Facades\Response;

class RssController extends Controller
{
    
    public function feed($rssFeed) {

        $url = '';

        if($rssFeed == "news" ) { $url = "http://www.blick.ch/news/rss.xml";}
        if($rssFeed == "swiss" ) {$url = "http://www.blick.ch/news/schweiz/rss.xml";}
        if($rssFeed == "basel" ) {$url = "http://www.blick.ch/news/schweiz/basel/rss.xml";}
        if($rssFeed == "bern" ) {$url = "http://www.blick.ch/news/schweiz/bern/rss.xml";}
        if($rssFeed == "graubuenden" ) {$url = "http://www.blick.ch/news/schweiz/graubuenden/rss.xml";}
        if($rssFeed == "mittelland" ) {$url = "http://www.blick.ch/news/schweiz/mittelland/rss.xml";}
        if($rssFeed == "ostschweiz" ) {$url = "http://www.blick.ch/news/schweiz/ostschweiz/rss.xml";}
        if($rssFeed == "tessin" ) {$url = "http://www.blick.ch/news/schweiz/tessin/rss.xml";}
        if($rssFeed == "westschweiz" ) {$url = "http://www.blick.ch/news/schweiz/westschweiz/rss.xml";}
        if($rssFeed == "zentralschweiz" ) {$url = "http://www.blick.ch/news/schweiz/zentralschweiz/rss.xml";}
        if($rssFeed == "zurich" ) {$url = "http://www.blick.ch/news/schweiz/zuerich/rss.xml";}
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
            $items[0]['title'] = "No news found";
            $items[0]['body'] = '';
            $items[0]['date'] = '';
            $items[0]['permalink'] = null;
           return Response::json([ 
                'items'   => $items,
                'message' => 'No news found :('
            ], 200);  
        }       


        if($success){  
            $feed->handle_content_type();  
        	foreach($feed->get_items() as $key => $item){
        		$items[$key]['title'] = $item->get_title();
        		$items[$key]['body'] = strip_tags($item->get_content());
                preg_match('/(src)=("[^"]*")/i',$item->get_content(), $image);
                $items[$key]['image'] = str_replace('"', '', $image[2]);
        		$items[$key]['date'] = $item->get_date('j M Y, g:i a');
        		if ($item->get_permalink()){
        			$items[$key]['permalink'] = $item->get_permalink();
        		}
        	}
        }
        return Response::json([
                'items'  => $items,
                'message'   => "Here are the news"
            ], 200);
    }
}
