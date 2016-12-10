<?php

// Initialize Session



//$_E107['no_buffer'] = true;
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['no_maintenance'] = true;

// error_reporting(E_ALL);
define('e_MINIMAL', true);
require_once("../../class2.php");
//session_cache_limiter(false);


/*
session_cache_limiter(false);
//header('Content-Encoding: none');

if(session_id() == '')
{
	session_start();
}
*/



@include(__DIR__ . '/vendor/autoload.php');


\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// Setup CORS
 $app->response['Access-Control-Allow-Origin'] = '*';

// Inject Session object into app
if($namespace = $app->request->params('namespace'))
{
	$app->session = defined("e107_INIT") ? e107::getSession('visualcaptcha_' . $namespace) :  new \visualCaptcha\Session('visualcaptcha_' . $namespace);
//	$app->session = e107::getSession('visualcaptcha_' . $namespace); //
}
else
{
	$app->session = defined("e107_INIT") ? e107::getSession('visualcaptcha') : new \visualCaptcha\Session();
//	$app->session = e107::getSession('visualcaptcha'); //;
}


// Populates captcha data into session object
// -----------------------------------------------------------------------------
// @param howmany is required, the number of images to generate
$app->get('/start/:howmany', function ($howMany) use ($app)
{
	$captcha = new \visualCaptcha\Captcha($app->session, __DIR__.'/languages/English'); //todo calculate current e107 language.
	$captcha->generate($howMany);

	$app->response['Content-Type'] = 'application/json';

	echo json_encode($captcha->getFrontendData());
});

// Streams captcha images from disk
// -----------------------------------------------------------------------------
// @param index is required, the index of the image you wish to get
$app->get('/image/:index', function ($index) use ($app)
{

	$captcha = new \visualCaptcha\Captcha($app->session, __DIR__); // e107_plugins/visualcaptcha/images


	if(defined("e107_INIT"))
	{
		list($index,$tmp) = explode("?", $index); // Fix for class2.php inclusion
	}

	$index = intval($index);
	
	if(!$captcha->streamImage(
		$app->response,
		$index,
		$app->request->params('retina')
	))
	{
		$app->pass();
	}
});

// Streams captcha audio from disk
// -----------------------------------------------------------------------------
// @param type is optional and defaults to 'mp3', but can also be 'ogg'
$app->get('/audio(/:type)', function ($type = 'mp3') use ($app)
{
	$captcha = new \visualCaptcha\Captcha($app->session, __DIR__.'/languages/English'); //todo calculate current e107 language.

	if(!$captcha->streamAudio($app->response, $type))
	{
		$app->pass();
	}
});


while (@ob_end_clean());

$app->run();
