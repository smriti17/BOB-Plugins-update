<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WooThumbs_Settings.
 *
 * @class    Iconic_WooThumbs_Settings
 * @version  1.0.0
 * @package  Iconic_WooThumbs
 */
class Iconic_WooThumbs_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'admin_init', array( __CLASS__, 'onboard' ) );
		add_action( 'update_option_iconic_woothumbs_settings', array( __CLASS__, 'on_save' ), 10, 3 );
		add_filter( 'iconic_woothumbs_settings_validate', array( __CLASS__, 'validate_settings' ), 10, 1 );
		add_filter( 'iconic_onboard_save_iconic_woothumbs_result', array( __CLASS__, 'save_onboard_settings' ), 10, 2 );
	}

	/**
	 * Init.
	 */
	public static function init() {
		global $iconic_woothumbs_class;

		if ( empty( $iconic_woothumbs_class ) ) {
			return;
		}

		$iconic_woothumbs_class->set_settings();
		self::onboard();
	}

	/**
	 * Trigger onboard process.
	 */
	public static function onboard() {
		global $iconic_woothumbs_class;

		$action            = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
		$has_valid_licence = true;
		$is_settings_page  = Iconic_WooThumbs_Core_Settings::is_settings_page();
		$is_settings_page  = $is_settings_page ? $is_settings_page : defined( 'DOING_AJAX' ) && in_array( $action, array( 'iconic_onboard_iconic_woothumbs_save_modal', 'iconic_onboard_iconic_woothumbs_dismiss_modal' ) );

		if ( ! $has_valid_licence || ! $is_settings_page ) {
			return;
		}

		include_once ICONIC_WOOTHUMBS_INC_PATH . 'vendor/iconic-onboard/class-iconic-onboard.php';

		$slides = array(
			array(
				"header_image" => $iconic_woothumbs_class->plugin_url . 'assets/img/onboarding/woothumbs-onboarding-welcome.png',
				"title"        => __( 'Welcome to WooThumbs for WooCommerce', 'iconic-woothumbs' ),
				"description"  => __( 'This wizard will quickly get you started with WooThumbs. It is optional and should only take a few minutes.', 'iconic-woothumbs' ),
				"button_text"  => sprintf( '%s  <span class=\'dashicons dashicons-arrow-right-alt2\'></span>', __( 'Start', 'iconic-woothumbs' ) ),
				'button_icon'  => '',
			),
			array(
				"title"       => __( 'Which thumbnail layout do you prefer?', 'iconic-woothumbs' ),
				"description" => __( 'WooThumbs makes it easy to customise your product image gallery. How you would like the thumbnails to be displayed?', 'iconic-woothumbs' ),
				"button_text" => sprintf( '%s  <span class=\'dashicons dashicons-arrow-right-alt2\'></span>', __( 'Continue', 'iconic-woothumbs' ) ),
				"fields"      => array(
					array(
						'id'      => 'layout',
						'title'   => '',
						'desc'    => '',
						'type'    => 'image_radio',
						'default' => 'below',
						'choices' => array(
							'below' => array(
								"text"  => __( 'Below', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/layout_bottom.png",
							),
							'left'   => array(
								"text"  => __( 'Left', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/layout_left.png",
							),
							'right'  => array(
								"text"  => __( 'Right', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/layout_right.png",
							),
							'none'   => array(
								"text"  => __( 'None', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/layout_none.png",
							),
						),
					),
				),
			),
			array(
				"title"       => __( 'How should the images transition?', 'iconic-woothumbs' ),
				"description" => __( 'When transitioning between images in the gallery, which effect would you like to use?', 'iconic-woothumbs' ),
				"button_text" => sprintf( '%s  <span class=\'dashicons dashicons-arrow-right-alt2\'></span>', __( 'Continue', 'iconic-woothumbs' ) ),
				"fields"      => array(
					array(
						'id'      => 'transition',
						'title'   => '',
						'desc'    => '',
						'type'    => 'image_radio',
						'default' => 'horizontal',
						'choices' => array(
							'horizontal' => array(
								"text"  => __( 'Horizontal', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/transition_horizontal_slide.png",
							),
							'vertical'   => array(
								"text"  => __( 'Vertical', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/transition_vertical_slide.png",
							),
							'fade'       => array(
								"text"  => __( 'Fade', 'iconic-woothumbs' ),
								"image" => $iconic_woothumbs_class->plugin_url . "/assets/img/onboarding/transition_fade.png",
							),
						),
					),
				),
			),
			array(
				"title"       => __( "You're ready to start using WooThumbs!", 'iconic-woothumbs' ),
				"description" => sprintf(
					'%s%s<em>%s</em>',
					__( "Once you've saved the settings using the button below, go and visit a product page to see it in action.", 'iconic-woothumbs' ),
					"\n\n",
					__( "Don't worry, you can still come back and change these settings whenever you like.", 'iconic-woothumbs' )
				),
				"button_text" => sprintf( '%s  <span class=\'dashicons dashicons-yes\'></span>', __( 'Finish &amp; Save', 'iconic-woothumbs' ) ),
			),
		);

		Iconic_WooThumbs_Onboard::run(
			array(
				'version'     => $iconic_woothumbs_class->version,
				'plugin_slug' => $iconic_woothumbs_class->slug_alt,
				'plugin_url'  => $iconic_woothumbs_class->plugin_url,
				'plugin_path' => $iconic_woothumbs_class->plugin_path,
				'slides'      => $slides,
			)
		);
	}

	/**
	 * Save the onboarding data in database.
	 *
	 * @param array $result
	 * @param array $fields_arr
	 *
	 * @return array $result
	 */
	public static function save_onboard_settings( $result, $fields_arr ) {
		if ( empty( $fields_arr["iconic_woothumbs_settings"]['iconic_onboard_layout'] ) || empty( $fields_arr["iconic_woothumbs_settings"]['iconic_onboard_transition'] ) ) {
			return false;
		}

		$layout     = filter_var( $fields_arr["iconic_woothumbs_settings"]['iconic_onboard_layout'], FILTER_SANITIZE_STRING );
		$transition = filter_var( $fields_arr["iconic_woothumbs_settings"]['iconic_onboard_transition'], FILTER_SANITIZE_STRING );

		// Get existing/default settings.
		$settings = get_option( "iconic_woothumbs_settings", array() );

		// Update settings with onboarding data.
		$settings["navigation_thumbnails_position"] = $layout;
		$settings["carousel_general_mode"]          = $transition;
		$settings                                   = array_filter( $settings );

		// Save settings.
		update_option( "iconic_woothumbs_settings", $settings );

		// Return success.
		return $result;
	}

	/**
	 * On save settings.
	 *
	 * @param mixed  $old_value
	 * @param mixed  $value
	 * @param string $option
	 */
	public static function on_save( $old_value, $value, $option ) {
		if ( class_exists( 'WC_Regenerate_Images' ) && apply_filters( 'woocommerce_background_image_regeneration', true ) ) {
			WC_Regenerate_Images::maybe_regenerate_images();
		}
	}

	/**
	 * Admin: Validate Settings
	 *
	 * @param array $settings Un-validated settings
	 *
	 * @return array $validated_settings
	 */
	public static function validate_settings( $settings ) {
		self::maybe_clear_image_cache( $settings );

		if ( isset( $_POST['iconic-woothumbs-delete-image-cache'] ) ) {
			add_settings_error( 'iconic-woothumbs-delete-image-cache', 'iconic-woothumbs', __( 'The image cache has been cleared.', 'iconic-woothumbs' ), 'updated' );
		}

		if ( 0 >= (int) $settings['carousel_general_main_slider_swipe_threshold'] ) {
			$settings['carousel_general_main_slider_swipe_threshold'] = 5;
			add_settings_error( 'iconic-woothumbs-swipe-threshold-invalid-value', 'iconic-woothumbs', __( 'Touch threshold cannot be less then less than 1, the value has been reset to 5.', 'iconic-woothumbs' ), 'error' );
		}

		return $settings;
	}

	/**
	 * Maybe clear image cache.
	 *
	 * @param $settings
	 */
	public static function maybe_clear_image_cache( $settings ) {
		global $iconic_woothumbs_class;

		$clear = apply_filters( 'iconic_woothumbs_maybe_clear_image_cache', false, $settings );
		$watch = apply_filters( 'iconic_woothumbs_maybe_clear_image_cache_watch', array(
			'media_mp4_controls',
			'media_mp4_loop',
			'media_mp4_loop',
			'variations_settings_maintain_gallery',
		), $settings );

		foreach( $watch as $key ) {
			if ( isset( $settings[ $key ] ) && $iconic_woothumbs_class->settings[ $key ] === $settings[ $key ] ) {
				continue;
			}

			$clear = true;
			break;
		}

		if ( $clear ) {
			$iconic_woothumbs_class->delete_transients( true );
		}
	}

	/**
	 * Get a list of image sizes for the site
	 *
	 * @return array
	 */
	public static function get_image_sizes() {
		$image_sizes = array_merge( get_intermediate_image_sizes(), array( 'full' ) );

		return array_combine( $image_sizes, $image_sizes );
	}

	/**
	 * Clear image cache link.
	 *
	 * @return string
	 */
	public static function clear_image_cache_link() {
		ob_start();

		?>
		<button name="iconic-woothumbs-delete-image-cache" class="button button-secondary"><?php _e( 'Clear Image Cache', 'iconic-woothumbs' ); ?></button>
		<?php

		return ob_get_clean();
	}

	/**
	 * Add ratio fields.
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function ratio_fields( $args ) {
		$defaults = array(
			'width'  => '',
			'height' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$width_name  = sprintf( '%s_width', $args['name'] );
		$height_name = sprintf( '%s_height', $args['name'] );
		$input       = '<input id="%s" name="iconic_woothumbs_settings[%s]" type="number" style="width: 50px;" value="%s">';
		$width       = sprintf( $input, $width_name, $width_name, $args['width'] );
		$height      = sprintf( $input, $height_name, $height_name, $args['height'] );

		return sprintf( '%s : %s', $width, $height );
	}

	/**
	 * Get default gallery width based on theme.
	 *
	 * @return int
	 */
	public static function get_default_width() {
		$default = 42;
		$theme   = wp_get_theme();

		if ( empty( $theme ) ) {
			return $default;
		}

		switch ( $theme->template ) {
			case 'twentytwenty':
			case 'Divi':
				$default = 48;
				break;
			case 'astra':
			case 'savoy':
				$default = 50;
				break;
			case 'atelier':
				$default = 60;
				break;
			case 'flatsome':
			case 'Avada':
			case 'enfold':
			case 'porto':
			case 'shopkeeper':
			case 'woodmart':
				$default = 100;
				break;
		}

		return $default;
	}

	/**
	 * Get thumbnail width based on thumbnail count.
	 *
	 * @return int
	 */
	public static function get_thumbnail_width() {
		$navigation_thumbnails_count = Iconic_WooThumbs_Core_Settings::get_setting( 'navigation_thumbnails_count' );

		return empty( $navigation_thumbnails_count ) ? 0 : ( 100 / (int) $navigation_thumbnails_count );
	}

	/**
	 * Get maintain gallery setting.
	 *
	 * 0 = No.
	 * 1 = Yes.
	 * 2 = Only if variation has no gallery images.
	 *
	 * @return bool|string
	 */
	public static function get_maintain_gallery() {
		$maintain_gallery = Iconic_WooThumbs_Core_Settings::get_setting( 'variations_settings_maintain_gallery' );

		return apply_filters( 'iconic_woothumbs_maintain_gallery', (int) $maintain_gallery );
	}
}
