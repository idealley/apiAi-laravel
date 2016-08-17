<?php 

namespace App\Extensions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

use App\Extensions\Helper;

class GoogleTranslate {
	public function getTranslation($query, $target = "en", $source = "de"){
		$client = new Client();
		$response = $client->request('GET','https://www.googleapis.com/language/translate/v2?q='.$query.'&target='.$target.'&source='.$source.'&key='.env('GOOGLE_TRANSLATE') );
		
		$translation = json_decode($response->getBody(), true);

		return $translation['data']['translations'][0]['translatedText'];	
	}
}