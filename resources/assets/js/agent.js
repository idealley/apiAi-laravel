var Vue = require('vue')
//var validator = require('vue-validator')
var resource = require('vue-resource')
//var typeahead = require('vue-typeahead')

//Vue.use(validator)
Vue.use(resource)
//Vue.use(typeahead)



var token = $('#token').attr('value');
Vue.http.headers.common['X-CSRF-TOKEN'] = token;

var apiAi, apiAiTts;
var app, text, dialogue, response, start, stop, results;
var SERVER_PROTO, SERVER_DOMAIN, SERVER_PORT, ACCESS_TOKEN;

SERVER_PROTO = 'wss';
SERVER_DOMAIN = 'api.api.ai';
SERVER_PORT = '4435';
ACCESS_TOKEN = '77d16fe0cd7b4aaba3ced96f48590d46';

var sessionId = '1234'
var config = {
	server: SERVER_PROTO + '://' + SERVER_DOMAIN + ':' + SERVER_PORT + '/api/ws/query',
	token: ACCESS_TOKEN,// Use Client access token there (see agent keys).
	sessionId: sessionId,
	lang: 'en',
	onInit: function () {
	    console.log("> ON INIT use config");
	}
};    


new Vue({
	el:'#agent',

	data: {
		request: '',

	},

	ready: function(){
		//this.fetchInvitedReviewers();
				/**
			 * Demo application
			 */

		/*
		todo it breaks the search...
		if(!this.quantity === true && !submitted) {
				this.$set('notAllowed', true)
			}
		*/	
	},
	methods: {
		fetchWelcomeMessage: function() {
			this.$http.get('api', function(response) {
			//We do something
			//this.$set('reviewers', response.reviewers);

			});
		},

		onSubmitForm: function(e){
			e.preventDefault();

			//var request = this.request;
			
			//reset input values
			//this.request = {''};
			
			
			//sent post ajax request
			/*	this.$http.post(
					'api/rss/'+ request, 
					function(response){
						this.$set('news', response.items);
						this.$set('message', response.message);

					}).error(function(response){
						this.$set('error',response.message);
					})

			*/


		}

		
		
	}
  	

});

