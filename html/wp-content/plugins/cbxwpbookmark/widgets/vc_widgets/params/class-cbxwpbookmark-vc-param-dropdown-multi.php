<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CBXWPBookmark_VCParam_DropDownMulti {
	/**
	 * Initiator.
	 */
	public function __construct() {
		if ( defined( 'WPB_VC_VERSION' ) && version_compare( WPB_VC_VERSION, 4.8 ) >= 0 ) {
			if ( function_exists( 'vc_add_shortcode_param' ) ) {

				wp_register_style( 'select2', plugin_dir_url( __FILE__ ) . '../../../assets/vendors/select2/css/select2.min.css', array(), CBXWPBOOKMARK_PLUGIN_VERSION );
				wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . '../../../assets/vendors/select2/js/select2.full.min.js', array( 'jquery' ), CBXWPBOOKMARK_PLUGIN_VERSION );

				wp_enqueue_style( 'select2' );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'select2' );

				vc_add_shortcode_param( 'cbxwpbookmarkdownmulti', array( $this, 'dropdown_multi_render' ) );
			}
		} else {
			if ( function_exists( 'add_shortcode_param' ) ) {

				wp_register_style( 'select2', plugin_dir_url( __FILE__ ) . '../../../assets/vendors/select2/css/select2.min.css', array(), CBXWPBOOKMARK_PLUGIN_VERSION );
				wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . '../../../assets/vendors/select2/js/select2.full.min.js', array( 'jquery' ), CBXWPBOOKMARK_PLUGIN_VERSION );

				wp_enqueue_style( 'select2' );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'select2' );

				add_shortcode_param( 'cbxwpbookmarkdownmulti', array( $this, 'dropdown_multi_render' ) );
			}
		}
	}

	/**
	 * Select2 Dropdown Field Render
	 *
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function dropdown_multi_render( $settings, $value ) {
		$dependency = '';
		$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
		$type       = isset( $settings['type'] ) ? $settings['type'] : '';
		$class      = isset( $settings['class'] ) ? $settings['class'] : '';

		$uni    = uniqid( 'cbxwpbookmarkdownmulti-' . wp_rand() );
		$output = '<style>
.select2-container{
	width: 100% !important;
</style>';
		$output .= '<select multiple name="' . esc_attr( $param_name ) . '" class="wpb_vc_param_value wpb-input wpb-select cbxwpbookmarkdownmulti ' . esc_attr( $param_name ) . ' ' . esc_attr( $type ) . '' . esc_attr( $class ) . '">';


		$param_value_arr = array();

		if ( ! is_array( $value ) ) {
			$param_value_arr = explode( ',', $value );
		} else {
			$param_value_arr = $value;
		}

		foreach ( $settings['value'] as $text_val => $val ) {
			if ( is_numeric( $text_val ) && ( is_string( $val ) || is_numeric( $val ) ) ) {
				$text_val = $val;
			}

			$selected = '';


			//if ( $value !== '' && in_array( $val, $param_value_arr ) ) {
			if ( sizeof( $param_value_arr ) > 0 && in_array( $val, $param_value_arr ) ) {
				$selected = ' selected="selected"';
			}

			$output .= '<option class="cbxwpbookmarkdownmulti-' . $val . '" value="' . $val . '"' . $selected . '>' . $text_val . '</option>';
		};
		$output .= '</select>';

		$output .= '<script type="text/javascript">
 					jQuery(\'.cbxwpbookmarkdownmulti\').select2({
 						allowClear: false,
 						dropdownParent: jQuery("#vc_ui-panel-edit-element")
 					});
				</script>';

		return $output;
	}

}

new CBXWPBookmark_VCParam_DropDownMulti();