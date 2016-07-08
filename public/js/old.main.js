/**
 * Demo application
 */

var app, text, dialogue, response, start, stop;
var SERVER_PROTO, SERVER_DOMAIN, SERVER_PORT, ACCESS_TOKEN;

SERVER_PROTO = 'wss';
SERVER_DOMAIN = 'api.api.ai';
SERVER_PORT = '4435';
ACCESS_TOKEN = '77d16fe0cd7b4aaba3ced96f48590d46';

window.onload = function () {
    text = $$('text');
    dialogue = $$('dialogue');
    response = $$('response');
    start = $$('start');
    stop = $$('stop');
    //$$('server').innerHTML = SERVER_DOMAIN;
    //$$('token').innerHTML = ACCESS_TOKEN;

    app = new App();
};

function App() {
    var apiAi, apiAiTts;
    var isListening = false;
    var sessionId = _generateId(32);

    this.start = function () {
        //start.className += ' hidden';
        //stop.className = stop.className.replace('hidden', '');

        _start();
    };
    this.stop = function () {
        _stop();

        stop.className += ' hidden';
        start.className = start.className.replace('hidden', '');
    };
//creates a loop
    this.toggle = function() {
        if(start.className == ' toggle'){
            start.className = start.className.replace('toggle', '');
            _stop();
        }


        start.className += ' toggle';  
        _start();


    }

    this.sendJson = function () {
        var query = text.value,
        //var query = 'hello',
            queryJson = {
                "v": "20150910",
                "query": query,
                "timezone": "GMT+6",
                "lang": "en",
                //"contexts" : ["weather", "local"],
                "sessionId": sessionId
            };

        console.log('sendJson', queryJson);

        apiAi.sendJson(queryJson);
    };

    this.open = function () {
        console.log('open');
        apiAi.open();
    };

    this.close = function () {
        console.log('close');
        apiAi.close();
    };

    this.clean = function () {
        dialogue.innerHTML = '';
    };

    _init();


    function _init() {
        console.log('init');

        /**
         * You can use configuration object to set properties and handlers.
         */
        var config = {
            server: SERVER_PROTO + '://' + SERVER_DOMAIN + ':' + SERVER_PORT + '/api/ws/query',
            token: ACCESS_TOKEN,// Use Client access token there (see agent keys).
            sessionId: sessionId,
            lang: 'en',
            onInit: function () {
                console.log("> ON INIT use config");
            }
        };
        apiAi = new ApiAi(config);

        /**
         * Also you can set properties and handlers directly.
         */
        apiAi.sessionId = '1234';

        apiAi.onInit = function () {
            console.log("> ON INIT use direct assignment property");
            apiAi.open();
        };

        apiAi.onStartListening = function () {
            console.log("> ON START LISTENING");
        };

        apiAi.onStopListening = function () {
            console.log("> ON STOP LISTENING");
        };

        apiAi.onOpen = function () {
            console.log("> ON OPEN SESSION");

            /**
             * You can send json through websocet.
             * For example to initialise dialog if you have appropriate intent.
             */
            apiAi.sendJson({
                "v": "20150512",
                "query": "hello",
                "timezone": "GMT+6",
                "lang": "en",
                //"contexts" : ["weather", "local"],
                "sessionId": sessionId
            });

        };

        apiAi.onClose = function () {
            console.log("> ON CLOSE");
            apiAi.close();
        };

        /**
         * Reuslt handler
         */
        apiAi.onResults = function (data) {
            console.log("> ON RESULT", data);

            var status = data.status,
                code,
                speech;

            if (!(status && (code = status.code) && isFinite(parseFloat(code)) && code < 300 && code > 199)) {
                //dialogue.innerHTML = JSON.stringify(status);
                return;
            }


            if(data.result.parameters.news){

            var param = data.result.parameters.news;
            var item = '';
                $.ajax({
                    type: "GET",
                    url: "api/rss/"+param,

                    success: function(item) {

                        console.log(item.item.title);
    
                                newsToRead = '';  
                                newsToRead = item.item.title + item.item.body;
                                apiAiTts.tts(newsToRead, undefined, 'de-DE');


                                var image =     '<div class="card"><div class="card-main"><div class="card-img"><img alt="alt text" src="' +
                                                item.item.image
                                                +'" style="width: 100%;"></div>';

                                var content =   '<div class="card-inner"><h3>'+
                                                item.item.title
                                                +'</h3><p>'+
                                                item.item.body
                                                +'</p><p>'+
                                                item.item.date
                                                +'</p></div>';
                                
                                if(!item.item.link) {
                                    var action = '</div></div>';
                                }
                                var action =    '<div class="card-action"><a class="btn btn-flat waves-attach waves-effect" href="'+
                                                item.item.permalink
                                                +'"><span class="icon">check</span>read more...</a></div></div></div> ';
                      
                                var news = image + content + action;

                                $('#news').html(news);

                    }

                        
                });

            }

            speech = data.result.speech;
            // Use Text To Speech service to play text.
            apiAiTts.tts(speech, undefined, 'en-US');
            /*
            dialogue.innerHTML += ('user : ' + data.result.resolvedQuery +
            '\napi  : ' + data.result.speech +
            '\nresponse  : ' + data.result.parameters.news 
            + '\n\n');
            response.innerHTML = JSON.stringify(data, null, 2);
            */
            text.innerHTML = '';// clean input
            
        };

        apiAi.onError = function (code, data) {
            apiAi.close();
            console.log("> ON ERROR", code, data);
            //if (data && data.indexOf('No live audio input in this browser') >= 0) {}
        };

        apiAi.onEvent = function (code, data) {
            console.log("> ON EVENT", code, data);
        };

        /**
         * You have to invoke init() method explicitly to decide when ask permission to use microphone.
         */
        apiAi.init();

        /**
         * Initialise Text To Speech service for playing text.
         */
        apiAiTts = new TTS(SERVER_DOMAIN, ACCESS_TOKEN, undefined, 'en-US');

    }

    function _start() {
        console.log('start');

        isListening = true;
        apiAi.startListening();
    }

    function _stop() {
        console.log('stop');

        apiAi.stopListening();
        isListening = false;
    }

    function _generateId(length) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < length; i++) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }
        return text;
    }

}


function $$(id) {
    return document.getElementById(id);
}
