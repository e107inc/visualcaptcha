<?php

// Initialize Session



//$_E107['no_buffer'] = true;
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['no_maintenance'] = true;

require_once("../../class2.php");



class visualcaptch_front
{

	private $session;


	function __construct()
	{
		if(!defined('e107_INIT'))
		{
			return null;
		}


		while (@ob_end_clean()); // clean out any chars or space

		if(!empty($_GET['namespace']))
		{
			$this->session = e107::getSession('visualcaptcha_' . $_GET['namespace']) ;
		}
		else
		{

			$this->session = e107::getSession('visualcaptcha');
		}


	//	header('Access-Control-Allow-Origin: *');

		if(strpos(e_REQUEST_URI,'/start/'))
		{
			$this->sendJSON();
		}

		if(strpos(e_REQUEST_URI,'/image/'))
		{
			$this->sendImage();
		}

		if(strpos(e_REQUEST_URI,'/audio'))
		{
			$this->sendAudio();
		}

		if(strpos(e_REQUEST_URI,'/test') && e_DEBUG === true)
		{
			$this->sendTest();
		}


		exit;

	}

	private function sendTest()
	{
		$captcha = new \visualCaptcha\Captcha($this->session, __DIR__.'/languages/English'); //todo replace with e_LANGUAGE

		$captcha->generate(5);
		//print_a($captcha);

		$opts = $captcha->getImageOptions();

		$data = $captcha->getImageOptionAtIndex(3);

		print_a($opts);
		echo "Index at 3: ".print_a($data,true);


	}

	function sendJSON()
	{
		$this->session->clear();
		$vpref = e107::pref('visualcaptcha');
		$captcha = new \visualCaptcha\Captcha($this->session, __DIR__.'/languages/English'); //todo replace with e_LANGUAGE
		$amt = vartrue($vpref['imgCount'],5);
		$captcha->generate($amt);
		header( 'Content-Type: application/json' );
		$text = json_encode($captcha->getFrontendData());
		header( 'Content-Length: ' . strlen($text) );
		echo $text;
		// print_r($this->session);
	}


	function sendImage()
	{
		$captcha = new \visualCaptcha\Captcha($this->session, __DIR__); // e107_plugins/visualcaptcha/images

		list($index,$tmp) = explode("?", str_replace(e_SELF, '', e_REQUEST_URL));

		$index = str_replace('/image/','',$index);

	//	$index = intval($index);

		$data = $captcha->getImageOptionAtIndex($index);
		$image = __DIR__.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR.$data['path'];

		if(empty($data['path']))
		{
			echo "Not found";
			return;
		}


		header( 'Pragma: public' );
	//	header( 'Expires: 0' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' , true);
		header( 'Cache-Control: private', true );
		header( 'Content-Type: image/png' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize($image) );
		readfile( $image );


	}


	function sendAudio()
	{

		$captcha = new \visualCaptcha\Captcha($this->session, __DIR__.'/languages/English'); //todo calculate current e107 language.
		$data = $captcha->getAudioOption();
		$sep = DIRECTORY_SEPARATOR;

		$file = $data['path'];
		$audioFile = __DIR__.$sep."languages".$sep.e_LANGUAGE.$sep."audios".$sep.$file;


		$mime = $this->getMimeType($audioFile);

		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: '.$mime );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize($audioFile) );
		readfile($audioFile);

	}


	private function getMimeType($filePath)
	{
		if(function_exists('mime_content_type'))
		{
			return mime_content_type($filePath);
		}
		else
		{
			// Some PHP 5.3 builds don't have mime_content_type because it's deprecated
			if(function_exists('finfo_open'))
			{// Use finfo (right way)
				$finfo = finfo_open(FILEINFO_MIME_TYPE);

				if($mimetype = finfo_file($finfo, $filePath))
				{
					finfo_close($finfo);

					return $mimetype;
				}
			}
			elseif(function_exists('pathinfo'))
			{// Use pathinfo
				if($pathinfo = pathinfo($filePath))
				{
					$imagetypes = array('gif', 'jpg', 'png');

					if(in_array($pathinfo['extension'], $imagetypes) && getimagesize($filePath))
					{
						$size = getimagesize($filePath);

						return $size['mime'];
					}
				}
			}

			// Just figure out from a set of possibilities, if we didn't figure it out before
			$fileProperties = explode('.', $filePath);
			$extension = end($fileProperties);

			switch($extension)
			{
				case 'png':
					return 'image/png';

				case 'gif':
					return 'image/gif';

				case 'jpg':
				case 'jpeg':
					return 'image/jpeg';

				case 'mp3':
					return 'audio/mpeg3';

				case 'ogg':
					return 'audio/ogg';

				default:
					return 'application/octet-stream';
			}
		}
	}


}

/**
* todo This entire class can be removed and class2.php remain - if/when the audio functions at line 323.
*/
new visualcaptch_front();





if(!defined('e107_INIT'))
{
	session_cache_limiter(false);
	if(session_id() == '')
	{
		session_start();
	}
}



@include(__DIR__ . '/vendor/autoload.php');


\Slim\Slim::registerAutoloader();



$app = new \Slim\Slim();

while (@ob_end_clean()); //XXX clean out any chars or space - this fixes many issues.

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


/*print_a($session);
print_a($_SERVER['QUERY_STRING']);
print_a($_GET);
print_a(e_QUERY);
exit;*/
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
$app->get('/audio(/:type)', function ($type = 'mp3') use ($app) //todo this is failing
{

	$captcha = new \visualCaptcha\Captcha($app->session, __DIR__.'/languages/English'); //todo replace 'English' with e_LANGUAGE once working

	if(!$captcha->streamAudio($app->response, $type))
	{
		$app->pass();
	}
});



$app->run();
