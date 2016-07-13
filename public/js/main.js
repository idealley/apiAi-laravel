function send(){
    var text = $("#query").val();

    $.post(
        'api/api-ai',
        {
            'query': text
        },
        function(item) {
            console.log('>>>>>>> the item object (bellow):');
            console.log(item);

            if(item.action == "smalltalk.greetings" || item.action == "news.search" || item.action == "wisdom.unknown"){
                responsiveVoice.speak(item.speech, 'UK English Female');
            }
            
            //If no emotion it is German... Just because Watson does not understand yet German. No hint here.
            if(!item.news.emotion && item.action != "smalltalk.greetings"){
                responsiveVoice.speak('Here is what I found', 'UK English Female', {onend: speakGermanNews});
            }

            if(item.news.emotion){
                responsiveVoice.speak("According to Watson, the main emotion of this article is:" + item.news.emotion + ': ' + item.news.title + ' ' +item.news.body, 'UK English Female');
            }

            if(item.action == "play.music"){
                responsiveVoice.speak(item.speech, 'UK English Female', {onend: playMusic});
            }
            
            function speakGermanNews(){ //no need to pass the object...
                if(item.news.title){
                    responsiveVoice.speak(item.news.title + item.news.body, "Deutsch Female");
                } else {
                    if(item.action !== "smalltalk.greetings"){           
                        responsiveVoice.speak("Sorry, No news found");
                    }
                }
            }
            var emotion = '';
            if(item.news.emotion){
                emotion = '<h4>Watson has found that this news reflects the following emotion: <b>'+item.news.emotion+'</b></h4>'
            }

            if(item.action == "show.news") {
                var image =     '<div class="card"><div class="card-main"><div class="card-img"><img alt="alt text" src="' +
                                        item.news.image
                                        +'" style="width: 100%;"></div>';

                var content =   '<div class="card-inner">'+ emotion +'<h3>'+
                                        item.news.title
                                        +'</h3><p>'+
                                        item.news.body
                                        +'</p></div>';
                                    
                if(!item.news.link) {
                    var action = '</div></div>';
                }
                var action =    '<div class="card-action"><a class="btn btn-flat waves-attach waves-effect" href="'+
                                        item.news.permalink
                                        +'"><span class="icon">check</span>read more...</a></div></div></div> ';
                          
                var news = image + content + action;

                $('#news').html(news);
            }

            if(item.action == "news.search"){
                var content =   '<div class="card-inner"><p>'+
                                        item.speech
                                        +'</p></div>';
                $('#news').html(content);
            }
            function playMusic(){
                if(item.action == "play.music" || item.action == "wisdom.unknown"){
                    var player =   '<div class="card-main"><div style="text-align:center;" class="card-inner"><audio controls autoplay>'+
                                    '<source src="'+item.music+'" type="audio/mpeg">'+
                                    '</audio></div></div>';
                    $('#news').html(player);
                }
            }

        }
    );
}