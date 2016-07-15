<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta id="token" name="token" value="{{ csrf_token() }}"></meta>
		<title>News Agent</title>
		<meta name="description" content="Animated icons powered by the motion graphics library mo.js by Oleg Solomka" />
		<meta name="keywords" content="animated icons, svg, webfont, mo.js, facebook, thumbs up, animation, web design" />
		<meta name="author" content="Codrops" />
		<link rel="shortcut icon" href="favicon.ico">
		<link href='https://fonts.googleapis.com/css?family=Patrick+Hand+SC' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.5.0/css/font-awesome.min.css" />
		<link rel="stylesheet" type="text/css" href="css/normalize.css" />
		<link rel="stylesheet" type="text/css" href="css/demo.css" />
		<link rel="stylesheet" type="text/css" href="css/icons.css" />
		<link href="../css/base.min.css" rel="stylesheet">
		<link href="../css/project.min.css" rel="stylesheet">
		<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

		<script src="js/jquery.js"></script>
		 <script src="../js/responsive-voice.js"></script>
	</head>
	<body class="page-brand page-red">
		<header class="header header-transparent header-waterfall header-red ui-header">

		<a class="header-logo margin-left-no" href="/">News Agent</a>

	</header>
		<nav aria-hidden="true" class="menu" id="ui_menu" tabindex="-1">
		<div class="menu-scroll">
			<div class="menu-content">
				<ul class="nav">
					<li>
					Menu
					</li>
				</ul>
			</div>
		</div>
	</nav>
<main class="content">
		<div class="content-header ui-content-header">
			<div class="container">
				<div class="row">
					<div class="col-lg-6 col-lg-push-3 col-sm-10 col-sm-push-1">
						<h1 class="content-heading"></h1>
					</div>
				</div>
			</div>
		</div>
		<div class="container" id="agent">
			<div class="row">
				<div class="col-lg-6 col-lg-push-3 col-sm-10 col-sm-push-1">
					<section class="content-inner margin-top-no">
						<div class="card">
							<div class="card-main">
								<div class="card-inner">
									<section class="content">
										<div class="grid">
											<div class="grid__item">
										    	<div>
										        	<a class="fbtn fbtn-lg fbtn-brand waves-attach waves-circle waves-light icon" id="rec" style="font-size:2.5em;">mic
										        	</a>
										    	</div>
											</div>
											<div class="grid__item" style="margin: 1em 0;">
										    	<div>
											        <div id="toFocus" class="form-group form-group-label form-group-brand">
														<label class="floating-label" for="ui_floating_label_example_brand">Or, ask with your keyboard</label>
														<input class="form-control" id="query" type="text">
														<div class="form-help">
															<p>Use the Enter Key to validate your input</p>
														</div>
													</div>
										    	</div>
											</div>		
										</div>		
									</section>		

									<section id="news">
										
									</section>


									<section>
									<div style="display: flex">
									    <div id="dialogue" class="half-panel" style="white-space: pre-line;width: 50%"></div>
									    <div id="response" class="half-panel" style="white-space: pre;width: 50%;"></div>
									</div>
									</section>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
	</main>					

	<footer class="ui-footer">
		<div class="container">
			<p></p>
		</div>
	</footer>
	<div class="fbtn-container">
		<div class="fbtn-inner">
			<a id="toggle_play" style="display:none;" class="fbtn fbtn-lg fbtn-brand-accent waves-attach waves-circle waves-light" data-toggle="dropdown">
				<span class="fbtn-text fbtn-text-left">Play or Pause the voice reading the news!</span>
				<span class="fbtn-ori icon">pause</span>
				<span class="fbtn-sub icon">play_arrow</span>
			</a>
		</div>
	</div>
	<div id="spinner-wrapp" style="display:none;">
		<div id="spinner">
			<svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
		   		<circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>
			</svg>	
		</div>
	</div>

		<script src="/js/jquery.js"></script>
		<script src="/js/project.min.js"></script>
		<script src="/js/base.min.js"></script>
		<script src="/js/speech-recognition.js"></script>
		<script src="/js/main.js"></script>
	</body>
</html>
