<?php

/**
 * Template hooks.
 *
 * @since 4.6.12
 */
class Iconic_WooThumbs_Template_Hooks {
	/**
	 * Init.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'remove_hooks' ) );
		add_action( 'iconic_woothumbs_image', array( __CLASS__, 'slide_content' ), 10, 3 );
		add_action( 'iconic_woothumbs_before_images', 'woocommerce_show_product_sale_flash', 10 );
	}

	/**
	 * Remove hooks.
	 */
	public static function remove_hooks() {
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	}

	/**
	 * Slide content.
	 *
	 * @param $image
	 * @param $i
	 * @param $images
	 */
	public static function slide_content( $image, $i, $images ) {
		if ( ! empty( $image['media_embed'] ) ) {
			echo $image['media_embed'];
		} else { ?>
			<img <?php echo Iconic_WooThumbs::array_to_html_atts( Iconic_WooThumbs::get_image_loop_data( $image, $i ) ); ?>>
		<?php }
	}
}