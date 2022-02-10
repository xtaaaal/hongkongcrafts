<?php

namespace CBXWPBookmark_ElemWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX My Bookmarks Elementor Widget
 */
class CBXWPBookmarks_ElemWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve my bookmarks widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'cbxwpbookmarks';
	}

	/**
	 * Retrieve my bookmarks widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return esc_html__( 'CBX My Bookmarked Posts', 'cbxwpbookmark' );
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
	 * Retrieve my bookmarks widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'cbxwpbookmars-post-list-icon';
	}

	/**
	 * Register my bookmarks widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_cbxwpbookmarks',
			array(
				'label' => esc_html__( 'CBX My Bookmarks Settings', 'cbxwpbookmark' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title', 'cbxwpbookmark' ),
				'description' => esc_html__( 'Keep empty to hide', 'cbxwpbookmark' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'All Bookmarks', 'cbxwpbookmark' ),
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
				'default'     => 'id',
				'placeholder' => esc_html__( 'Select order by', 'cbxwpbookmark' ),
				'options'     => array(
					'id'          => esc_html__( 'Bookmark id', 'cbxwpbookmark' ),
					'object_id'   => esc_html__( 'Post ID', 'cbxwpbookmark' ),
					'object_type' => esc_html__( 'Post Type', 'cbxwpbookmark' ),
					'title'       => esc_html__( 'Post Title', 'cbxwpbookmark' ),
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
			'loadmore',
			array(
				'label'        => esc_html__( 'Show load more', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'catid',
			array(
				'label'   => esc_html__( 'Category ID', 'cbxwpbookmark' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => ''
			)
		);

		$this->add_control(
			'cattitle',
			array(
				'label'        => esc_html__( 'Show category title', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);


		$this->add_control(
			'catcount',
			array(
				'label'        => esc_html__( 'Show category count', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'allowdelete',
			array(
				'label'        => esc_html__( 'Allow Delete', 'cbxwpbookmark' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'label_off'    => esc_html__( 'No', 'cbxwpbookmark' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'allowdeleteall',
			array(
				'label'        => esc_html__( 'Allow Delete All', 'cbxwpbookmark' ),
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

		$type = $settings['type'];
		if ( is_array( $type ) ) {
			$type = array_filter( $type );
			$type = implode( ',', $type );
		} else {
			$type = '';
		}


		$attr['title']          = sanitize_text_field( $settings['title'] );
		$attr['order']          = sanitize_text_field( $settings['order'] );
		$attr['orderby']        = sanitize_text_field( $settings['orderby'] );
		$attr['limit']          = intval( $settings['limit'] );
		$attr['type']           = $type;
		$attr['catid']          = $settings['catid'];
		$attr['loadmore']       = $this->yes_no_to_1_0( $settings['loadmore'] );
		$attr['cattitle']       = $this->yes_no_to_1_0( $settings['cattitle'] );
		$attr['catcount']       = $this->yes_no_to_1_0( $settings['catcount'] );
		$attr['allowdelete']    = $this->yes_no_to_1_0( $settings['allowdelete'] );
		$attr['allowdeleteall'] = $this->yes_no_to_1_0( $settings['allowdeleteall'] );

		$attr = apply_filters( 'cbxwpbookmark_elementor_shortcode_builder_attr', $attr, $settings, 'cbxwpbookmark' );

		$attr_html = '';

		foreach ( $attr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		echo do_shortcode( '[cbxwpbookmark ' . $attr_html . ']' );
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
}//end method CBXWPBookmarks_ElemWidget
