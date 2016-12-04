var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{
	'use strict';

	/**
	 * Initializes VisualCaptcha.
	 *
	 * @type {{attach: e107.behaviors.initVisualCaptcha.attach}}
	 */
	e107.behaviors.initVisualCaptcha = {
		attach: function (context, settings)
		{
			$.each(e107.settings.visualcaptcha, function() {
				var captcha = this;

				$(context).find('#' + captcha.formID).once('init-visual-captcha').each(function ()
				{
					var $form = $(this);
					var $captcha = $form.find('.e-visual-captcha');

					$captcha.visualCaptcha({
						imgPath: captcha.imgPath,
						captcha: {
							url: captcha.url,
							numberOfImages: captcha.imgCount,
							namespace: captcha.namespace,
							callbacks: {
								loaded: function (captcha)
								{
									// Avoid adding the hashtag to the URL when clicking/selecting
									// visualCaptcha options.
									$captcha.find('a').on('click', function (event)
									{
										event.preventDefault();
									});
								}
							}
						},
						language: captcha.language
					});
				});

			});
		}
	};


})(jQuery);
