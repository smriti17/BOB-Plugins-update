<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Iconic_WooThumbs_Onboard_Settings' ) ) { 
	return;
}

class Iconic_WooThumbs_Onboard_Settings {
	/**
	 * @var mixed
	 */
	protected static $plugin_slug;
	
	/**
	 * @var mixed
	 */
	protected static $template_path;
		
	/**
	 * @access protected
	 * @var array
	 */
	protected static $setting_defaults = array(
		'id'          => 'default_field',
		'title'       => 'Default Field',
		'desc'        => '',
		'std'         => '',
		'type'        => 'text',
		'placeholder' => '',
		'choices'     => array(),
		'class'       => '',
		'subfields'   => array(),
	);

	/**
	 * Initialize.
	 * @param $args
	 */
	public static function run( $args ) {
		$plugin_slug         = self::$plugin_slug         = $args["plugin_slug"];
		self::$template_path = $args["plugin_path"] . "/inc/vendor/iconic-onboard/templates/";

		add_action( "iconic_onboard_{$plugin_slug}_slide_settings", array( __CLASS__, "add_settings" ) );
	}

	/**
	 * Returns $setting_defaults array.
	 *
	 * @return array
	 */
	public static function get_field_defaults() {
		return self::$setting_defaults;
	}

	/**
	 * @param $slide
	 */
	public static function add_settings( $slide ) {
		if ( ! $slide["slide"]["fields"] || ! count( $slide["slide"]["fields"] ) ) {
			return;
		}

		foreach ( $slide["slide"]["fields"] as $slide_index => $field ) {
			$field          = wp_parse_args( $field, self::$setting_defaults );
			$field['id']    = sprintf( '%s_%s', "iconic_onboard", $field['id'] );
			$field['value'] = isset( $field['default'] ) ? $field['default'] : '';
			$field['name']  = self::generate_field_name( $field['id'] );
			include self::$template_path . "/admin/single-field.php";
		}
	}

	/**
	 * Generate: Field ID
	 *
	 * @param mixed $id
	 *
	 * @return string
	 */
	public static function generate_field_name( $id ) {
		return sprintf( '%s_settings[%s]', self::$plugin_slug, $id );
	}

	/**
	 * Do field method, if it exists
	 *
	 * @param array $args
	 */
	public static function do_field_method( $args ) {
		$generate_field_method = sprintf( 'generate_%s_field', $args['type'] );

		if ( method_exists( __CLASS__, $generate_field_method ) ) {
			self::$generate_field_method( $args );
		}
	}

	/**
	 * Generate: Text field
	 *
	 * @param array $args
	 */
	public static function generate_text_field( $args ) {
		$args['value'] = esc_attr( stripslashes( $args['value'] ) );

		echo '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="regular-text ' . $args['class'] . '" />';

		self::generate_description( $args['desc'] );
	}


	/**
	 * Generate: Select field
	 *
	 * @param array $args
	 */
	public static function generate_select_field( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );

		echo '<select name="' . $args['name'] . '" id="' . $args['id'] . '" class="' . $args['class'] . '">';

		foreach ( $args['choices'] as $value => $text ) {
			if ( is_array( $text ) ) {
				if ( ! isset( $text['group_label'] ) ) {
					continue;
				}

				echo '<optgroup label="'. esc_attr( $text['group_label'] ) .'">';
					foreach( $text['values'] as $value => $text ) {
						$selected = $value == $args['value'] ? 'selected="selected"' : '';

						echo sprintf( '<option value="%s" %s>%s</option>', $value, $selected, $text );
					}
				echo '</optgroup>';
			} else {
				$selected = $value == $args['value'] ? 'selected="selected"' : '';

				echo sprintf( '<option value="%s" %s>%s</option>', $value, $selected, $text );
			}
		}

		echo '</select>';

