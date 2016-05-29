# api.ai and laravel

This is a little experiment with api.ai and laravel. The assistant respond with a parameter that is used to fetch some news accordingly (rss). The latest news in the rss is being read aloud by the assistant.

It is not production ready, again it is just a quick test.

# routes

there is one GET route that returns JSON with parsed date from RSS feeds according to the param sent by the "News Agent".

# controller

there is one controller that manages the selection of the rss feed accordin the received param and that pareses the rss feeds and formats it before sending it back to the Javascript that resquested it.

# JS

the file `public/js/main.js` handels the connection to api.ai and then requests the data by doing an ajax call to the laravel route. It then handels the response to send a `tts` request to api.ai to have it reading the news and it displays all the news found in the RSS feed.  