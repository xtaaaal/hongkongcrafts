<?php

namespace CBXWPBookmark_ElemWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Bookmark button Elementor Widget
 */
class CBXWPBookmarkbtn_ElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve bookmark button widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxwpbookmarkbtn';
	}

	/**
	 * Retrieve bookmark button widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return esc_html__( 'CBX Bookmark Button', 'cbxwpbookmark' );
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the widget categories.
	 *
	 * @return array Widget categories.
	 * @since  1.0.10
	 * @access public
	 *
	 */
	public function get_categories() {
		return array( 'cbxwpbookmark' );
	}

	/**
	 * Retrieve bookmark button widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'cbxwpbookmars-btn-icon';
	}

	/**
	 * Register bookmark button widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_cbxwpbookmarkbtn',
			array(
				'label' => esc_html__( 'CBX Bookmark Button Settings', 'cbxwpbookmark' ),
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => esc_html__( 'Show count', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}//end method _register_controls


	/**
	 * Convert yes/no to boolean on/off
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function yes_no_to_on_off( $value = '' ) {
		if ( $value === 'yes' ) {
			return 'on';
		}

		return 'off';
	}//end yes_no_to_on_off

	/**
	 * Convert yes/no switch to boolean 1/0
	 *
	 * @param string $value
	 *
	 * @return int
	 */
	public static function yes_no_to_1_0( $value = '' ) {
		if ( $value === 'yes' ) {
			return 1;
		}

		return 0;
	}//end yes_no_to_1_0

	/**
	 * Render my bookmarks widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		/*if ( ! class_exists( 'CBXWPBookmark_Settings_API' ) ) {
			require_once plugin_dir_path( dirname(dirname( __FILE__ ) )) . 'includes/class-cbxwpbookmark-setting.php';
		}

		$settings_api = new \CBXWPBookmark_Settings_API();*/

		$settings = $this->get_settings();

		$attr = array();

		$attr['show_count'] = $this->yes_no_to_1_0( $settings['show_count'] );


		$attr = apply_filters( 'cbxwpbookmark_elementor_shortcode_builder_attr', $attr, $settings, 'cbxwpbookmarkbtn' );

		$attr_html = '';

		foreach ( $attr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		echo do_shortcode( '[cbxwpbookmarkbtn ' . $attr_html . ']' );
	}//end method render

	/**
	 * Render my bookmarks widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _content_template() {
	}//end method _content_template
}//end method CBXWPBookmarkbtn_ElemWidget
