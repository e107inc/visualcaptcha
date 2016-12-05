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
			$(context).find('.visual-captcha').once('init-visual-captcha').each(function ()
			{
				var $captcha = $(this);
				var $form = $(this).closest('form');
				var $label = $form.find('.visual-captcha-label');

				$captcha.visualCaptcha({
					imgPath: e107.settings.visualcaptcha.imgPath,
					captcha: {
						url: e107.settings.visualcaptcha.url,
						numberOfImages: e107.settings.visualcaptcha.imgCount,
						namespace: $form.attr('id') || $form.attr('name'),
						callbacks: {
							loaded: function (captcha)
							{
								// Avoid adding the hashtag to the URL when clicking/selecting
								// visualCaptcha options.
								$captcha.find('a').on('click', function (event)
								{
									event.preventDefault();
								});

								if($label.length > 0)
								{
									var $explanation = $captcha.find('.visualCaptcha-explanation');
									$label.html($explanation.html());
									$explanation.remove();
								}
							}
						}
					},
					language: e107.settings.visualcaptcha.language
				});
			});
		}
	};


})(jQuery);
