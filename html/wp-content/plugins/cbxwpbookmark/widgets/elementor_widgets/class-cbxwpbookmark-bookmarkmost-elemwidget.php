<?php

namespace CBXWPBookmark_ElemWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Most Bookmarked Post Elementor Widget
 */
class CBXWPBookmarkmost_ElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve most bookmarked posts widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxwpbookmarkmost';
	}

	/**
	 * Retrieve most bookmarked posts widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return esc_html__( 'CBX Most Bookmarked Posts', 'cbxwpbookmark' );
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
	 * Retrieve most bookmarked posts widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'cbxwpbookmars-most-list-icon';
	}

	/**
	 * Register most bookmarked posts widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_cbxwpbookmarkmost',
			array(
				'label' => esc_html__( 'Most Bookmarked Posts Settings', 'cbxwpbookmark' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title', 'cbxwpbookmark' ),
				'description' => esc_html__( 'Keep empty to hide', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Most Bookmarked Posts', 'cbxwpbookmark' ),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'       => esc_html__( 'Display order', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'DESC',
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
				'default'     => 'object_count',
				'placeholder' => esc_html__( 'Select order by', 'cbxwpbookmark' ),
				'options'     => array(
					'object_count' => esc_html__( 'Bookmark Count', 'cbxwpbookmark' ),
					'id'           => esc_html__( 'Bookmark id', 'cbxwpbookmark' ),
					'object_id'    => esc_html__( 'Post ID', 'cbxwpbookmark' ),
					'object_type'  => esc_html__( 'Post Type', 'cbxwpbookmark' ),
					'title'        => esc_html__( 'Post Title', 'cbxwpbookmark' ),
				)
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'Limit', 'cbxwpbookmark' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 10,
				'min'     => 1,
				'step'    => 1
			)
		);

		$object_types = \CBXWPBookmarkHelper::object_types( true );

		$this->add_control(
			'type',
			array(
				'label'       => esc_html__( 'Post type(s)', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => array(),
				'placeholder' => esc_html__( 'Select post type(s)', 'cbxwpbookmark' ),
				'options'     => $object_types,
				'multiple'    => true,
				'label_block' => true

			)
		);

		$this->add_control(
			'daytime',
			array(
				'label'       => esc_html__( 'Day(s)', 'cbxwpbookmark' ),
				'description' => esc_html__( '0 means all time', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'step'        => 1
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

		$this->add_control(
			'show_thumb',
			array(
				'label'        => esc_html__( 'Show thumbnail', 'cbxwpbookmark' ),
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
	 * Render most bookmarked posts widget output on the frontend.
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

		$type = $settings['type'];
		if ( is_array( $type ) ) {
			$type = array_filter( $type );
			$type = implode( ',', $type );
		} else {
			$type = '';
		}


		$attr['title']      = sanitize_text_field( $settings['title'] );
		$attr['order']      = sanitize_text_field( $settings['order'] );
		$attr['orderby']    = sanitize_text_field( $settings['orderby'] );
		$attr['limit']      = intval( $settings['limit'] );
		$attr['type']       = $type;
		$attr['daytime']    = intval( $settings['daytime'] );
		$attr['show_count'] = $this->yes_no_to_1_0( $settings['show_count'] );
		$attr['show_thumb'] = $this->yes_no_to_1_0( $settings['show_thumb'] );

		$attr = apply_filters( 'cbxwpbookmark_elementor_shortcode_builder_attr', $attr, $settings, 'cbxwpbookmark-most' );

		$attr_html = '';

		foreach ( $attr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		echo do_shortcode( '[cbxwpbookmark-most ' . $attr_html . ']' );
	}//end method render

	/**
	 * Render most bookmarked posts widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _content_template() {
	}//end method _content_template
}//end method CBXWPBookmarkmost_ElemWidget
