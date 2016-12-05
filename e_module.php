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

	e107::getOverride()->replace('secure_image::r_image',       'visualcaptcha_module::blank');
	e107::getOverride()->replace('secure_image::renderInput',   'visualcaptcha_module::input');
	e107::getOverride()->replace('secure_image::invalidCode',   'visualcaptcha_module::invalid');
	e107::getOverride()->replace('secure_image::renderLabel',   'visualcaptcha_module::label');
	e107::getOverride()->replace('secure_image::verify_code',   'visualcaptcha_module::verify');
}

class visualcaptcha_module
{
	
	function blank()
	{
		return;	
	}
	
	static function input()
	{
	//	return "hello";
		$form = e107::getForm();

		$captchaSettings = array();

		$form_name = 'login-page'; // @lonalore  we may need to auto-detect this with jQuery.

		$captchaSettings[] = array(
			'namespace' => $form_name,
			'formID'    => $form->name2id($form_name),
			'imgPath'   => e_PLUGIN . 'visualcaptcha/img/',
			'url'       => e_PLUGIN . 'visualcaptcha/app.php',
			'imgCount'  => 5,
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

		return '<div class="e-visual-captcha"></div>
		<input type="hidden" name="rand_num" value="x" />'; // BC compat.


	}
	
	static function label()
	{
		return "Enable the form";	
	}
	
	static function verify($code,$other)
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
}


?>