<?php

/**
 * @file
 * Provides information about external libraries.
 */


/**
 * Class visualcaptcha_library.
 */
class visualcaptcha_library
{

	/**
	 * Return information about external libraries.
	 */
	function config()
	{
		$libraries['visualcaptcha.jquery'] = array(
			'name'              => 'visualCaptcha Frontend',
			'vendor_url'        => 'https://github.com/emotionLoop/visualCaptcha-frontend-jquery',
			'download_url'      => 'https://github.com/emotionLoop/visualCaptcha-frontend-jquery/releases',
			'library_path'      => '{e_PLUGIN}visualcaptcha/vendor/visualcaptcha.jquery/',
			'version_arguments' => array(
				'file'    => 'bower.json',
				'pattern' => '/(0\.\d+\.\d+)/', // "version": "0.0.8",
				'lines'   => 5,
			),
			'files'             => array(
				'css' => array(
					'visualcaptcha.src.css' => array(
						'zone' => 2,
					),
				),
				'js'  => array(
					'visualcaptcha.jquery.src.js' => array(
						'type' => 'footer',
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// All properties defined for 'minified' override top-level properties.
				'minified' => array(
					'css' => array(
						'visualcaptcha.css' => array(
							'zone' => 2,
						),
					),
					'js'  => array(
						'visualcaptcha.jquery.js' => array(
							'type' => 'footer',
							'zone' => 2,
						),
					),
				),
			),
		);

		return $libraries;
	}

}
