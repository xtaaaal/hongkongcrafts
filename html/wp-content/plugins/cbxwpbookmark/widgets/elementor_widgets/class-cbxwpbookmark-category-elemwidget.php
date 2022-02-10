<?php

namespace CBXWPBookmark_ElemWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Bookmark Category Elementor Widget
 */
class CBXWPBookmarkCategory_ElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve category widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxwpbookmarkcategory';
	}

	/**
	 * Retrieve category widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return esc_html__( 'CBX Bookmark Categories', 'cbxwpbookmark' );
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
	 * Retrieve category widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'cbxwpbookmars-category-icon';
	}

	/**
	 * Register category widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_cbxwpbookmarkcategory',
			array(
				'label' => esc_html__( 'Bookmark Categories Settings', 'cbxwpbookmark' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title', 'cbxwpbookmark' ),
				'description' => esc_html__( 'Keep empty to hide', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'       => esc_html__( 'Display order', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'ASC',
				'placeholder' => esc_html__( 'Select order', 'cbxwpbookmark' ),
				'options'     => array(
					'ASC'  => esc_html__( 'Ascending', 'cbxwpbookmark' ),
					'DESC' => esc_html__( 'Descending', 'cbxwpbookmark' ),
				)
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'       => esc_html__( 'Display order by', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'cat_name',
				'placeholder' => esc_html__( 'Select order by', 'cbxwpbookmark' ),
				'options'     => array(
					'cat_name' => esc_html__( 'Category Name', 'cbxwpbookmark' ),
					'id'       => esc_html__( 'Category Id', 'cbxwpbookmark' ),
					'privacy'  => esc_html__( 'Privacy', 'cbxwpbookmark' ),
				)
			)
		);

		$this->add_control(
			'privacy',
			array(
				'label'       => esc_html__( 'Privacy', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 2,
				'placeholder' => esc_html__( 'Select privacy', 'cbxwpbookmark' ),
				'options'     => array(
					'2' => esc_html__( 'Ignore privacy', 'cbxwpbookmark' ),
					'1' => esc_html__( 'Public', 'cbxwpbookmark' ),
					'0' => esc_html__( 'Private', 'cbxwpbookmark' ),
				)
			)
		);

		$this->add_control(
			'display',
			array(
				'label'       => esc_html__( 'Display method', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 0,
				'placeholder' => esc_html__( 'Select display', 'cbxwpbookmark' ),
				'options'     => array(
					'0' => esc_html__( 'List', 'cbxwpbookmark' ),
					'1' => esc_html__( 'Dropdown', 'cbxwpbookmark' )
				)
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
				'default'      => 'no',
			)
		);

		$this->add_control(
			'allowedit',
			array(
				'label'        => esc_html__( 'Allow Edit', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);


		$this->add_control(
			'show_bookmarks',
			array(
				'label'        => esc_html__( 'Show bookmarks as sublist', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'no',
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
	 * Render category widget output on the frontend.
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

		$attr['title']          = sanitize_text_field( $settings['title'] );
		$attr['order']          = sanitize_text_field( $settings['order'] );
		$attr['orderby']        = sanitize_text_field( $settings['orderby'] );
		$attr['privacy']        = intval( $settings['privacy'] );
		$attr['display']        = intval( $settings['display'] );
		$attr['show_count']     = $this->yes_no_to_1_0( $settings['show_count'] );
		$attr['show_bookmarks'] = $this->yes_no_to_1_0( $settings['show_bookmarks'] );
		$attr['allowedit']      = $this->yes_no_to_1_0( $settings['allowedit'] );

		$attr = apply_filters( 'cbxwpbookmark_elementor_shortcode_builder_attr', $attr, $settings, 'cbxwpbookmark-mycat' );

		$attr_html = '';

		foreach ( $attr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		echo do_shortcode( '[cbxwpbookmark-mycat ' . $attr_html . ']' );
	}//end method render

	/**
	 * Render category widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _content_template() {
	}//end method _content_template
}//end method CBXWPBookmarkCategory_ElemWidget
