<?php
/**
 * Prevent Mailchimp popups from opening, if the option is so set.
 *
 * @since 0.1.1
 * @package News_Match_Popup_Basics
 */

/**
 * This class contains the functionality for the News Match Popup Basics plugin's suppression of popups containing mailchimp signup dialogs when the browser has been referred from a Mailchimp-analytics-using URL
 *
 * @since 0.1.1
 */
class News_Match_Popup_Basics_Mailchimp {
	/**
	 * Option key
	 *
	 * @var string
	 * @since 0.1.1
	 */
	private $key = '';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Set us up the vars from the plugin's single instance
	 *
	 * And initialize the hooks
	 *
	 * @param string $settings_key the wp_options table option key.
	 * @param string $url the URL of this plugin's directory's location.
	 * @since 0.1.1
	 */
	public function __construct( $settings_key, $url ) {
		$this->key = $settings_key;
		$this->url = $url;
		add_action( 'wp_enqueue_scripts', array( $this, 'mailchimp_maybe_enqueue' ), 9 );
	}

	/**
	 * Determine whether to enqueue the popup-disabling javascript
	 *
	 * @since 0.1.1
	 * @return Boolean whether or not the mailchimp_enqueue function was run.
	 */
	public function mailchimp_maybe_enqueue() {
		$option = get_option( $this->key, array() );
		if ( ! isset( $option['mailchimp_toggle'] ) || 'on' !== $option['mailchimp_toggle'] ) {
			return false;
		}
		if ( ! isset( $option['mailchimp_campaign'] ) || empty( $option['mailchimp_campaign'] ) ) {
			return false;
		}

		$this->mailchimp_enqueue( $option );
		return true;
	}

	/**
	 * Modify the things/output the js
	 *
	 * @param array $options The options array for this plugin.
	 * @since 0.1.1
	 * @since Popup Maker v1.6.6
	 */
	public function mailchimp_enqueue( $options = array() ) {
		wp_register_script(
			'news-match-popup-basics-mailchimp',
			$this->url . 'js/exclude.js',
			array( 'jquery', 'popup-maker-site' ), // depends upon both of these scripts.
			null,
			true
		);
		wp_localize_script(
			'news-match-popup-basics-mailchimp',
			'news_match_popup_basics_mailchimp',
			array(
				'campaign' => $options['mailchimp_campaign'],
				/**
				 * @filter news_match_popup_basics_mailchimp_selector
				 * @param string $selector A CSS selector that chooses mailchimp forms within Popup Maker popups; default is '.pum #mc_embed_signup'.
				 * @return string The new selector.
				 * @since 0.1.2
				 */
				'selector' => apply_filters( 'news_match_popup_basics_mailchimp_selector', '.pum #mc_embed_signup' )
			)
		);
		wp_enqueue_script(
			'news-match-popup-basics-mailchimp'
		);
	}
}
