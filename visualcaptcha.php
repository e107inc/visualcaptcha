<?php

@include(__DIR__ . '/vendor/autoload.php');

// Initialize Session.
session_cache_limiter(false);

if(session_id() == '')
{
	session_start();
}

require_once("../../class2.php");

$form = e107::getForm();
$tp = e107::getParser();
$msg = e107::getMessage();


$captchaSettings = array();

$captchaSettings[] = array(
	'namespace' => 'captcha_test_form',
	'formID'    => 'captcha-test-form',
	'imgPath'   => e_PLUGIN . 'visualcaptcha/img/',
	'url'       => e_PLUGIN . 'visualcaptcha/app.php',
	'imgCount'  => 4,
	'language'  => array(
		'accessibilityAlt'         => 'Sound icon',
		'accessibilityTitle'       => 'Accessibility option: listen to a question and answer it!',
		'accessibilityDescription' => 'Type below the <strong>answer</strong> to what you hear. Numbers or words:',
		'explanation'              => 'Click or touch the <strong>ANSWER</strong>',
		'refreshAlt'               => 'Refresh/reload icon',
		'refreshTitle'             => 'Refresh/reload: get new images and accessibility option!',
	)
);

e107::js('settings', array('visualcaptcha' => $captchaSettings));
e107::js('visualcaptcha', 'js/visualcaptcha.bootstrap.js');

if(e_DEBUG)
{
	e107::css('visualcaptcha', 'css/visualcaptcha.src.css');
	e107::js('visualcaptcha', 'js/visualcaptcha.jquery.src.js');
}
else
{
	e107::css('visualcaptcha', 'css/visualcaptcha.css');
	e107::js('visualcaptcha', 'js/visualcaptcha.jquery.js');
}


require_once(HEADERF);

if(!empty($_POST['namespace']))
{
	$valid = validateCaptcha();

	if(!$valid)
	{
		$msg->addError('Validation failed.');
	}
	else
	{
		$msg->addSuccess('Validation success.');
	}

	echo $msg->render();
}

echo $form->open('captcha_test_form', 'POST', e_SELF);
echo '<div class="e-visual-captcha"></div>';
echo $form->submit('submit', 'Submit');
echo $form->close();

require_once(FOOTERF);
exit;

/**
 * @return bool
 */
function validateCaptcha()
{
	$captchaValid = false;

	if(!empty($_POST['namespace']))
	{
		$session = new \visualCaptcha\Session('visualcaptcha_' . $_POST['namespace']);
	}
	else
	{
		$session = new \visualCaptcha\Session();
	}

	$captcha = new \visualCaptcha\Captcha($session);
	$frontendData = $captcha->getFrontendData();

	// If captcha is present, try to validate it.
	if($frontendData)
	{
		// If an image field name was submitted, try to validate it.
		if(($imageAnswer = $_POST[$frontendData['imageFieldName']]) && !empty($imageAnswer))
		{
			if($captcha->validateImage($imageAnswer))
			{
				$captchaValid = true;
			}
		}
		else
		{
			if(($audioAnswer = $_POST[$frontendData['audioFieldName']]) && !empty($audioAnswer))
			{
				if($captcha->validateAudio($audioAnswer))
				{
					$captchaValid = true;
				}
			}
		}

		// Clear current session after captcha has been validated.
		$session->clear();
	}

	return $captchaValid;
}
