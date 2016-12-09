<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User signup
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/signup.php $
 * $Id: signup.php 12780 2012-06-02 14:36:22Z secretr $
 * 
 */

// if(e_PAGE == 'login.php' || e_PAGE == 'admin.php')
{


	@include(__DIR__ . '/vendor/autoload.php');

	e107::getOverride()->replace('secure_image::r_image',   'visualcaptcha_module::blank');
	e107::getOverride()->replace('secure_image::renderInput', 'visualcaptcha_module::input');
	e107::getOverride()->replace('secure_image::invalidCode', 'visualcaptcha_module::invalid');
	e107::getOverride()->replace('secure_image::renderLabel', 'visualcaptcha_module::label');
	e107::getOverride()->replace('secure_image::verify_code', 'visualcaptcha_module::verify');
}


class visualcaptcha_module
{

	static function blank()
	{
		return;
	}

	static function input()
	{
		$form = e107::getForm();
		$tp = e107::getParser();
		$pref = e107::pref('visualcaptcha');

		e107::lan('visualcaptcha', false, true);

		if(e_DEBUG)
		{
			e107::library('load', 'visualcaptcha.jquery');
		}
		else
		{
			e107::library('load', 'visualcaptcha.jquery', 'minified');
		}

		$library = e107::library('info', 'visualcaptcha.jquery');

		$captchaSettings = array(
			'imgPath'   => $tp->replaceConstants($library['library_path']) . 'img/',
			'url'       => e_PLUGIN_ABS . 'visualcaptcha/app.php',
			'imgCount'  => vartrue($pref['imgCount'],5),
			'language'  => array(
				'accessibilityAlt'         => LAN_VCAPTCHA_00, // 'Sound icon',
				'accessibilityTitle'       => LAN_VCAPTCHA_01, // 'Accessibility option: listen to a question and answer it!',
				'accessibilityDescription' => $tp->lanVars(LAN_VCAPTCHA_02,'<strong>answer</strong>'), // 'Type below the <strong>answer</strong> to what you hear. Numbers or words:',
				'explanation'              => $tp->lanVars(LAN_VCAPTCHA_03,'<strong>ANSWER</strong>'), // 'Click or touch the <strong>ANSWER</strong>',
				'refreshAlt'               => LAN_VCAPTCHA_04, // 'Refresh/reload icon',
				'refreshTitle'             => LAN_VCAPTCHA_05, // 'Refresh/reload: get new images and accessibility option!',
			)
		);

		e107::js('settings', array('visualcaptcha' => $captchaSettings));

		e107::css('visualcaptcha', 'css/styles.css');
		e107::js('visualcaptcha', 'js/visualcaptcha.init.js');

		$element = '<div class="visual-captcha"></div>';
		$element .= $form->hidden('rand_num', 'x'); // BC compat.

		return $element;
	}

	static function label()
	{
		return '<span class="visual-captcha-label"></span>';
	}

	static function verify($code, $other)
	{
		return (self::invalid()) ? false : true;
	}

	static function invalid()
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


		$assetPath = __DIR__.'/languages/English';

		$captcha = new \visualCaptcha\Captcha($session, $assetPath);
		$frontendData = $captcha->getFrontendData();

		// print_a($frontendData);

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
			else //todo another instance of captcha with a different asset path?
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
}


?>