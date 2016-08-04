<?php

namespace App\Extensions;


class SpotifyHelper {

    public function getSong($query, $offset){
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
}    