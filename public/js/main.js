function send(){
    var text = $("#query").val();
    $.post(
        'api/api-ai',
        {
            'query': text
        },
        function(item) {
            console.log('>>>>>>>>>> the item object (bellow):');
            console.log(item);

            $('#spinner-wrapp').hide();
            if(item.action != "play.music" || item.action != "show.news"){  
                console.log('>>>>>>>>>> Default');
                $('#toggle_play').show();
                responsiveVoice.speak(item.speech, 'UK English Female');
            }

            var language = '';
            var emotion = '';

            if(item.news != null){
                language = item.news.language;
                emotion = item.news.emotion;
            }
            
            if(language == "german" && item.action == "show.news"){
                console.log('>>>>>>>>>> Show News (German)');
                $('#toggle_play').show();
                responsiveVoice.speak(item.speech, 'UK English Female', {onend: speakGermanNews});
            }

            if(language == "english" && item.action == "show.news"){
                console.log('>>>>>>>>>> Show News (English)');
                $('#toggle_play').show();
                responsiveVoice.speak(item.speech, 'UK English Female', {onend: speakEnglishNews});
            }

            if(item.action == "play.music"){
                console.log('>>>>>>>>>> Play Music');
                $('#toggle_play').hide();
                responsiveVoice.speak(item.speech, 'UK English Female', {onend: playMusic});
            }
            
            function speakGermanNews(){ //no need to pass the object...
                if(item.news.title){
                    responsiveVoice.speak(item.news.title, "Deutsch Female", {onend: germanNewsBody});
                } else {       
                        responsiveVoice.speak("Sorry, No news found");
                    }
            }
            
            function germanNewsBody(){
                if(item.news.body){
                    responsiveVoice.speak(item.news.body, "Deutsch Female");              
                    responsiveVoice.speak(item.news.source, 'Deutsch Female');
                } else {       
                        responsiveVoice.speak("Nothing else to read");
                }

            }

            function speakEnglishNews(){
                if(item.news.title){
                    responsiveVoice.speak("According to Watson the main emotion expressed in the article is:" + item.news.emotion + " : :" + item.news.title, 'UK English Female', {onend: englishNewsBody});
                    //console.log("English news title >>>>>>>>> " + item.news.title)
                    //responsiveVoice.speak(item.news.title, 'UK English Female', {onend: englishNewsBody});
                } else {       
                        responsiveVoice.speak("Sorry, No news found");
                }
            }

            function englishNewsBody(){
                if(emotion){
                    responsiveVoice.speak(item.news.body, 'UK English Female');
                    responsiveVoice.speak(item.news.source, 'UK English Female');
                } else {       
                    responsiveVoice.speak(item.news.body, 'UK English Female');             
                    responsiveVoice.speak(item.news.source, 'UK English Female');
                }
            }

            if(emotion){
                emotion = '<h4>According to Watson the main emotion expressed in the article is: <b>'+emotion+'</b></h4>'
            }

            if(item.action == "show.news") {
                var image =     '<div class="card"><div class="card-main"><div class="card-img"><img alt="alt text" src="' +
                                        item.news.image
                                        +'" style="width: 100%;"></div>';

                var content =   '<div class="card-inner">'+ emotion +'<h3>'+
                                        item.news.title
                                        +'</h3><p>'+
                                        item.news.body
                                        +'</p><p>Source: '+item.news.source+'</p></div>';
                                    
                if(!item.news.link) {
                    var action = '</div></div>';
                }
                var action =    '<div class="card-action"><a class="btn btn-flat waves-attach waves-effect" href="'+
                                        item.news.link
                                        +'"><span class="icon">link</span>read more...</a></div></div></div> ';
                          
                var news = image + content + action;

                $('#news').html(news);
            }

            if(item.action != "show.news"){
                var content =   '<div class="card-inner"><p>'+
                                        item.speech
                                        +'</p></div>';
                $('#news').html(content);
            }
            function playMusic(){       

                    var image =     '<div class="card"><div class="card-main"><div class="card-img"><img alt="alt text" src="' +
                                        item.music.image
                                        +'" style="width: 100%;"></div>';

                    var content =   '<div style="text-align:center;" class="card-inner"><audio controls autoplay>'+
                                        '<source src="'+item.music.url+'" type="audio/mpeg">'+
                                        '</audio></div>';
                                    
                    if(!item.music.full) {
                            var action = '</div></div>';
                        }
                        var action =    '<div class="card-action"><a target="_blank" class="btn btn-flat waves-attach waves-effect" href="'+
                                                item.music.full
                                                +'"><span class="icon">link</span>Listent to the full song on Spotify</a></div></div></div> ';
                          
                var player = image + content + action;                                   
                    $('#news').html(player);
                
            }

        }
    );
}
