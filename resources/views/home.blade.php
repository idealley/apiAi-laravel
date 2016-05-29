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
	</head>
	<body class="page-brand">
		<header class="header header-transparent header-waterfall ui-header">
		<ul class="nav nav-list pull-left">
			<li>
				<a data-toggle="menu" href="#ui_menu">
					<span class="icon icon-lg">menu</span>
				</a>
			</li>
		</ul>
		<a class="header-logo margin-left-no" href="index.html">News Agent</a>

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
												<button type="button" onclick="app.start()" id="start" class="icobutton icobutton--microphone">
													<span class="fa fa-microphone"></span>
												</button>
												<!-- We need to change the animation -->
												
											</div>
											<h4 style="text-align:center; margin:2em auto 0;">Or use your keyboard...</h4>
											<div class="grid__item" style="margin-top:0;">


													<div class="form-group form-group-label form-group-brand">
														<input 
														id="text"
														class="form-control" 
														id="ui_floating_label_example_brand" 
														type="text"
														>
														<button onclick="app.sendJson()" id="submit" class="btn btn-flat btn-brand">Interact</button>
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
			<a class="fbtn fbtn-lg fbtn-brand-accent waves-attach waves-circle waves-light" data-toggle="dropdown"><span class="fbtn-text fbtn-text-left">Links</span><span class="fbtn-ori icon">apps</span><span class="fbtn-sub icon">close</span></a>
			<div class="fbtn-dropup">
				<a class="fbtn waves-attach waves-circle" href="https://github.com/Daemonite/material" target="_blank"><span class="fbtn-text fbtn-text-left">Fork me on GitHub</span><span class="icon">code</span></a>
				<a class="fbtn fbtn-brand waves-attach waves-circle waves-light" href="https://twitter.com/daemonites" target="_blank"><span class="fbtn-text fbtn-text-left">Follow Daemon on Twitter</span><span class="icon">share</span></a>
				<a class="fbtn fbtn-green waves-attach waves-circle" href="http://www.daemon.com.au/" target="_blank"><span class="fbtn-text fbtn-text-left">Visit Daemon Website</span><span class="icon">link</span></a>
			</div>
		</div>
	</div>

		<script src="js/jquery.js"></script>
	<script type="text/javascript" src="js/api.ai.min.js"></script>
	<script type="text/javascript" src="js/resampler.js"></script>
<script type="text/javascript" src="js/recorderWorker.js"></script>
<script type="text/javascript" src="js/recorder.js"></script>
<script type="text/javascript" src="js/processors.js"></script>
<script type="text/javascript" src="js/vad.js"></script>
<script type="text/javascript" src="js/tts.js"></script>
		<script src="js/mo.min.js"></script>
		<script src="js/demo.js"></script>
		<script src="/js/base.min.js"></script>
		<script src="/js/project.min.js"></script>
		<script src="/js/main.js"></script>
	</body>
</html>
