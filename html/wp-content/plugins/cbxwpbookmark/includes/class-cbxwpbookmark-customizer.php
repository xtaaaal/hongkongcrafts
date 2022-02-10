<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The customizer specific functionality of the plugin.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */


/**
 * The customizer specific functionality of the plugin.
 *
 * This class is used to register the customizer sections, panel, setting and control
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Customizer {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Constructor.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'customize_register', array( $this, 'add_sections' ) );


		add_action( 'customize_controls_print_styles', array( $this, 'add_styles' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'add_scripts' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) ); //for frontend
	}

	/**
	 * Add settings to the customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function add_sections( $wp_customize ) {
		//load custom controls
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-customizer-select.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-customizer-checkbox.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-customizer-switch.php';

		$wp_customize->add_panel( 'cbxwpbookmark', array(
			'priority'       => 200,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => esc_html__( 'CBX Bookmark & Favorite', 'cbxwpbookmark' ),
		) );


		$this->add_section_shortcodes( $wp_customize );
	}//end add_sections

	/**
	 * Bookmark shortcodes
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function add_section_shortcodes( $wp_customize ) {
		$wp_customize->add_section(
			'cbxwpbookmark_customizer_shortcodes',
			array(
				'title'    => esc_html__( 'Shortcodes/Functionalities', 'cbxwpbookmark' ),
				'priority' => 10,
				'panel'    => 'cbxwpbookmark',
			)
		);


		//shortcode fields
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[shortcodes]',
			array(
				'default'    => 'cbxwpbookmark-mycat,cbxwpbookmark',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Checkbox(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcodes',
				array(
					'label'       => esc_html__( 'Select Bookmark Shortcodes', 'cbxwpbookmark' ),
					'description' => esc_html__( 'Select which bookmark shortcodes', 'cbxwpbookmark' ),
					'section'     => 'cbxwpbookmark_customizer_shortcodes',
					'settings'    => 'cbxwpbookmark_customizer[shortcodes]',
					'type'        => 'cbxwpbookmark_checkbox',
					'default'     => 'cbxwpbookmark-mycat,cbxwpbookmark',
					'choices'     => apply_filters( 'cbxwpbookmark_customizer_shortcodes_choices', array(
						'cbxwpbookmark-mycat' => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
						'cbxwpbookmark'       => esc_html__( 'Bookmark List', 'cbxwpbookmark' ),
						//'cbxwpbookmarkgrid'   => esc_html__( 'Bookmark Grid', 'cbxwpbookmark' ),
					) ),
					'input_attrs' => array(
						'placeholder' => esc_html__( 'Please select shortcode(s)', 'cbxwpbookmark' ),
						'sortable'    => true,
						'fullwidth'   => true,
					)
				)
			)
		);
		//end shortcode fields

		//category shortcode
		$wp_customize->add_section(
			'cbxwpbookmark_customizer_shortcode_category',
			array(
				'title'    => esc_html__( 'Shortcode Params: Bookmark Categories', 'cbxwpbookmark' ),
				'priority' => 10,
				'panel'    => 'cbxwpbookmark',
			)
		);

		//title
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][title]',
			array(
				'default'           => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_category_title',
			array(
				'label'    => esc_html__( 'Title', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_category',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][title]',
				'type'     => 'text',
				'default'  => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
			)
		);

		//order
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][order]',
			array(
				'default'    => 'ASC',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_category_order',
			array(
				'label'    => esc_html__( 'Order', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_category',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][order]',
				'type'     => 'select',
				'default'  => 'ASC',
				'choices'  => array(
					'ASC'  => esc_html__( 'Ascending', 'cbxwpbookmark' ),
					'DESC' => esc_html__( 'Descending', 'cbxwpbookmark' )
				)
			)
		);

		//orderby
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][orderby]',
			array(
				'default'    => 'cat_name',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_category_orderby',
			array(
				'label'    => esc_html__( 'Order By', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_category',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][orderby]',
				'type'     => 'select',
				'default'  => 'cat_name',
				'choices'  => array(
					'cat_name' => esc_html__( 'Category Title', 'cbxwpbookmark' ),
					'id'       => esc_html__( 'Category ID', 'cbxwpbookmark' ),
					'privacy'  => esc_html__( 'Category Privacy', 'cbxwpbookmark' )
				)
			)
		);

		//privacy
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][privacy]',
			array(
				'default'    => '2',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_category_privacy',
			array(
				'label'    => esc_html__( 'Privacy', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_category',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][privacy]',
				'type'     => 'select',
				'default'  => '2',
				'choices'  => array(
					'2' => esc_html__( 'Ignore Privacy', 'cbxwpbookmark' ),
					'1' => esc_html__( 'Public', 'cbxwpbookmark' ),
					'0' => esc_html__( 'Privacy', 'cbxwpbookmark' )
				)
			)
		);

		//display format
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][display]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_category_display',
			array(
				'label'    => esc_html__( 'Display Format', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_category',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][display]',
				'type'     => 'select',
				'default'  => '0',
				'choices'  => array(
					'0' => esc_html__( 'List', 'cbxwpbookmark' ),
					'1' => esc_html__( 'Dropdown', 'cbxwpbookmark' ),
				)
			)
		);

		//show_count
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][show_count]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_category_show_count',
				array(
					'label'             => esc_html__( 'Show Count', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_category',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][show_count]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '0',
					'sanitize_callback' => array( $this, 'absint' )
					/*'choices'  => array(
						'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
					)*/
				)
			)
		);

		//allowedit
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][allowedit]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_category_allowedit',
				array(
					'label'             => esc_html__( 'Allow Edit', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_category',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][allowedit]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '0',
					'sanitize_callback' => array( $this, 'absint' )
					/*'choices'  => array(
						'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
					)*/
				)
			)
		);

		//show_bookmarks
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark-mycat][show_bookmarks]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_category_show_bookmarks',
				array(
					'label'             => esc_html__( 'Show Bookmark Sublist', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_category',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark-mycat][show_bookmarks]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '0',
					'sanitize_callback' => array( $this, 'absint' )
					/*'choices'  => array(
						'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
					)*/
				)
			)
		);


		//cbxwpbookmark shortcode
		$wp_customize->add_section(
			'cbxwpbookmark_customizer_shortcode_bookmarks',
			array(
				'title'    => esc_html__( 'Shortcode Params: Bookmark List', 'cbxwpbookmark' ),
				'priority' => 10,
				'panel'    => 'cbxwpbookmark'
			)
		);

		//title
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][title]',
			array(
				'default'    => esc_html__( 'All Bookmarks', 'cbxwpbookmark' ),
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarks_title',
			array(
				'label'    => esc_html__( 'Title', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarks',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark][title]',
				'type'     => 'text',
				'default'  => esc_html__( 'All Bookmarks', 'cbxwpbookmark' )
			)
		);

		//order
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][order]',
			array(
				'default'    => 'DESC',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarks_order',
			array(
				'label'    => esc_html__( 'Order', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarks',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark][order]',
				'type'     => 'select',
				'default'  => 'DESC',
				'choices'  => array(
					'DESC' => esc_html__( 'Descending', 'cbxwpbookmark' ),
					'ASC'  => esc_html__( 'Ascending', 'cbxwpbookmark' )
				)
			)
		);

		//orderby
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][orderby]',
			array(
				'default'    => 'id',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarks_orderby',
			array(
				'label'    => esc_html__( 'Order By', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarks',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark][orderby]',
				'type'     => 'select',
				'default'  => 'id',
				'choices'  => array(
					'id'          => esc_html__( 'ID', 'cbxwpbookmark' ),
					'object_id'   => esc_html__( 'Post ID', 'cbxwpbookmark' ),
					'object_type' => esc_html__( 'Post Type', 'cbxwpbookmark' ),
					'title'       => esc_html__( 'Post Title', 'cbxwpbookmark' ),
				)
			)
		);

		//limit
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][limit]',
			array(
				'default'           => '10',
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => array( $this, 'sanitize_number_field' )
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarks_limit',
			array(
				'label'       => esc_html__( 'Limit', 'cbxwpbookmark' ),
				'section'     => 'cbxwpbookmark_customizer_shortcode_bookmarks',
				'settings'    => 'cbxwpbookmark_customizer[cbxwpbookmark][limit]',
				'type'        => 'number',
				'default'     => '10',
				'input_attrs' => array(
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				)
			)
		);

		//https://github.com/maddisondesigns/customizer-custom-controls/blob/master/inc/customizer.php
		//solution https://raw.githubusercontent.com/maddisondesigns/customizer-custom-controls/master/inc/custom-controls.php

		//type
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][type]',
			array(
				'default'           => '',
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => array( $this, 'text_sanitization' )
			)
		);


		$object_types = CBXWPBookmarkHelper::object_types_customizer_format();


		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Select2(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarks_type',
				array(
					'label'       => esc_html__( 'Post Type(s)', 'cbxwpbookmark' ),
					'section'     => 'cbxwpbookmark_customizer_shortcode_bookmarks',
					'settings'    => 'cbxwpbookmark_customizer[cbxwpbookmark][type]',
					'type'        => 'cbxwpbookmark_select2',
					'default'     => '',
					'choices'     => $object_types,
					'input_attrs' => array(
						'placeholder' => esc_html__( 'Please select post type(s)', 'cbxwpbookmark' ),
						'multiselect' => true,
					)
				)
			)
		);

		//loadmore
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][loadmore]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarks_loadmore',
				array(
					'label'             => esc_html__( 'Show Load More', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarks',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark][loadmore]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
					/*'choices'  => array(
						'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						'0' => esc_html__( 'No', 'cbxwpbookmark' )
					)*/
				)
			)
		);

		//catid
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][catid]',
			array(
				'default'    => '',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarks_catid',
			array(
				'label'    => esc_html__( 'Category ID', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarks',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmark][catid]',
				'type'     => 'text',
				'default'  => ''
			)
		);

		//cattitle
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][cattitle]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarks_cattitle',
				array(
					'label'             => esc_html__( 'Show category title', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarks',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark][cattitle]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
					/*'choices'  => array(
						'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						'0' => esc_html__( 'No', 'cbxwpbookmark' )
					),*/
				)
			)
		);

		//catcount
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][catcount]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarks_catcount',
				array(
					'label'             => esc_html__( 'Show item count per category', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarks',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark][catcount]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
					/*'choices'  => array(
						'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						'0' => esc_html__( 'No', 'cbxwpbookmark' )
					),*/
				)
			)
		);

		//allowdelete
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][allowdelete]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);


		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarks_allowdelete',
				array(
					'label'             => esc_html__( 'Allow Delete', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarks',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark][allowdelete]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '0',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);

		//allowdeleteall
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmark][allowdeleteall]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);


		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarks_allowdeleteall',
				array(
					'label'             => esc_html__( 'Allow Delete All', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarks',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmark][allowdeleteall]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '0',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);


		/*//cbxwpbookmarkgrid shortcode
		$wp_customize->add_section(
			'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
			array(
				'title'    => esc_html__( 'Shortcode Params: Bookmark Grid', 'cbxwpbookmark' ),
				'priority' => 10,
				'panel'    => 'cbxwpbookmark',
			)
		);

		//order
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][order]',
			array(
				'default'    => 'DESC',
				'type'       => 'option',
				'capability' => 'manage_options',

			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarkgrid_order',
			array(
				'label'    => esc_html__( 'Order', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][order]',
				'type'     => 'select',
				'default'  => 'DESC',
				'choices'  => array(
					'DESC' => esc_html__( 'Descending', 'cbxwpbookmark' ),
					'ASC'  => esc_html__( 'Ascending', 'cbxwpbookmark' )
				),
			)
		);

		//orderby
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][orderby]',
			array(
				'default'    => 'id',
				'type'       => 'option',
				'capability' => 'manage_options',

			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarkgrid_orderby',
			array(
				'label'    => esc_html__( 'Order By', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][orderby]',
				'type'     => 'select',
				'default'  => 'id',
				'choices'  => array(
					'id'          => esc_html__( 'ID', 'cbxwpbookmark' ),
					'object_id'   => esc_html__( 'Post ID', 'cbxwpbookmark' ),
					'object_type' => esc_html__( 'Post Type', 'cbxwpbookmark' )
				),
			)
		);

		//limit
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][limit]',
			array(
				'default'           => '10',
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => array( $this, 'sanitize_number_field' ),
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarkgrid_limit',
			array(
				'label'       => esc_html__( 'Limit', 'cbxwpbookmark' ),
				'section'     => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
				'settings'    => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][limit]',
				'type'        => 'number',
				'default'     => '10',
				'input_attrs' => array(
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				),
			)
		);

		//type
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][type]',
			array(
				'default'           => '',
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => array( $this, 'text_sanitization' ),
			)
		);


		$object_types = CBXWPBookmarkHelper::object_types_customizer_format();


		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Select2(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarkgrid_type',
				array(
					'label'       => esc_html__( 'Post Type(s)', 'cbxwpbookmark' ),
					'section'     => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
					'settings'    => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][type]',
					'type'        => 'cbxwpbookmark_select2',
					'default'     => '',
					'choices'     => $object_types,
					'input_attrs' => array(
						'placeholder' => esc_html__( 'Please select post type(s)', 'cbxwpbookmark' ),
						'multiselect' => true,
					),
				)
			)
		);

		//loadmore
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][loadmore]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarkgrid_loadmore',
				array(
					'label'             => esc_html__( 'Show Load More', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][loadmore]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);

		//catid
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][catid]',
			array(
				'default'    => '',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			'cbxwpbookmark_customizer_shortcode_bookmarkgrid_catid',
			array(
				'label'    => esc_html__( 'Category ID', 'cbxwpbookmark' ),
				'section'  => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
				'settings' => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][catid]',
				'type'     => 'text',
				'default'  => ''
			)
		);

		//cattitle
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][cattitle]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarkgrid_cattitle',
				array(
					'label'             => esc_html__( 'Show category title', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][cattitle]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);

		//catcount
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][catcount]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarkgrid_catcount',
				array(
					'label'             => esc_html__( 'Show item count per category', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][catcount]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);

		//allowdelete
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][allowdelete]',
			array(
				'default'    => '0',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);


		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarkgrid_allowdelete',
				array(
					'label'             => esc_html__( 'Allow Delete', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][allowdelete]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '0',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);

		//show_thumb
		$wp_customize->add_setting(
			'cbxwpbookmark_customizer[cbxwpbookmarkgrid][show_thumb]',
			array(
				'default'    => '1',
				'type'       => 'option',
				'capability' => 'manage_options'
			)
		);


		$wp_customize->add_control(
			new CBXWPBookmark_Customizer_Control_Switch(
				$wp_customize,
				'cbxwpbookmark_customizer_shortcode_bookmarkgrid_show_thumb',
				array(
					'label'             => esc_html__( 'Show Thumbnail', 'cbxwpbookmark' ),
					'section'           => 'cbxwpbookmark_customizer_shortcode_bookmarkgrid',
					'settings'          => 'cbxwpbookmark_customizer[cbxwpbookmarkgrid][show_thumb]',
					'type'              => 'cbxwpbookmark_switch',
					'default'           => '1',
					'sanitize_callback' => array( $this, 'absint' )
				)
			)
		);*/

		do_action( 'cbxwpbookmark_customizer_shortcode_controls', $wp_customize, $this );
	}//end add_section_shortcodes

	/**
	 * Number field sanitization
	 *
	 * @param $number
	 * @param $setting
	 *
	 * @return int
	 */
	public function sanitize_number_field( $number, $setting ) {
		// Ensure $number is an absolute integer (whole number, zero or greater).
		$number = absint( $number );

		// If the input is an absolute integer, return it; otherwise, return the default
		return ( $number ? $number : $setting->default );
	}//end sanitize_number_field

	/**
	 * Post type sanitization
	 *
	 * @param $number
	 * @param $setting
	 *
	 * @return int
	 */
	public function sanitize_post_types( $types, $setting ) {
		$types = wp_unslash( $types );

		return $types;
	}//end sanitize_post_types

	/**
	 * Frontend CSS styles.
	 */
	public function add_frontend_scripts() {
		if ( ! is_customize_preview() ) {
			return;
		}
	}//end add_frontend_scripts

	/**
	 * Styles to improve our form.
	 */
	public function add_styles() {
		wp_register_style( 'select2', plugin_dir_url( __FILE__ ) . '../assets/vendors/select2/css/select2.min.css', array(), $this->version );
		wp_register_style( 'cbxwpbookmark-customizer', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-customizer.css', array( 'select2' ), $this->version );
		wp_enqueue_style( 'cbxwpbookmark-customizer' );
	}//end add_styles

	/**
	 * Scripts to improve our form.
	 */
	public function add_scripts() {
		wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . '../assets/vendors/select2/js/select2.full.min.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'cbxwpbookmark-customizer', plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-customizer.js', array(
			'jquery',
			'select2'
		), $this->version, true );
		$cbxwpbookmark_customizer_js_vars = apply_filters( 'cbxwpbookmark_customizer_js_vars',
			array(
				'please_select'           => esc_html__( 'Please Select', 'cbxwpbookmark' ),
				'please_select_shortcode' => esc_html__( 'Please Select Shortcodes', 'cbxwpbookmark' ),
				'upload_title'            => esc_html__( 'Window Title', 'cbxwpbookmark' ),
				//'cbxbookmark_lang'        => get_user_locale(),
			) );

		wp_localize_script( 'cbxwpbookmark-customizer', 'cbxwpbookmark_customizer', $cbxwpbookmark_customizer_js_vars );
		wp_enqueue_script( 'cbxwpbookmark-customizer' );


		?>
        <script type="text/javascript">
            /*jQuery( document ).ready( function( $ ) {

			});*/
        </script>
		<?php
	}//end add_scripts

	public function text_sanitization( $input ) {
		if ( strpos( $input, ',' ) !== false ) {
			$input = explode( ',', $input );
		}

		if ( is_array( $input ) ) {
			foreach ( $input as $key => $value ) {
				$input[ $key ] = sanitize_text_field( $value );
			}
			$input = implode( ',', $input );
		} else {
			$input = sanitize_text_field( $input );
		}

		return $input;
	}//end text_sanitization
}//end class CBXWPBookmark_Customizer