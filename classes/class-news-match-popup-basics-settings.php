<?php
/**
 * The settings file.
 *
 * @since 0.1.1
 * @package News_Match_Popup_Basics
 */

/**
 * This class contains the settings and functionality for the News Match Popup Basics plugin's suppression of popups containing mailchimp signup dialogs when the browser has been referred from a Mailchimp-analytics-using URL
 *
 * @since 0.1.1
 * @package News_Match_Popup_Basics
 */
class News_Match_Popup_Basics_Settings {
	/**
	 * Option key and option page slug
	 *
	 * @var string
	 * @since 0.1.1
	 */
	private $key = '';

	/**
	 * Slug of settings group
	 *
	 * @var string $settings_group The settings group slug
	 */
	private $settings_group = '';

	/**
	 * Slug of settings section
	 *
	 * @var string $settings_section The settings section slug
	 */
	private $settings_section = '';

	/**
	 * Options page title.
	 *
	 * @var string
	 * @since 0.1.1
	 */
	protected $title = '';

	/**
	 * Set us up the vars from the plugin's single instance
	 *
	 * @since 0.1.1
	 * @param string $settings_key the option key in wp_options.
	 */
	public function __construct( $settings_key ) {
		$this->key = $settings_key;
		$this->settings_section = $settings_key . '_section';
		$this->settings_group = $settings_key; // the same because of https://core.trac.wordpress.org/ticket/7277 .
		$this->title = esc_attr__( 'News Match Popup Basics', 'news-match-popup-basics' );

		add_action( 'admin_init', array( $this, 'register_settings' ), 10 );
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 999 );
	}

	/**
	 * The settings section and settings group and option name were all registered in the save
	 *
	 * @since 0.1.1
	 */
	public function register_settings() {
		register_setting( $this->key, $this->key, array( $this, 'settings_sanitizer' ) );

		add_settings_section(
			$this->settings_section,
			esc_html( $this->title ),
			array( $this, 'settings_section_callback' ),
			$this->key
		);

		add_settings_field(
			$this->key . '[mailchimp_toggle]',
			__( 'Popup prevention for Mailchimp visitors', 'news-match-popup-basics' ),
			array( $this, 'mailchimp_toggle' ),
			$this->key,
			$this->settings_section,
			array(
				'name' => $this->key . '[mailchimp_toggle]',
			)
		);

		add_settings_field(
			$this->key . '[mailchimp_campaign]',
			__( 'Mailchimp campaign ID', 'news-match-popup-basics' ),
			array( $this, 'mailchimp_campaign' ),
			$this->key,
			$this->settings_section,
			array(
				'name' => $this->key . '[mailchimp_campaign]',
			)
		);

		add_settings_field(
			$this->key . '[donate_toggle]',
			__( 'Donation page popup prevention', 'news-match-popup-basics' ),
			array( $this, 'donate_toggle' ),
			$this->key,
			$this->settings_section,
			array(
				'name' => $this->key . '[donate_toggle]',
			)
		);

		add_settings_field(
			$this->key . '[donate_urls]',
			__( 'Donation page URLs', 'news-match-popup-basics' ),
			array( $this, 'donate_urls' ),
			$this->key,
			$this->settings_section,
			array(
				'name' => $this->key . '[donate_urls]',
			)
		);

		return true;
	}

	/**
	 * Gather the settings values from the $_POST, clean them
	 *
	 * @since 0.1.1
	 * @param array $value the submitted settings values.
	 */
	public function settings_sanitizer( $value ) {
		$new_settings = array();

		if ( isset( $value['mailchimp_toggle'] ) && ! empty( $value['mailchimp_toggle'] ) ) {
			if ( 'on' === $value['mailchimp_toggle'] ) {
				$new_settings['mailchimp_toggle'] = 'on';
			}
		}

		if ( isset( $value['mailchimp_campaign'] ) && ! empty( $value['mailchimp_campaign'] ) ) {
			$new_settings['mailchimp_campaign'] = esc_attr( $value['mailchimp_campaign'] );
		}

		if ( isset( $value['donate_toggle'] ) && ! empty( $value['donate_toggle'] ) ) {
			if ( 'on' === $value['donate_toggle'] ) {
				$new_settings['donate_toggle'] = 'on';
			}
		}

		if ( isset( $value['donate_urls'] ) && ! empty( $value['donate_urls'] ) ) {
			$potential_urls = explode( PHP_EOL, $value['donate_urls'] );
			foreach ( $potential_urls as $url ) {
				$new_urls[] = $this->remove_protocol_from_url( $url );
			}
			$new_settings['donate_urls'] = implode( PHP_EOL, $new_urls );
		}

		return $new_settings;
	}

	/**
	 * Function to strip the frontmatter from a URL
	 *
	 * @param string $url The URL passed to the function, which may have a protocol that should be removed.
	 * @return string The URL without the protocol or domain name.
	 * @since 0.1.1
	 */
	public function remove_protocol_from_url( $url ) {
		// remove protocol http(s).
		return preg_replace( '/^http(s)?:\/\//', '', $url );
	}

	/**
	 * Display the checkbox for toggling whether the plugin suppresses based on mailchimp campaign ID in the URL
	 *
	 * @since 0.1.1
	 * @param array $args Optional arguments passed to callbacks registered with add_settings_field.
	 */
	public function mailchimp_toggle( $args ) {
		$option = get_option( $this->key, array() );
		if ( ! isset( $option['mailchimp_toggle'] ) || 'on' !== $option['mailchimp_toggle'] ) {
			$value = false;
		} else {
			$value = 'on';
		}

		echo sprintf(
			'<input name="%1$s" id="%1$s" type="checkbox" value="on" %2$s>',
			esc_attr( $args['name'] ),
			checked( $value, 'on', false )
		);
		echo sprintf(
			'<label for="%2$s">%1$s</label>',
			wp_kses_post( __( 'Checking this box will prevent popups containing a Mailchimp signup form with the HTML element ID <code>#mc_embed_signup</code> from appearing when visiting your site at a link with a <code>utm_source</code> parameter matching the one entered in the box below.', 'news-match-popup-basics' ) ),
			esc_attr( $args['name'] )
		);
	}

	/**
	 * Display text input for mailchimp campaign ID
	 *
	 * @since 0.1.1
	 * @param array $args Optional arguments passed to callbacks registered with add_settings_field.
	 */
	public function mailchimp_campaign( $args ) {
		$option = get_option( $this->key, array() );
		if ( ! isset( $option['mailchimp_campaign'] ) || empty( $option['mailchimp_campaign'] ) ) {
			$value = '';
		} else {
			$value = esc_attr( $option['mailchimp_campaign'] );
		}

		echo sprintf(
			'<p><code>utm_source=</code><input name="%1$s" id="%1$s" type="text" value="%2$s"></p>',
			esc_attr( $args['name'] ),
			esc_attr( $value )
		);
		echo sprintf(
			'<label for="%2$s">%1$s</label>',
			wp_kses_post( __( 'The campaign name can be found by examining outbound links from your Mailchimp newsletter, then carefully copying everything between <code>utm_source=</code> and <code>&amp;</code>.', 'news-match-popup-basics' ) ),
			esc_attr( $args['name'] )
		);

	}

	/**
	 * Display the checkbox for toggling whether the plugin suppresses based on donate page URLs
	 *
	 * @since 0.1.1
	 * @param array $args Optional arguments passed to callbacks registered with add_settings_field.
	 */
	public function donate_toggle( $args ) {
		$option = get_option( $this->key, array() );
		if ( ! isset( $option['donate_toggle'] ) || 'on' !== $option['donate_toggle'] ) {
			$value = false;
		} else {
			$value = 'on';
		}

		echo sprintf(
			'<input name="%1$s" id="%1$s" type="checkbox" value="on" %2$s>',
			esc_attr( $args['name'] ),
			checked( $value, 'on', false )
		);
		echo sprintf(
			'<label for="%2$s">%1$s</label>',
			wp_kses_post( __( 'Checking this box will prevent <strong>all</strong> Popup Maker popups from appearing on pages with URLs matching the URLs entered in the box below.', 'news-match-popup-basics' ) ),
			esc_attr( $args['name'] )
		);
	}

	/**
	 * Display text area input for donation page URLs where the mailchimp popup should not appear
	 *
	 * @param array $args The extra arguments passed to add_settings_field callbacks.
	 * @since 0.1.1
	 */
	public function donate_urls( $args ) {
		$option = get_option( $this->key, array() );
		if ( ! isset( $option['donate_urls'] ) || empty( $option['donate_urls'] ) ) {
			$value = '';
		} else {
			$value = wp_kses_post( $option['donate_urls'] );
		}

		echo sprintf(
			'<textarea name="%1$s" id="%1$s" type="checkbox" wrap="off" style="width: 100%%; display: block;">%2$s</textarea>',
			esc_attr( $args['name'] ),
			$value // $value is already escaped above. It's either '' or it's been wp_kses_post'd.
		);

		// reminder to remove http(s)?:// .
		echo sprintf(
			'<label for="%1$s">%2$s</label>',
			esc_attr( $args['name'] ),
			wp_kses_post( __( 'Each URL should be entered on a separate line. Please remove the opening <code>https://</code> or <code>http://</code> from the URL, as it is not needed in this context. You can also provide URL fragments such as <code>/donate/</code> to hit both <code>example.org/donate/</code> and <code>example.org/about-us/donate</code>.', 'news-match-popup-basics' ) )
		);
	}

	/**
	 * Settings section display
	 *
	 * @since 0.1.1
	 */
	public function settings_section_callback() {
		echo wp_kses_post( sprintf(
			'<p>%1$s</p>',
			__( 'This page controls modifications that News Match Popup Basics plugin makes to Popup Maker popups on your site.', 'news-match-popup-basics' )
		));
	}

	/**
	 * Add menu options page
	 *
	 * @since 0.1.1
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page(
			'edit.php?post_type=popup',
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);
	}

	/**
	 * Admin page markup
	 *
	 * @since 0.1.1
	 */
	public function admin_page_display() {
		?>
		<div class="wrap options-page <?php echo esc_attr( $this->key ); ?>">
			<form method="post" action="options.php">
			<?php
				settings_fields( $this->settings_group );
				do_settings_sections( $this->key );
				submit_button();
	?>
			</form>
		</div>
		<?php
	}
}