		self::generate_description( $args['desc'] );
	}

	/**
	 * Generate: Radio field
	 *
	 * @param array $args
	 */
	public static function generate_radio_field( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );

		echo '<ul class="iconic-onboard-fields-list iconic-onboard-fields-list--radio iconic-onboard-fields-list--bordered">';

		foreach ( $args['choices'] as $value => $text ) {
			$field_id = sprintf( '%s_%s', $args['id'], $value );
			$checked  = $value == $args['value'] ? 'checked="checked"' : '';

			echo sprintf( '<li><label><input type="radio" name="%s" id="%s" value="%s" class="%s" %s> %s</label></li>', $args['name'], $field_id, $value, $args['class'], $checked, $text );
		}

		echo '</ul>';

		self::generate_description( $args['desc'] );
	}

	/**
	 * Generate: Checkbox field
	 *
	 * @param array $args
	 */
	public static function generate_checkbox_field( $args ) {
		$args['value'] = esc_attr( stripslashes( $args['value'] ) );
		$checked       = $args['value'] ? 'checked="checked"' : '';

		echo '<input type="hidden" name="' . $args['name'] . '" value="0" />';
		echo '<label><input type="checkbox" name="' . $args['name'] . '" id="' . $args['id'] . '" value="1" class="' . $args['class'] . '" ' . $checked . '> ' . $args['desc'] . '</label>';
	}

	/**
	 * Generate: Checkboxes field
	 *
	 * @param array $args
	 */
	public static function generate_checkboxes_field( $args ) {
		echo '<input type="hidden" name="' . $args['name'] . '" value="0" />';

		echo '<ul class="iconic-onboard-fields-list iconic-onboard-fields-list--checkboxes iconic-onboard-fields-list--bordered">';

		foreach ( $args['choices'] as $value => $text ) {
			$checked  = is_array( $args['value'] ) && in_array( $value, $args['value'] ) ? 'checked="checked"' : '';
			$field_id = sprintf( '%s_%s', $args['id'], $value );

			echo sprintf( '<li><label><input type="checkbox" name="%s[]" id="%s" value="%s" class="%s" %s> %s</label></li>', $args['name'], $field_id, $value, $args['class'], $checked, $text );
		}

		echo '</ul>';

		self::generate_description( $args['desc'] );
	}
	
	/**
	 * Generate Image Checkboxes
	 *
	 * @return void
	 */
	public static function generate_image_checkboxes_field( $args ) {
		
		echo '<input type="hidden" name="' . $args['name'] . '" value="0" />';

		echo '<ul class="iconic-onboard-fields-list iconic-onboard-fields-list--image-checkboxes iconic-onboard-fields-list--grid iconic-onboard-fields-list--cols">';

		foreach ( $args['choices'] as $value => $choice ) {
			$checked  = is_array( $args['value'] ) && in_array( $value, $args['value'] ) ? 'checked="checked"' : '';
			$field_id = sprintf( '%s_%s', $args['id'], $value );

			echo sprintf( '<li>
								<label>
									<img src="%s" >
									<input type="checkbox" name="%s[]" id="%s" value="%s" class="%s" %s> 
									%s
								</label>
							</li>', $choice["image"], $args['name'], $field_id, $value, $args['class'], $checked, $choice['text'] );
		}

		echo '</ul>';

		self::generate_description( $args['desc'] );
	}
	
	/**
	 * Generate: Image Radio field
	 *
	 * @param array $args
	 */
	public static function generate_image_radio_field( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );
		$count         = count( $args['choices'] );
		echo sprintf( '<ul class="iconic-onboard-fields-list iconic-onboard-fields-list--image-radio iconic-onboard-fields-list--grid iconic-onboard-fields-list--cols iconic-onboard-fields-list--col-%s ">', $count );
		
		foreach ( $args['choices'] as $value => $choice ) {
			$field_id = sprintf( '%s_%s', $args['id'], $value );
			$checked  = $value == $args['value'] ? 'checked="checked"' : '';

			echo sprintf( '<li class="iconic-onboard-fields-list__item %s">				
								<label>
									<div class="iconic-onboard-fields-list-image-radio__img_wrap">
										<img src="%s">
									</div>
									<input type="radio" name="%s" id="%s" value="%s" class="%s" %s>
									%s
								</label>
							</li>	
							', ( $checked ? 'iconic-onboard-fields-list__item--checked' : '' ), $choice["image"], $args['name'], $field_id, $value, $args['class'], $checked, $choice['text'] );
		}
		echo '</ul>';
		self::generate_description( $args['desc'] );
	}
			
	/**
	 * Generate: Custom field
	 *
	 * @param array $args
	 */
	public static function generate_custom_field( $args ) {
		echo $args['default'];
	}

	/**
	 * Generate: Description
	 *
	 * @param mixed $description
	 */
	public static function generate_description( $description ) {
		if ( $description && $description !== "" ) {
			echo '<p class="description">' . $description . '</p>';
		}
	}

}
