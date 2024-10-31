<?php
/**
 * File contains class News_Match_Popup_Basics_Url_Exclude
 *
 * @since 0.1.1
 * @package News_Match_Popup_Basics
 */

/**
 * This class contains the functionality for the News Match Popup Basics plugin's suppression of of all popups on pages that match the URLs defined in the News Match Popup Basics settings
 *
 * @since 0.1.1
 * @package News_Match_Popup_Basics
 */
class News_Match_Popup_Basics_Url_Exclude {
	/**
	 * Option key
	 *
	 * @var string
	 * @since 0.1.1
	 */
	private $key = '';

	/**
	 * Set us up the vars from the plugin's single instance
	 * initialize the hooks
	 *
	 * @param string $settings_key the settings key in wp_options for the plugin.
	 * @since 0.1.1
	 */
	public function __construct( $settings_key ) {
		$this->key = $settings_key;
		add_action( 'wp_enqueue_scripts', array( $this, 'popmake_maybe_dequeue' ), 9 );
	}

	/**
	 * Determine whether or not to prevent enqueueing of popups
	 *
	 * @since 0.1.1
	 * @return Boolean whether or not the mailchimp_enqueue function was run.
	 */
	public function popmake_maybe_dequeue() {
		$option = get_option( $this->key, array() );
		if ( ! isset( $option['donate_toggle'] ) || 'on' !== $option['donate_toggle'] ) {
			return false;
		}
		if ( ! isset( $option['donate_urls'] ) || empty( $option['donate_urls'] ) ) {
			return false;
		}

		// check whether the present URL is one of those URLs.
		$potential_urls = explode( PHP_EOL, $option['donate_urls'] );
		global $wp;
		$current_url = trailingslashit( home_url( $wp->request ) );

		$dequeue = false;
		foreach ( $potential_urls as $url ) {
			if ( ! empty( $url ) && false !== strpos( $current_url, $url ) ) {
				$dequeue = true;
				continue;
			}
		}

		if ( $dequeue ) {
			$this->dequeue();
			return true;
		}

		return false;
	}

	/**
	 * Dequeue all the things that get enqueued that are directly related to popups.
	 *
	 * This doesn't address the admin bar scripts, styles, or admin bar entry.
	 *
	 * @since 0.1.1
	 * @since Popup Maker v1.6.6
	 */
	public function dequeue() {
		remove_action( 'wp_enqueue_scripts', 'popmake_load_site_scripts', 10 );
		remove_action( 'wp_enqueue_scripts', 'popmake_load_site_styles', 10 );
		remove_action( 'wp_enqueue_scripts', 'popmake_preload_popups', 11 );
		remove_action( 'wp_footer', 'popmake_render_popups', 1 );
		remove_action( 'wp_head', 'popmake_render_popup_theme_styles', 99999 );
		remove_action( 'wp_head', 'popmake_script_loading_enabled', 10 );
		remove_action( 'admin_head', 'popmake_render_popup_theme_styles', 99999 );
		wp_dequeue_script( 'news-match-popup-basics-mailchimp' );
	}
}
