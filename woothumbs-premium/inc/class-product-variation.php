<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WooThumbs_Product_Variation.
 *
 * @class    Iconic_WooThumbs_Product_Variation
 * @version  1.0.0
 * @package  Iconic_WooThumbs
 * @category Class
 * @author   Iconic
 */
class Iconic_WooThumbs_Product_Variation {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'woocommerce_product_variation_get_gallery_image_ids', array( __CLASS__, 'get_gallery_image_ids', ), 10, 2 );
	}

	/**
	 * Add parent gallery to variation, if configured.
	 *
	 * @param array                $value
	 * @param WC_Product_Variation $product
	 *
	 * @return array
	 */
	public static function get_gallery_image_ids( $value, $product ) {
		// Add image gallery from meta for legacy compatibility.
		if ( empty( $value ) ) {
			$value = array_map( 'absint', array_filter( explode( ',', $product->get_meta( 'variation_image_gallery' ) ) ) );
		}

		/**
		 * Filter to bypass getting gallery image IDs.
		 *
		 * @since 4.12.1
		 * @hook iconic_woothumbs_bypass_get_gallery_image_ids
		 * @param  bool $value True to bypass get_gallery_image_ids. Default: is_admin().
		 * @return bool New value
		 */
		$bypass_get_gallery_image_ids = apply_filters( 'iconic_woothumbs_bypass_get_gallery_image_ids', is_admin() );

		if ( $bypass_get_gallery_image_ids ) {
			return $value;
		}

		$has_gallery_image_ids = ! empty( $value );
		$parent_product_id     = Iconic_WooThumbs_Product::get_parent_id( $product );
		$maintain_gallery      = Iconic_WooThumbs_Settings::get_maintain_gallery();

		if ( ( 0 === $maintain_gallery && ! $has_gallery_image_ids && ! has_post_thumbnail( $product->get_id() ) ) || 1 === $maintain_gallery || ( 2 === $maintain_gallery && ! $has_gallery_image_ids ) ) {
			$parent_product = wc_get_product( $parent_product_id );

			if ( $parent_product ) {
				$value = array_merge( $value, Iconic_WooThumbs_Product::get_gallery_image_ids( $parent_product ) );
			}
		}

		return array_filter( $value );
	}
}
