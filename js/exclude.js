(function ($) {
	$(document).on( 'pumBeforeOpen', function( ) {
		// escape the string for the regex via https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions#Using_special_characters
		var escaped = news_match_popup_basics_mailchimp.campaign.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
		var match = new RegExp( 'utm_source=' + escaped, 'i' );

		if (window.location.href.match( match )) {

			if ( news_match_popup_basics_mailchimp.selector ) {
				var $popup = PUM.getPopup( news_match_popup_basics_mailchimp.selector );
			} else {
				var $popup = PUM.getPopup( '.pum #mc_embed_signup' );
				console.log( 'Something is wrong with the filter applied to \'news_match_popup_basics_mailchimp_selector\'. Please ensure that it is returning a string and that that string is a valid CSS selector' );
			}

			// @since Popup Maker v1.6.6
			$popup.addClass('preventOpen');
		}
	});
}(jQuery));
