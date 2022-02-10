<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/admin
 */


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/admin
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Admin {

	/**
	 * The plugin basename of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_basename The plugin basename of the plugin.
	 */
	protected $plugin_basename;
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
	 * The settings api of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $settings_api settings api of this plugin.
	 */
	private $settings_api;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $cbxwpbookmark The ID of this plugin.
	 */
	private $cbxwpbookmark;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->cbxwpbookmark = $plugin_name;
		$this->plugin_name   = $plugin_name;
		$this->version       = $version;

		$this->version = $version;
		if ( defined( 'WP_DEBUG' ) ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}

		$this->plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->cbxwpbookmark . '.php' );

		$this->settings_api = new CBXWPBookmark_Settings_API();

		/*set_transient('cbxwpbookmark_fullreset_message_1', '1');
		set_transient('cbxwpbookmark_fullreset_message_2', '2');*/
	}//end constructor

	public function setting_init() {
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}//end setting_init

	/**
	 * Tab Defination
	 *
	 * @return array
	 */
	public function get_settings_sections() {
		return apply_filters( 'cbxwpbookmark_setting_sections',
			array(
				array(
					'id'    => 'cbxwpbookmark_basics',
					'title' => esc_html__( 'General Settings', 'cbxwpbookmark' ),
				),
				array(
					'id'    => 'cbxwpbookmark_tools',
					'title' => esc_html__( 'Tools', 'cbxwpbookmark' ),
				),
			)
		);
	}//end get_settings_sections


	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	public function get_settings_fields() {
		$settings_api = $settings = $this->settings_api;

		global $wp_roles;
		// now this is for meta box
		$roles = CBXWPBookmarkHelper::user_roles( false, true );


		$posts_definition = CBXWPBookmarkHelper::post_types_multiselect( CBXWPBookmarkHelper::post_types() );


		$post_types_automation_default = $settings->get_option( 'cbxbookmarkposttypes', 'cbxwpbookmark_basics', array());

		if(!is_array($post_types_automation_default)) $post_types_automation_default = array();


		$posts_definition_automation = array();

		foreach ($posts_definition as $group_name => $post_types){
			foreach ($post_types as $post_type_key => $post_type_name){
			    if(in_array($post_type_key, $post_types_automation_default)) $posts_definition_automation[$post_type_key] = $post_type_name;
            }
		}


		$reset_data_link = add_query_arg( 'cbxwpbookmark_fullreset', 1, admin_url( 'admin.php?page=cbxwpbookmark_settings' ) );

		$table_names = CBXWPBookmarkHelper::getAllDBTablesList();

		$table_html = '<p><a class="button button-primary" id="cbxwpbookmark_info_trig" href="#">' . esc_html__( 'Show/hide details', 'cbxwpbookmark' ) . '</a></p>';
		$table_html .= '<div id="cbxwpbookmark_resetinfo" style="display: none;">';
		$table_html .= '<p id="cbxwpbookmark_plg_gfig_info"><strong>' . esc_html__( 'Following database tables will be reset/deleted.', 'cbxwpbookmark' ) . '</strong></p>';

		$table_counter = 1;
		foreach ( $table_names as $key => $value ) {
			$table_html .= '<p>' . str_pad( $table_counter, 2, '0', STR_PAD_LEFT ) . '. ' . $key . ' - (<code>' . $value . '</code>)</p>';
			$table_counter ++;
		}

		$table_html .= '<p><strong>' . esc_html__( 'Following option values created by this plugin(including addon) from wordpress core option table', 'cbxwpbookmark' ) . '</strong></p>';


		$option_values = CBXWPBookmarkHelper::getAllOptionNames();
		$table_counter = 1;
		foreach ( $option_values as $key => $value ) {
			$table_html .= '<p>' . str_pad( $table_counter, 2, '0', STR_PAD_LEFT ) . '. ' . $value['option_name'] . ' - ' . $value['option_id'] . ' - (<code style="overflow-wrap: break-word; word-break: break-all;">' . $value['option_value'] . '</code>)</p>';

			$table_counter ++;
		}

		$table_html .= '</div>';


		$pages         = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}


		$mybookmark_pageid = absint( $settings_api->get_option( 'mybookmark_pageid', 'cbxwpbookmark_basics', 0 ) );

		$mybookmark_pageid_link_html = '';
		if ( $mybookmark_pageid > 0 ) {
			$mybookmark_pageid_link      = cbxwpbookmarks_mybookmark_page_url();
			$mybookmark_pageid_link_html = sprintf( __( 'Visit <a class="button" href="%s" target="_blank">My Bookmarks</a> Page', 'cbxwpbookmark' ), $mybookmark_pageid_link );
		} else {
			$mybookmark_pageid_link_html = esc_html__( 'My Bookmark Page doesn\'t exists.', 'cbxwpbookmark' ) . ' ' . __( 'Please <a data-busy="0" id="cbxwpbookmark_autocreate_page" class="button" href="#" target="_blank">click here</a> to create. If <strong>My Bookmark Page Method</strong> is <strong>Customizer</strong> then only page will be created without shortcode as shortcode is not needed for customizer method.', 'cbxwpbookmark' );
		}

		$mybookmark_customizer_url_html = '';
		if ( $mybookmark_pageid > 0 ) {
			$mybookmark_customizer_url      = add_query_arg( array(
				'autofocus' => array( 'panel' => 'cbxwpbookmark' ),
				'url'       => cbxwpbookmarks_mybookmark_page_url()
			), admin_url( 'customize.php' ) );
			$mybookmark_customizer_url_html = '<a class="button button-primary" href="' . esc_url( $mybookmark_customizer_url ) . '">' . esc_html__( 'Configure using customizer', 'cbxwpbookmark' ) . '</a>';
		} else {
			$mybookmark_customizer_url_html = __( 'To configure <strong>My Bookmarks</strong> page using customizer please create a page and set as my bookmark page using above setting.', 'cbxwpbookmark' );
		}

		$gust_login_forms = CBXWPBookmarkHelper::guest_login_forms();
		$bookmarks_themes = CBXWPBookmarkHelper::themes();

		$settings_builtin_fields =
			array(
				'cbxwpbookmark_basics' => array(
					'basics_heading'     => array(
						'name'    => 'basics_heading',
						'label'   => esc_html__( 'General Settings', 'cbxwpbookmark' ),
						'type'    => 'heading',
						'default' => '',
					),
					'display_theme'      => array(
						'name'    => 'display_theme',
						'label'   => esc_html__( 'Select Theme', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Select predefine theme.', 'cbxwpbookmark' ),
						'type'    => 'select',
						'default' => 'cbxwpbookmark-default',
						'options' => $bookmarks_themes,
					),
					'display_label'      => array(
						'name'    => 'display_label',
						'label'   => esc_html__( 'Display Bookmark Label', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Display the label Bookmark or Bookmarked. This param has no shortcode method, if enabled works everywhere, if disabled then same.', 'cbxwpbookmark' ),
						'type'    => 'select',
						'default' => '1',
						'options' => array(
							'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
							'0' => esc_html__( 'No', 'cbxwpbookmark' ),

						),
					),
					'bookmark_label'     => array(
						'name'    => 'bookmark_label',
						'label'   => esc_html__( 'Bookmark Label', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Example: Bookmark. If empty then label will be used from translation', 'cbxwpbookmark' ),
						'type'    => 'text',
						'default' => '',
					),
					'bookmarked_label'   => array(
						'name'    => 'bookmarked_label',
						'label'   => esc_html__( 'Bookmarked Label', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Example: Bookmarked. If empty then label will be used from translation', 'cbxwpbookmark' ),
						'type'    => 'text',
						'default' => '',
					),
					'bookmark_mode'      => array(
						'name'    => 'bookmark_mode',
						'label'   => esc_html__( 'Bookmark Mode', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Default is category belongs to user, other two mode is global category and no category quick bookmark.', 'cbxwpbookmark' ),
						'type'    => 'select',
						'default' => 'user_cat',
						'options' => array(
							'user_cat'   => esc_html__( 'User owns category', 'cbxwpbookmark' ),
							'global_cat' => esc_html__( 'Global Category', 'cbxwpbookmark' ),
							'no_cat'     => esc_html__( 'No Category(Single Click Bookmark)', 'cbxwpbookmark' ),
						),
					),
					'category_status'    => array(
						'name'    => 'category_status',
						'label'   => esc_html__( 'Category Default Status', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Category Default Status If user category mode is selected', 'cbxwpbookmark' ),
						'type'    => 'radio',
						'default' => '1',
						'options' => array(
							'1' => esc_html__( 'Public', 'cbxwpbookmark' ),
							'0' => esc_html__( 'Private', 'cbxwpbookmark' ),
						),
					),
					'hide_cat_privacy'   => array(
						'name'    => 'hide_cat_privacy',
						'label'   => esc_html__( 'Hide Category Privacy Field', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Hide category privacy field if user category mode is selected. Default status will be used from above setting. This feature does\'t disable the category feature but hides from user interface.', 'cbxwpbookmark' ),
						'type'    => 'radio',
						'default' => '0',
						'options' => array(
							'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
							'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						),
					),
					'cbxbookmarkpostion' => array(
						'name'    => 'cbxbookmarkpostion',
						'label'   => esc_html__( 'Auto Integration', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Bookmark button auto integration position', 'cbxwpbookmark' ),
						'type'    => 'select',
						'default' => 'after_content',
						'options' => array(
							'before_content' => esc_html__( 'Before Content', 'cbxwpbookmark' ),
							'after_content'  => esc_html__( 'After Content', 'cbxwpbookmark' ),
							'disable'        => esc_html__( 'Disable Auto Integration', 'cbxwpbookmark' ),
						),
					),
					'skip_ids'           => array(
						'name'     => 'skip_ids',
						'label'    => esc_html__( 'Skip Post Id(s)', 'cbxwpbookmark' ),
						'desc'     => esc_html__( 'Skip to show bookmark button for post id, put post id as comma separated for multiple', 'cbxwpbookmark' ),
						'type'     => 'text',
						'default'  => '',
						'desc_tip' => true,
					),
					'skip_roles'         => array(
						'name'     => 'skip_roles',
						'label'    => esc_html__( 'Skip for User Role', 'cbxwpbookmark' ),
						'desc'     => esc_html__( 'Skip to show bookmark button for user roles', 'cbxwpbookmark' ),
						'type'     => 'multiselect',
						'optgroup' => 1,
						'options'  => $roles,
						'default'  => array(),
						'desc_tip' => true,
					),
					'showinarchive'      => array(
						'name'    => 'showinarchive',
						'label'   => esc_html__( 'Show in Archive', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Show in Archive', 'cbxwpbookmark' ),
						'type'    => 'radio',
						'default' => '0',
						'options' => array(
							'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
							'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						),
					),
					'showinhome'         => array(
						'name'    => 'showinhome',
						'label'   => esc_html__( 'Show in Home', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Show in Home', 'cbxwpbookmark' ),
						'type'    => 'radio',
						'default' => '0',
						'options' => array(
							'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
							'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						),
					),

					'cbxbookmarkposttypes' => array(
						'name'     => 'cbxbookmarkposttypes',
						'label'    => esc_html__( 'Post Type Selection', 'cbxwpbookmark' ),
						'desc'     => esc_html__( 'Bookmark will work for selected post types', 'cbxwpbookmark' ),
						'type'     => 'multiselect',
						'optgroup' => 1,
						'default'  => array( 'post', 'page' ),
						'options'  => $posts_definition,
					),
					'post_types_automation' => array(
						'name'     => 'post_types_automation',
						'label'    => esc_html__( 'Post Type Auto Integration', 'cbxwpbookmark' ),
						'desc'     => esc_html__( 'For which post types auto integration will be used', 'cbxwpbookmark' ),
						'type'     => 'multiselect',
						'optgroup' => 0,
						'default'  => $post_types_automation_default,
						'options'  => $posts_definition_automation,
					),
					'showcount'            => array(
						'name'    => 'showcount',
						'label'   => esc_html__( 'Show count', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Show bookmark count', 'cbxwpbookmark' ),
						'type'    => 'radio',
						'default' => '1',
						'options' => array(
							'1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
							'0' => esc_html__( 'No', 'cbxwpbookmark' ),
						),
					),
					'mybookmark_pageid'    => array(
						'name'    => 'mybookmark_pageid',
						'label'   => esc_html__( 'My Bookmark Page', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'User\'s private(or public based on shortcode/customizer params) bookmark page.', 'cbxwpbookmark' ) . ' ' . $mybookmark_pageid_link_html,
						'type'    => 'select',
						'default' => 0,
						'options' => $pages_options,
					),
					'mybookmark_way'       => array(
						'name'    => 'mybookmark_way',
						'label'   => esc_html__( 'My Bookmark Page Method', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Shortcode method is old, customizer way is new and more easy. We recommend to use customizer and remove the shortcodes from that page related with this plugin. If the shortcode still exists in the my bookmarks page and  customizer method enabled still it will work.', 'cbxwpbookmark' ) . ' ' . $mybookmark_customizer_url_html,
						'type'    => 'select',
						'default' => 'shortcode',
						'options' => array(
							'customizer' => esc_html__( 'Customizer(Recommended)', 'cbxwpbookmark' ),
							'shortcode'  => esc_html__( 'Shortcode', 'cbxwpbookmark' ),
						),
					),
					'pop_z_index'          => array(
						'name'              => 'pop_z_index',
						'label'             => esc_html__( 'Bookmark Popup Z-Inxdex', 'cbxwpbookmark' ),
						'desc'              => esc_html__( 'Sometimes bookmark popup doesn\'t show properly or may not compatible with theme. Increasing the z-index value will help.', 'cbxwpbookmark' ),
						'type'              => 'text',
						'default'           => 1,
						'sanitize_callback' => 'absint'
					),
					'guest_login_form'     => array(
						'name'    => 'guest_login_form',
						'label'   => esc_html__( 'Guest User Login Form', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Default guest user is shown wordpress core login form. Pro addon helps to integrate 3rd party plugins like woocommerce, restrict content pro etc.', 'cbxwpbookmark' ),
						'type'    => 'select',
						'default' => 'wordpress',
						'options' => $gust_login_forms
					),
					'guest_show_register'  => array(
						'name'    => 'guest_show_register',
						'label'   => esc_html__( 'Show Register link to guest', 'cbxwpbookmark' ),
						'desc'    => esc_html__( 'Show register link to guest, depends on if registration is enabled in wordpress core', 'cbxwpbookmark' ),
						'type'    => 'radio',
						'default' => 1,
						'options' => array(
							1 => esc_html__( 'Yes', 'cbxwpbookmark' ),
							0 => esc_html__( 'No', 'cbxwpbookmark' ),
						),
					),

				),
				'cbxwpbookmark_tools'  => array(
					'tools_heading'        => array(
						'name'    => 'tools_heading',
						'label'   => esc_html__( 'Tools Settings', 'cbxwpbookmark' ),
						'type'    => 'heading',
						'default' => '',
					),
					'delete_global_config' => array(
						'name'    => 'delete_global_config',
						'label'   => esc_html__( 'On Uninstall delete plugin data', 'cbxwpbookmark' ),
						'desc'    => '<p>' . __( 'Delete Global Config data and custom table created by this plugin on uninstall.', 'cbxwpbookmark' ) . ' ' . __( 'Details table information is <a href="#cbxwpbookmark_plg_gfig_info">here</a>', 'cbxwpbookmark' ) . '</p>' . '<p>' . __( '<strong>Please note that this process can not be undone and it is recommended to keep full database backup before doing this.</strong>', 'cbxwpbookmark' ) . '</p>',
						'type'    => 'radio',
						'options' => array(
							'yes' => esc_html__( 'Yes', 'cbxwpbookmark' ),
							'no'  => esc_html__( 'No', 'cbxwpbookmark' ),
						),
						'default' => 'no',
					),
					'reset_data'           => array(
						'name'    => 'reset_data',
						'label'   => esc_html__( 'Reset all data', 'cbxwpbookmark' ),
						'desc'    => sprintf( __( 'Reset option values and all tables created by this plugin. 
<a class="button button-primary" onclick="return confirm(\'%s\')" href="%s">Reset Data</a>',
								'cbxwpbookmark' ),
								esc_html__( 'Are you sure to reset all data, this process can not be undone?', 'cbxwpbookmark' ),
								$reset_data_link ) . $table_html,
						'type'    => 'html',
						'default' => 'off',
					),

				),
			);

		$settings_fields = array(); //final setting array that will be passed to different filters

		$sections = $this->get_settings_sections();

		foreach ( $sections as $section ) {
			if ( ! isset( $settings_builtin_fields[ $section['id'] ] ) ) {
				$settings_builtin_fields[ $section['id'] ] = array();
			}
		}

		foreach ( $sections as $section ) {

			$settings_fields[ $section['id'] ] = apply_filters( 'cbxwpbookmark_global_' . $section['id'] . '_fields', $settings_builtin_fields[ $section['id'] ] );
		}

		$settings_fields = apply_filters( 'cbxwpbookmark_global_fields', $settings_fields ); //final filter if need

		return $settings_fields;
	}//end delete_bookmark

	/**
	 * Returns post types as array
	 *
	 * @return array
	 */
	public function post_types() {
		return CBXWPBookmarkHelper::post_types();
	}//end enqueue_scripts

	/**
	 * Adds hook for post delete - delete bookmark for those post
	 */
	public function on_bookmarkpost_delete() {
		add_action( 'delete_post', array( $this, 'delete_bookmark' ), 10 );
	}//end get_settings_sections

	/**
	 * Delete bookmark on post delete
	 *
	 * @param type $postid
	 */
	public function delete_bookmark( $object_id ) {
		global $wpdb;

		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$object_id = intval( $object_id );

		$object_types = CBXWPBookmarkHelper::object_types( true ); //get plain post type as array

		$bookmarks = CBXWPBookmarkHelper::getBookmarksByObject( $object_id );

		if ( is_array( $bookmarks ) && sizeof( $bookmarks ) > 0 ) {
			foreach ( $bookmarks as $bookmark ) {
				$bookmark_id = intval( $bookmark['id'] );
				$user_id     = intval( $bookmark['user_id'] );
				$object_type = esc_attr( $bookmark['object_type'] );

				if ( ! in_array( $object_type, $object_types ) ) {
					return;
				}

				do_action( 'cbxbookmark_bookmark_removed_before', $bookmark_id, $user_id, $object_id, $object_type );

				$delete_bookmark = $wpdb->delete( $bookmark_table,
					array(
						'object_id' => $object_id,
						'user_id'   => $user_id,
					),
					array( '%d', '%d' ) );

				if ( $delete_bookmark !== false ) {
					do_action( 'cbxbookmark_bookmark_removed', $bookmark_id, $user_id, $object_id, $object_type );
				}
			}
		}
	}//end post_types

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		$page        = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';
		$admin_slugs = CBXWPBookmarkHelper::admin_page_slugs();

		if ( in_array( $page, $admin_slugs ) ) {
			wp_enqueue_style( 'wp-color-picker' );

			//wp_register_style( 'cbxbookmarkchoosen', plugin_dir_url( __FILE__ ) . '../assets/css/chosen.min.css', array(), $this->version, 'all' );
			wp_register_style( 'select2',
				plugin_dir_url( __FILE__ ) . '../assets/vendors/select2/css/select2.min.css',
				array(),
				$this->version );
			wp_register_style( 'cbxwpbookmark-setting',
				plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-setting.css',
				array(
					'select2',
					'wp-color-picker',
				),
				$this->version,
				'all' );
			wp_register_style( 'cbxwpbookmark-admin',
				plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-admin.css',
				array(
					'select2',
					'wp-color-picker',
					'cbxwpbookmark-setting',
				),
				$this->version,
				'all' );

			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'cbxwpbookmark-setting' );
			wp_enqueue_style( 'cbxwpbookmark-admin' );
		}

		$admin_slugs = CBXWPBookmarkHelper::admin_page_slugs();
		if ( in_array( $page, $admin_slugs ) ) {
			wp_register_style( 'cbxwpbookmark-branding',
				plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-branding.css',
				array(),
				$this->version );
			wp_enqueue_style( 'cbxwpbookmark-branding' );
		}
	}//end enqueue_styles

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		$page = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';

		if ( $page == 'cbxwpbookmark_settings' ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_media();
			wp_enqueue_script( 'wp-color-picker' );

			wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . '../assets/vendors/select2/js/select2.full.min.js', array( 'jquery' ), $this->version, true );
			wp_register_script( 'cbxwpbookmark-setting',
				plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-setting.js',
				array(
					'jquery',
					'select2',
					'wp-color-picker',
				),
				$this->version,
				true );

			$cbxwpbookmark_setting_js_vars = apply_filters( 'cbxwpbookmark_setting_js_vars',
				array(
					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( "cbxbookmarknonce" ),
					'please_select' => esc_html__( 'Please Select', 'cbxwpbookmark' ),
					'upload_title'  => esc_html__( 'Window Title', 'cbxwpbookmark' ),
					//'cbxbookmark_lang' => get_user_locale(),
				) );

			wp_localize_script( 'cbxwpbookmark-setting', 'cbxwpbookmark_setting', $cbxwpbookmark_setting_js_vars );

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'cbxwpbookmark-setting' );
		}

		//Admin js
		$admin_slugs = CBXWPBookmarkHelper::admin_page_slugs();
		if ( in_array( $page, $admin_slugs ) ) {
			wp_register_script( 'cbxwpbookmark-admin',
				plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-admin.js',
				array(
					'jquery',
				),
				$this->version,
				true );
			wp_enqueue_script( 'cbxwpbookmark-admin' );
		}
	}//end get_settings_fields

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function admin_pages() {

		//CBXWPBookmarkHelper::init_session();

		global $submenu;

		//review listing page
		$bookmark_list_page_hook = add_menu_page( esc_html__( 'CBX WP Bookmark Dashboard', 'cbxwpbookmark' ),
			esc_html__( 'CBX Bookmark', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmarkdash',
			array( $this, 'display_admin_bookmark_dash_page' ),
			CBXWPBOOKMARK_ROOT_URL . 'assets/img/menu_icon_24.png' );

		//review listing page
		$bookmark_list_page_hook = add_submenu_page( 'cbxwpbookmarkdash', esc_html__( 'CBX WP Bookmark Listing', 'cbxwpbookmark' ),
			esc_html__( 'User Bookmarks', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmark',
			array( $this, 'display_admin_bookmark_list_page' ) );


		//add screen save option for bookmark listing
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'cbxwpbookmark' && ! isset( $_GET['view'] ) ) {
			add_action( "load-$bookmark_list_page_hook", array( $this, 'cbxwpbookmark_bookmark_list_screen' ) );
		}

		//Add menu for bookmark category listing
		$bookmark_category_page_hook = add_submenu_page( 'cbxwpbookmarkdash', esc_html__( 'CBX WP Bookmark Category Listing', 'cbxwpbookmark' ),
			esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmarkcats',
			array( $this, 'display_admin_bookmark_category_page' )
		);

		//add screen save option for bookmark category listing
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'cbxwpbookmarkcats' && ! isset( $_GET['view'] ) ) {
			add_action( "load-$bookmark_category_page_hook",
				array(
					$this,
					'cbxwpbookmark_bookmark_category_screen',
				) );
		}


		//add settings for this plugin
		$setting_page_hook = add_submenu_page(
			'cbxwpbookmarkdash',
			esc_html__( 'CBX WP Bookmark Setting', 'cbxwpbookmark' ),
			esc_html__( 'Setting', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmark_settings',
			array( $this, 'display_plugin_admin_settings' )
		);


		if ( isset( $submenu['cbxwpbookmarkdash'][0][0] ) ) {
			$submenu['cbxwpbookmarkdash'][0][0] = esc_html__( 'Bookmarks Dashboard', 'cbxwpbookmark' );
		}


	}//end add_plugin_admin_menu

	/**
	 * Admin dashboard view
	 */
	public function display_admin_bookmark_dash_page() {
		echo cbxwpbookmark_get_template_html( 'admin/dashboard.php', array() );
	}//end display_admin_bookmark_dash_page

	/**
	 * Admin review listing view
	 */
	public function display_admin_bookmark_list_page() {
		echo cbxwpbookmark_get_template_html( 'admin/bookmark_list_display.php', array() );
	}//end display_admin_bookmark_listing_page

	/**
	 * Set options for bookmark listing result
	 *
	 * @param $new_status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function cbxwpbookmark_bookmark_list_per_page( $new_status, $option, $value ) {
		if ( 'cbxwpbookmark_list_per_page' == $option ) {
			return $value;
		}

		return $new_status;
	}//end cbxwpbookmark_bookmark_list_per_page

	/**
	 * Add screen option for bookmark listing
	 */
	public function cbxwpbookmark_bookmark_list_screen() {

		$option = 'per_page';
		$args   = array(
			'label'   => esc_html__( 'Number of items per page', 'cbxwpbookmark' ),
			'default' => 50,
			'option'  => 'cbxwpbookmark_list_per_page',
		);
		add_screen_option( $option, $args );

	}//end cbxwpbookmark_bookmark_list_screen

	/**
	 * Admin review listing view
	 */
	public function display_admin_bookmark_category_page() {

		global $wpdb;

		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . $this->plugin_basename );

		$view = isset( $_GET['view'] ) ? $_GET['view'] : '';
		if ( $view == 'edit' ) {
			//include( 'partials/bookmark_category_edit.php' );
			include( cbxwpbookmark_locate_template( 'admin/bookmark_category_edit.php' ) );
		} else {
			//include( 'partials/bookmark_category_list.php' );
			include( cbxwpbookmark_locate_template( 'admin/bookmark_category_list.php' ) );
		}


	}//end display_admin_bookmark_listing_page


	/**
	 * Set options for bookmark category listing result
	 *
	 * @param $new_status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function cbxwpbookmark_bookmark_category_per_page( $new_status, $option, $value ) {
		if ( 'cbxwpbookmark_category_per_page' == $option ) {
			return $value;
		}

		return $new_status;
	}//end cbxwpbookmark_bookmark_category_per_page

	/**
	 * Add screen option for bookmark listing
	 */
	public function cbxwpbookmark_bookmark_category_screen() {

		$option = 'per_page';
		$args   = array(
			'label'   => esc_html__( 'Number of items per page', 'cbxwpbookmark' ),
			'default' => 50,
			'option'  => 'cbxwpbookmark_category_per_page',
		);
		add_screen_option( $option, $args );

	}//end cbxwpbookmark_bookmark_category_screen

	/**
	 * Admin page for settings of this plugin
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_settings() {
		echo cbxwpbookmark_get_template_html( 'admin/setting_display.php', array(
			'ref'          => $this,
			'settings_api' => $this->settings_api
		) );
	}//end display_plugin_admin_settings

	/**
	 * Add/Edit bookmark Category
	 */
	public function add_edit_category() {
		if ( isset( $_POST['cbxwpbookmark_cat_addedit'] ) && intval( $_POST['cbxwpbookmark_cat_addedit'] ) == 1 ) {
			global $wpdb;
			$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';

			$redirect_url        = 'admin.php?page=cbxwpbookmarkcats&view=edit';
			$form_validated      = true;
			$validation['error'] = false;
			$validation['field'] = array();

			$submit_data = isset( $_POST['cbxwpbookmark_form'] ) ? $_POST['cbxwpbookmark_form'] : array();
			$isAjax      = isset( $submit_data['ajax'] ) ? intval( $submit_data['ajax'] ) : 0;

			//verify nonce field
			if ( wp_verify_nonce( $_POST['cbxwpbookmark_cat_nonce'], 'cbxwpbookmark_cat_addedit' ) ) {

				$log_id   = isset( $submit_data['id'] ) ? absint( $submit_data['id'] ) : 0;
				$privacy  = isset( $submit_data['privacy'] ) ? absint( $submit_data['privacy'] ) : 0;
				$cat_name = isset( $submit_data['cat_name'] ) ? sanitize_text_field( $submit_data['cat_name'] ) : '';

				$title_len = mb_strlen( $cat_name );

				$col_data = array(
					'cat_name' => $cat_name,
					'privacy'  => $privacy,
				);


				//check category title length is not less than 5 or more than 200 char
				if ( $title_len < 3 || $title_len > 250 ) {
					$form_validated        = false;
					$validation['error']   = true;
					$validation['field'][] = 'title';
					$validation['msg']     = esc_html__( 'The title field character limit must be between 3 to 250.', 'cbxwpsimpleaccounting' );
				}


				//check form passes all validation rules
				if ( $form_validated ) {
					//edit mode
					if ( $log_id > 0 ) {


						$col_data['modyfied_date'] = current_time( 'mysql' );

						//cat_name, privacy, modyfied_date
						$col_data_format = array( '%s', '%d', '%s' );

						$where = array(
							'id' => $log_id,
						);

						$where_format = array( '%d' );

						//matching update function return is false, then update failed.
						if ( $wpdb->update( $category_table, $col_data, $where, $col_data_format, $where_format ) === false ) {
							//update failed
							$validation['msg'] = esc_html__( 'Sorry! category update failed or database error', 'cbxwpbookmark' );
						} else {
							$category_info = CBXWPBookmarkHelper::singleCategory( $log_id );

							do_action( 'cbxbookmark_category_edit', $log_id, $category_info['user_id'], $cat_name );

							//update successful
							$msg = esc_html__( 'Category updated successfully.', 'cbxwpbookmark' );
							$msg .= ' <a  href="' . admin_url( $redirect_url . '&id=0' ) . '" class="button">';
							$msg .= esc_html__( 'Create new category', 'cbxwpbookmark' );
							$msg .= '</a>';

							$validation['error']            = false;
							$validation['msg']              = $msg;
							$validation['data']['id']       = $log_id;
							$validation['data']['cat_name'] = stripslashes( $cat_name );
							$validation['data']['privacy']  = $privacy;
							$validation['data']['status']   = 'updated';


						}

					} else { //if category is new then go here

						$col_data['user_id']      = $user_id = intval( get_current_user_id() );
						$col_data['created_date'] = current_time( 'mysql' );

						///cat_name, privacy, user_id, created_date
						$col_data_format = array( '%s', '%d', '%d', '%s' );
						//insert new category
						if ( $wpdb->insert( $category_table, $col_data, $col_data_format ) ) {
							//new category inserted successfully

							$log_id = $wpdb->insert_id;

							do_action( 'cbxbookmark_category_added', $log_id, $user_id, $cat_name );

							$msg = esc_html__( 'Category created successfully.', 'cbxwpsimpleaccounting' );
							$msg .= ' <a  href="' . admin_url( $redirect_url . '&id=' . $log_id ) . '" class="button">';
							$msg .= esc_html__( 'Edit', 'cbxwpbookmark' );
							$msg .= '</a>';

							$validation['error']            = false;
							$validation['msg']              = $msg;
							$validation['data']['id']       = $log_id;
							$validation['data']['cat_name'] = stripslashes( $cat_name );
							$validation['data']['privacy']  = $privacy;
							$validation['data']['status']   = 'new';
						} else { //new category insertion failed
							$validation['error'] = true;
							$validation['msg']   = esc_html__( 'Error creating category', 'cbxwpbookmark' );
						}
					}
				}
			} else { //if wp_nonce not verified then entry here
				$validation['error']   = true;
				$validation['field'][] = 'wp_nonce';
				$validation['msg']     = esc_html__( 'Hacking attempt ?', 'cbxwpbookmark' );
			}


			if ( $isAjax ) {
				echo json_encode( $validation );
				wp_die();
			} else {
				set_transient( 'cbxwpbookmark_cat_addedit_error', $validation );

				if ( $log_id > 0 ) {
					$redirect_url .= '&id=' . $log_id;
				}

				wp_safe_redirect( admin_url( $redirect_url ) );
				exit;
			}
		}//if cbxwpbookmark_cat_addedit(category edit submited)  submit

	}//end add_edit_category

	/**
	 * Automatically create pages using ajax
	 */
	public function cbxwpbookmark_autocreate_page() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		if ( ! class_exists( 'CBXWPBookmark_Activator' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-activator.php';
		}

		//create pages
		CBXWPBookmark_Activator::cbxbookmark_create_pages(); //create the shortcode page

		$message        = array();
		$message['msg'] = esc_html__( 'Automatic page creation done. This message doesn\'t confirm success or failed', 'cbxwpbookmark' );

		echo json_encode( $message );
		wp_die();
	}//end cbxwpbookmark_autocreate_page

	/**
	 * Full plugin reset and redirect
	 */
	public function plugin_fullreset() {
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'cbxwpbookmark_settings' && isset( $_REQUEST['cbxwpbookmark_fullreset'] ) && $_REQUEST['cbxwpbookmark_fullreset'] == 1 ) {

			if ( ! class_exists( 'CBXWPBookmark_Activator' ) ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-activator.php';
			}


			global $wpdb;
			$option_prefix = 'cbxwpbookmark_';

			//delete custom tables
			$table_names  = CBXWPBookmarkHelper::getAllDBTablesList();
			$sql          = "DROP TABLE IF EXISTS " . implode( ', ', array_values( $table_names ) );
			$query_result = $wpdb->query( $sql );

			do_action( 'cbxwpbookmark_plugin_table_delete' );
			//delete custom tables done

			//delete options
			$option_values = CBXWPBookmarkHelper::getAllOptionNames();
			foreach ( $option_values as $key => $accounting_option_value ) {
				delete_option( $accounting_option_value['option_name'] );
			}

			do_action( 'cbxwpbookmark_plugin_option_delete' );
			//delete options done

			//3rd party plugin's table creation
			do_action( 'cbxwpbookmark_plugin_reset', $table_names, $option_prefix );


			//create custom tables
			CBXWPBookmark_Activator::activate();                 //db table creates

			//create option settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );
			$this->settings_api->admin_init();

			//create pages
			CBXWPBookmark_Activator::cbxbookmark_create_pages(); //create the shortcode page

			//create customizer settings
			CBXWPBookmarkHelper::customizer_default_adjust( true );


			set_transient( 'cbxwpbookmark_fullreset_message', esc_html__( 'CBX Bookmark plugin data has been reset which means setting fields, database table, meta keys related with this plugin are deleted, setting and database table recreated. ', 'cbxwpbookmark' ) );

			wp_safe_redirect( admin_url( 'admin.php?page=cbxwpbookmark_settings#cbxwpbookmark_tools' ) );
			exit();
		}

	}//end plugin_fullreset

	/**
	 * Display migration messages
	 */
	public function fullreset_message_display() {
		$reset_note = get_transient( 'cbxwpbookmark_fullreset_message' );
		if ( $reset_note ) {
			delete_transient( 'cbxwpbookmark_fullreset_message' );

			if ( $reset_note != '' ):
				?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo $reset_note; ?></p>
                </div>
			<?php
			endif;
		}
	}//end fullreset_message_display


	/**
	 * Post installation hook
	 *
	 * @param $response
	 * @param array $hook_extra
	 * @param array $result
	 */
	public function upgrader_post_install( $response, $hook_extra = array(), $result = array() ) {
		if ( $response && isset( $hook_extra['type'] ) && $hook_extra['type'] == 'plugin' ) {
			if ( isset( $result['destination_name'] ) && $result['destination_name'] == 'cbxwpbookmark' ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				CBXWPBookmarkHelper::create_tables();
				CBXWPBookmarkHelper::customizer_default_adjust( true );
				set_transient( 'cbxwpbookmark_upgraded_notice', 1 );
			}
		}
	}


	/**
	 * If we need to do something in upgrader process is completed
	 *
	 * @param $upgrader_object
	 * @param $options
	 */
	public function plugin_upgrader_process_complete( $upgrader_object, $options ) {
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) && sizeof( $options['plugins'] ) > 0 ) {
				foreach ( $options['plugins'] as $each_plugin ) {
					if ( $each_plugin == CBXWPBOOKMARK_BASE_NAME ) {
						CBXWPBookmarkHelper::create_tables();
						CBXWPBookmarkHelper::customizer_default_adjust( true );
						set_transient( 'cbxwpbookmark_upgraded_notice', 1 );
						break;
					}
				}
			}
		}

	}//end plugin_upgrader_process_complete

	/**
	 * Show a notice to anyone who has just installed the plugin for the first time
	 * This notice shouldn't display to anyone who has just updated this plugin
	 */
	public function plugin_activate_upgrade_notices() {
		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxwpbookmark_activated_notice' ) ) {
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible">';
			echo '<p><img style="float: left; display: inline-block; margin-right: 15px;" src="' . CBXWPBOOKMARK_ROOT_URL . 'assets/img/bookmarks_heading_icon.png?v=2' . '"/>' . sprintf( __( 'Thanks for installing/deactivating <strong>CBX Bookmark</strong> V%s - Codeboxr Team', 'cbxwpbookmark' ), CBXWPBOOKMARK_PLUGIN_VERSION ) . '</p>';
			echo '<p>' . sprintf( __( 'Check <a style="color:#005ae0 !important; font-weight: bold;" href="%s">Plugin Setting</a> | <a href="%s" target="_blank"><span class="dashicons dashicons-external"></span> Documentation</a>', 'cbxwpbookmark' ), admin_url( 'admin.php?page=cbxwpbookmark_settings' ), 'https://codeboxr.com/product/cbx-wordpress-bookmark/' ) . '</p>';
			echo '</div>';
			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxwpbookmark_activated_notice' );

			$this->pro_addon_compatibility_campaign();
		}

		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxwpbookmark_upgraded_notice' ) ) {
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible">';
			echo '<p><img style="float: left; display: inline-block; margin-right: 15px;" src="' . CBXWPBOOKMARK_ROOT_URL . 'assets/img/bookmarks_heading_icon.png' . '"/>' . sprintf( __( 'Thanks for upgrading <strong>CBX Bookmark</strong> V%s , enjoy the new features and bug fixes - Codeboxr Team', 'cbxwpbookmark' ), CBXWPBOOKMARK_PLUGIN_VERSION ) . '</p>';
			echo '<p>' . sprintf( __( 'Check <a style="color:#005ae0 !important; font-weight: bold;" href="%s">Plugin Setting</a> | <a href="%s" target="_blank"><span class="dashicons dashicons-external"></span> Documentation</a>', 'cbxwpbookmark' ), admin_url( 'admin.php?page=cbxwpbookmark_settings' ), 'https://codeboxr.com/product/cbx-wordpress-bookmark/' ) . '</p>';
			echo '</div>';
			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxwpbookmark_upgraded_notice' );

			$this->pro_addon_compatibility_campaign();
		}
	}//end plugin_activate_upgrade_notices

	/**
	 * Check plugin compatibility and pro addon install campaign
	 */
	public function pro_addon_compatibility_campaign() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		//if the pro addon is active or installed
		if ( in_array( 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXWPBOOKMARKADDON_PLUGIN_NAME' ) ) {
			//plugin is activated

			$pro_plugin_version  = CBXWPBOOKMARKADDON_PLUGIN_VERSION;
			$core_plugin_version = CBXWPBOOKMARK_PLUGIN_VERSION;

			if ( version_compare( $pro_plugin_version, '1.1.10', '<' ) ) {
				echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'CBX Bookmark Pro Addon V%s or any previous version is not 100%% compatible with CBX Bookmark Core V1.5.3 or later. Please update CBX Bookmark Pro Addon to version 1.1.10 or latest. - Codeboxr Team', 'cbxmcratingreview' ), $pro_plugin_version ) . '</p></div>';
			}


		} else {
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible"><p>' . sprintf( __( '<a target="_blank" href="%s">CBX Bookmark Pro Addon</a> has extended features, settings, widgets and shortcodes. try it  - Codeboxr Team', 'cbxwpbookmark' ), 'https://codeboxr.com/product/cbx-wordpress-bookmark/' ) . '</p></div>';
		}


		//if the mycred addon is active or installed
		if ( in_array( 'cbxwpbookmarkmycred/cbxwpbookmarkmycred.php.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXWPBOOKMARKMYCRED_PLUGIN_NAME' ) ) {
			//plugin is activated

			$plugin_version = CBXWPBOOKMARKMYCRED_PLUGIN_VERSION;


		} else {
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible"><p>' . sprintf( __( '<a target="_blank" href="%s">CBX Bookmark myCred Addon</a> has myCred integration. try it  - Codeboxr Team', 'cbxwpbookmark' ), 'https://codeboxr.com/product/cbx-bookmark-mycred-addon/' ) . '</p></div>';
		}

	}//end pro_addon_compatibility_campaign

	/**
	 * Register New Gutenberg block Category if need
	 *
	 * @param $categories
	 * @param $post
	 *
	 * @return mixed
	 */
	public function gutenberg_block_categories( $categories, $post ) {
		$found = false;
		foreach ( $categories as $category ) {
			if ( $category['slug'] == 'cbxwpbookmark' ) {
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'cbxwpbookmark',
						'title' => esc_html__( 'CBX Bookmark Blocks', 'cbxwpbookmark' ),
					),
				)
			);
		}

		return $categories;
	}//end gutenberg_block_categories

	/**
	 * Init all gutenberg blocks
	 */
	public function gutenberg_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		$this->init_cbxwpbookmark_btn_block();
		$this->init_cbxwpbookmark_post_block();

		$this->init_cbxwpbookmark_most_block();
		$this->init_cbxwpbookmark_mycat_block();
	}//end gutenberg_blocks

	/**
	 * Register bookmark button block
	 */
	public function init_cbxwpbookmark_btn_block() {
		wp_register_style( 'cbxwpbookmark-block', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css', array(), filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css' ) );

		wp_register_script( 'cbxwpbookmark-btn-block',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-btn-block.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-editor',
				//'jquery',
				//'codeboxrflexiblecountdown-public'
			),
			filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/cbxwpbookmark-btn-block.js' ) );

		$js_vars = apply_filters( 'cbxwpbookmark_btn_block_js_vars',
			array(
				//'cbxbookmark_lang'        => get_user_locale(),
				'block_title'      => esc_html__( 'CBX Bookmark Button', 'cbxwpbookmark' ),
				'block_category'   => 'cbxwpbookmark',
				'block_icon'       => 'universal-access-alt',
				'general_settings' => array(
					'title'      => esc_html__( 'Block Settings', 'cbxwpbookmark' ),
					'show_count' => esc_html__( 'Show Count', 'cbxwpbookmark' ),
				),
			) );

		wp_localize_script( 'cbxwpbookmark-btn-block', 'cbxwpbookmark_btn_block', $js_vars );

		register_block_type( 'codeboxr/cbxwpbookmark-btn-block',
			array(
				'editor_script'   => 'cbxwpbookmark-btn-block',
				'editor_style'    => 'cbxwpbookmark-block',
				'attributes'      => apply_filters( 'cbxwpbookmark_btn_block_attributes',
					array(
						//general
						'show_count' => array(
							'type'    => 'boolean',
							'default' => true,
						),

					) ),
				'render_callback' => array( $this, 'cbxwpbookmark_btn_block_render' ),
			) );
	}//end init_cbxwpbookmark_btn_block

	/**
	 * Getenberg server side render
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function cbxwpbookmark_btn_block_render( $attr ) {
		$arr['show_count'] = isset( $attr['show_count'] ) ? $attr['show_count'] : 'true';
		$arr['show_count'] = ( $arr['show_count'] == 'true' ) ? 1 : 0;

		$attr_html = '';
		foreach ( $arr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		//return do_shortcode('[cbxwpbookmarkbtn '.$attr_html.']');
		return '[cbxwpbookmarkbtn ' . $attr_html . ']';
	}//end init_cbxwpbookmark_post_block

	/**
	 * Register bookmark posts block
	 */
	public function init_cbxwpbookmark_post_block() {
		$order_options = array();

		$order_options[] = array(
			'label' => esc_html__( 'Descending Order', 'cbxwpbookmark' ),
			'value' => 'DESC',
		);

		$order_options[] = array(
			'label' => esc_html__( 'Ascending Order', 'cbxwpbookmark' ),
			'value' => 'ASC',
		);

		$orderby_options   = array();
		$orderby_options[] = array(
			'label' => esc_html__( 'Post Type', 'cbxwpbookmark' ),
			'value' => 'object_type',
		);

		$orderby_options[] = array(
			'label' => esc_html__( 'Post ID', 'cbxwpbookmark' ),
			'value' => 'object_id',
		);

		$orderby_options[] = array(
			'label' => esc_html__( 'Bookmark ID', 'cbxwpbookmark' ),
			'value' => 'id',
		);

		$orderby_options[] = array(
			'label' => esc_html__( 'Post Title', 'cbxwpbookmark' ),
			'value' => 'title',
		);

		$type_options   = array();
		$post_types     = CBXWPBookmarkHelper::post_types_plain();
		$type_options[] = array(
			'label' => esc_html__( 'Select Post Type', 'cbxwpbookmark' ),
			'value' => '',
		);

		foreach ( $post_types as $type_slug => $type_name ) {
			$type_options[] = array(
				'label' => $type_name,
				'value' => $type_slug,
			);
		}


		wp_register_style( 'cbxwpbookmark-block', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css', array(), filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css' ) );
		wp_register_script( 'cbxwpbookmark-post-block',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-post-block.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-editor',
				//'jquery',
				//'codeboxrflexiblecountdown-public'
			),
			filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/cbxwpbookmark-post-block.js' ) );

		$js_vars = apply_filters( 'cbxwpbookmark_post_block_js_vars',
			array(
				//'cbxbookmark_lang'        => get_user_locale(),
				'block_title'      => esc_html__( 'CBX My Bookmarked Posts', 'cbxwpbookmark' ),
				'block_category'   => 'cbxwpbookmark',
				'block_icon'       => 'universal-access-alt',
				'general_settings' => array(
					'heading'         => esc_html__( 'Block Settings', 'cbxwpbookmark' ),
					'title'           => esc_html__( 'Title', 'cbxwpbookmark' ),
					'title_desc'      => esc_html__( 'Leave empty to hide', 'cbxwpbookmark' ),
					'order'           => esc_html__( 'Order', 'cbxwpbookmark' ),
					'order_options'   => $order_options,
					'orderby'         => esc_html__( 'Order By', 'cbxwpbookmark' ),
					'orderby_options' => $orderby_options,
					'type'            => esc_html__( 'Post Type(s)', 'cbxwpbookmark' ),
					'type_options'    => $type_options,
					'limit'           => esc_html__( 'Number of Posts', 'cbxwpbookmark' ),
					'loadmore'        => esc_html__( 'Show Load More', 'cbxwpbookmark' ),
					'catid'           => esc_html__( 'Categories(Comma Separated)', 'cbxwpbookmark' ),
					'catid_note'      => esc_html__( 'This is practically useful if category mode = global category', 'cbxwpbookmark' ),
					'cattitle'        => esc_html__( 'Show Category Title', 'cbxwpbookmark' ),
					'catcount'        => esc_html__( 'Show Category Count', 'cbxwpbookmark' ),
					'allowdelete'     => esc_html__( 'Allow Delete', 'cbxwpbookmark' ),
					'allowdeleteall'  => esc_html__( 'Allow Delete All', 'cbxwpbookmark' ),
				),
			) );

		wp_localize_script( 'cbxwpbookmark-post-block', 'cbxwpbookmark_post_block', $js_vars );

		register_block_type( 'codeboxr/cbxwpbookmark-post-block',
			array(
				'editor_script'   => 'cbxwpbookmark-post-block',
				'editor_style'    => 'cbxwpbookmark-block',
				'attributes'      => apply_filters( 'cbxwpbookmark_post_block_attributes',
					array(
						'title'       => array(
							'type'    => 'string',
							'default' => esc_html__( 'All Bookmarks', 'cbxwpbookmark' ),
						),
						'order'       => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'orderby'     => array(
							'type'    => 'string',
							'default' => 'id',
						),
						'type'        => array(
							'type'    => 'array',
							'default' => array(),
							'items'   => array(
								'type' => 'string',
							),
						),
						'catid'       => array(
							'type'    => 'string',
							'default' => '',
						),
						'limit'       => array(
							'type'    => 'integer',
							'default' => 10,
						),
						'loadmore'    => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'cattitle'    => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'catcount'    => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'allowdelete' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'allowdeleteall' => array(
							'type'    => 'boolean',
							'default' => false,
						),

					) ),
				'render_callback' => array( $this, 'cbxwpbookmark_post_block_render' ),
			) );
	}//end init_cbxwpbookmark_post_block

	/**
	 * Getenberg server side render for my bookmark post block
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function cbxwpbookmark_post_block_render( $attr ) {
		$arr = array();

		$arr['title']   = isset( $attr['title'] ) ? sanitize_text_field( $attr['title'] ) : '';
		$arr['order']   = isset( $attr['order'] ) ? sanitize_text_field( $attr['order'] ) : 'DESC';
		$arr['orderby'] = isset( $attr['orderby'] ) ? sanitize_text_field( $attr['orderby'] ) : 'id';
		$arr['limit']   = isset( $attr['limit'] ) ? intval( $attr['limit'] ) : 10;


		$type        = isset( $attr['type'] ) ? wp_unslash( $attr['type'] ) : array();
		$type        = array_filter( $type );
		$arr['type'] = implode( ',', $type );

		$attr['catid'] = isset( $attr['catid'] ) ? wp_unslash( $attr['catid'] ) : '';


		$arr['loadmore'] = isset( $attr['loadmore'] ) ? $attr['loadmore'] : 'true';
		$arr['loadmore'] = ( $arr['loadmore'] == 'true' ) ? 1 : 0;


		$arr['cattitle'] = isset( $attr['cattitle'] ) ? $attr['cattitle'] : 'true';
		$arr['cattitle'] = ( $arr['cattitle'] == 'true' ) ? 1 : 0;

		$arr['catcount'] = isset( $attr['catcount'] ) ? $attr['catcount'] : 'true';
		$arr['catcount'] = ( $arr['catcount'] == 'true' ) ? 1 : 0;

		$arr['allowdelete'] = isset( $attr['allowdelete'] ) ? $attr['allowdelete'] : 'false';
		$arr['allowdelete'] = ( $arr['allowdelete'] == 'true' ) ? 1 : 0;

		$arr['allowdeleteall'] = isset( $attr['allowdeleteall'] ) ? $attr['allowdeleteall'] : 'false';
		$arr['allowdeleteall'] = ( $arr['allowdeleteall'] == 'true' ) ? 1 : 0;

		$attr_html = '';
		foreach ( $arr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		return do_shortcode( '[cbxwpbookmark ' . $attr_html . ']' );
		//return '[cbxwpbookmark '.$attr_html.']';
	}//end cbxwpbookmark_post_block_render

	/**
	 * Register most bookmarked posts block
	 */
	public function init_cbxwpbookmark_most_block() {
		$order_options = array();

		$order_options[] = array(
			'label' => esc_html__( 'Descending Order', 'cbxwpbookmark' ),
			'value' => 'DESC',
		);

		$order_options[] = array(
			'label' => esc_html__( 'Ascending Order', 'cbxwpbookmark' ),
			'value' => 'ASC',
		);

		$orderby_options   = array();
		$orderby_options[] = array(
			'label' => esc_html__( 'Bookmark Count', 'cbxwpbookmark' ),
			'value' => 'object_count',
		);
		$orderby_options[] = array(
			'label' => esc_html__( 'Post Type', 'cbxwpbookmark' ),
			'value' => 'object_type',
		);
		$orderby_options[] = array(
			'label' => esc_html__( 'Post ID', 'cbxwpbookmark' ),
			'value' => 'object_id',
		);
		$orderby_options[] = array(
			'label' => esc_html__( 'Bookmark ID', 'cbxwpbookmark' ),
			'value' => 'id',
		);
		$orderby_options[] = array(
			'label' => esc_html__( 'Post Title', 'cbxwpbookmark' ),
			'value' => 'title',
		);

		$type_options   = array();
		$post_types     = CBXWPBookmarkHelper::post_types_plain();
		$type_options[] = array(
			'label' => esc_html__( 'Select Post Type', 'cbxwpbookmark' ),
			'value' => '',
		);

		foreach ( $post_types as $type_slug => $type_name ) {
			$type_options[] = array(
				'label' => $type_name,
				'value' => $type_slug,
			);
		}

		$daytime_options   = array();
		$daytime_options[] = array(
			'label' => esc_html__( '-- All Time --', 'cbxwpbookmark' ),
			'value' => 0
		);

		$daytime_options[] = array(
			'label' => esc_html__( '1 Day', 'cbxwpbookmark' ),
			'value' => 1
		);

		$daytime_options[] = array(
			'label' => esc_html__( '7 Days', 'cbxwpbookmark' ),
			'value' => 7
		);

		$daytime_options[] = array(
			'label' => esc_html__( '30 Days', 'cbxwpbookmark' ),
			'value' => 30
		);

		$daytime_options[] = array(
			'label' => esc_html__( '6 Months', 'cbxwpbookmark' ),
			'value' => 180
		);

		$daytime_options[] = array(
			'label' => esc_html__( '1 Year', 'cbxwpbookmark' ),
			'value' => 365
		);


		wp_register_style( 'cbxwpbookmark-block', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css', array(), filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css' ) );
		wp_register_script( 'cbxwpbookmark-most-block',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-most-block.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-editor',
				//'jquery',
				//'codeboxrflexiblecountdown-public'
			),
			filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/cbxwpbookmark-most-block.js' ) );

		$js_vars = apply_filters( 'cbxwpbookmark_most_block_js_vars',
			array(
				//'cbxbookmark_lang'        => get_user_locale(),
				'block_title'      => esc_html__( 'CBX Most Bookmarked Posts', 'cbxwpbookmark' ),
				'block_category'   => 'cbxwpbookmark',
				'block_icon'       => 'universal-access-alt',
				'general_settings' => array(
					'heading'         => esc_html__( 'Block Settings', 'cbxwpbookmark' ),
					'title'           => esc_html__( 'Title', 'cbxwpbookmark' ),
					'title_desc'      => esc_html__( 'Leave empty to hide', 'cbxwpbookmark' ),
					'order'           => esc_html__( 'Order', 'cbxwpbookmark' ),
					'order_options'   => $order_options,
					'orderby'         => esc_html__( 'Order By', 'cbxwpbookmark' ),
					'orderby_options' => $orderby_options,
					'type'            => esc_html__( 'Post Type(s)', 'cbxwpbookmark' ),
					'type_options'    => $type_options,
					'limit'           => esc_html__( 'Number of Posts', 'cbxwpbookmark' ),
					'daytime'         => esc_html__( 'Duration', 'cbxwpbookmark' ),
					'daytime_options' => $daytime_options,
					'show_count'      => esc_html__( 'Show Count', 'cbxwpbookmark' ),
					'show_thumb'      => esc_html__( 'Show Thumbnail', 'cbxwpbookmark' ),
				),
			) );

		wp_localize_script( 'cbxwpbookmark-most-block', 'cbxwpbookmark_most_block', $js_vars );

		register_block_type( 'codeboxr/cbxwpbookmark-most-block',
			array(
				'editor_script'   => 'cbxwpbookmark-most-block',
				'editor_style'    => 'cbxwpbookmark-block',
				'attributes'      => apply_filters( 'cbxwpbookmark_most_block_attributes',
					array(
						'title'      => array(
							'type'    => 'string',
							'default' => esc_html__( 'Most Bookmarked Posts', 'cbxwpbookmark' ),
						),
						'order'      => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'orderby'    => array(
							'type'    => 'string',
							'default' => 'object_count',
						),
						'type'       => array(
							'type'    => 'array',
							'default' => array(),
							'items'   => array(
								'type' => 'string',
							),
						),
						'limit'      => array(
							'type'    => 'integer',
							'default' => 10,
						),
						'daytime'    => array(
							'type'    => 'integer',
							'default' => 0,
						),
						'show_count' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'show_thumb' => array(
							'type'    => 'boolean',
							'default' => true,
						)
					) ),
				'render_callback' => array( $this, 'cbxwpbookmark_most_block_render' ),
			) );
	}//end init_cbxwpbookmark_most_block

	/**
	 * Getenberg server side render for most bookmarked post block
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function cbxwpbookmark_most_block_render( $attr ) {
		$arr = array();

		$arr['title']   = isset( $attr['title'] ) ? sanitize_text_field( $attr['title'] ) : '';
		$arr['order']   = isset( $attr['order'] ) ? sanitize_text_field( $attr['order'] ) : 'DESC';
		$arr['orderby'] = isset( $attr['orderby'] ) ? sanitize_text_field( $attr['orderby'] ) : 'object_count';
		$arr['limit']   = isset( $attr['limit'] ) ? intval( $attr['limit'] ) : 10;


		$type        = isset( $attr['type'] ) ? wp_unslash( $attr['type'] ) : array();
		$type        = array_filter( $type );
		$arr['type'] = implode( ',', $type );

		$attr['daytime'] = isset( $attr['daytime'] ) ? intval( $attr['daytime'] ) : 0;


		$arr['show_count'] = isset( $attr['show_count'] ) ? $attr['show_count'] : 'true';
		$arr['show_count'] = ( $arr['show_count'] == 'true' ) ? 1 : 0;

		$arr['show_thumb'] = isset( $attr['show_thumb'] ) ? $attr['show_thumb'] : 'false';
		$arr['show_thumb'] = ( $arr['show_thumb'] == 'true' ) ? 1 : 0;

		$attr_html = '';
		foreach ( $arr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		return do_shortcode( '[cbxwpbookmark-most ' . $attr_html . ']' );
		//return '[cbxwpbookmark-most '.$attr_html.']';
	}//end cbxwpbookmark_most_block_render

	/**
	 * Register my bookmark category block
	 */
	public function init_cbxwpbookmark_mycat_block() {
		$order_options = array();

		$order_options[] = array(
			'label' => esc_html__( 'Descending Order', 'cbxwpbookmark' ),
			'value' => 'DESC',
		);

		$order_options[] = array(
			'label' => esc_html__( 'Ascending Order', 'cbxwpbookmark' ),
			'value' => 'ASC',
		);

		$orderby_options   = array();
		$orderby_options[] = array(
			'label' => esc_html__( 'Category ID', 'cbxwpbookmark' ),
			'value' => 'id',
		);
		$orderby_options[] = array(
			'label' => esc_html__( 'Category Name', 'cbxwpbookmark' ),
			'value' => 'cat_name',
		);
		$orderby_options[] = array(
			'label' => esc_html__( 'Privacy', 'cbxwpbookmark' ),
			'value' => 'privacy',
		);

		$display_options   = array();
		$display_options[] = array(
			'label' => esc_html__( 'List', 'cbxwpbookmark' ),
			'value' => 0,
		);
		$display_options[] = array(
			'label' => esc_html__( 'Dropdown', 'cbxwpbookmark' ),
			'value' => 1,
		);

		$privacy_options   = array();
		$privacy_options[] = array(
			'label' => esc_html__( 'All', 'cbxwpbookmark' ),
			'value' => 2,
		);
		$privacy_options[] = array(
			'label' => esc_html__( 'Public only', 'cbxwpbookmark' ),
			'value' => 1,
		);
		$privacy_options[] = array(
			'label' => esc_html__( 'Private only', 'cbxwpbookmark' ),
			'value' => 0,
		);


		wp_register_style( 'cbxwpbookmark-block', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css', array(), filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/cbxwpbookmark-block.css' ) );
		wp_register_script( 'cbxwpbookmark-mycat-block',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-mycat-block.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-editor',
				//'jquery',
				//'codeboxrflexiblecountdown-public'
			),
			filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/cbxwpbookmark-mycat-block.js' ) );

		$js_vars = apply_filters( 'cbxwpbookmark_mycat_block_js_vars',
			array(
				//'cbxbookmark_lang'        => get_user_locale(),
				'block_title'      => esc_html__( 'CBX Bookmark Categories', 'cbxwpbookmark' ),
				'block_category'   => 'cbxwpbookmark',
				'block_icon'       => 'universal-access-alt',
				'general_settings' => array(
					'heading'         => esc_html__( 'Block Settings', 'cbxwpbookmark' ),
					'title'           => esc_html__( 'Title', 'cbxwpbookmark' ),
					'title_desc'      => esc_html__( 'Leave empty to hide', 'cbxwpbookmark' ),
					'order'           => esc_html__( 'Order', 'cbxwpbookmark' ),
					'order_options'   => $order_options,
					'orderby'         => esc_html__( 'Order By', 'cbxwpbookmark' ),
					'orderby_options' => $orderby_options,
					'display'         => esc_html__( 'Display Format', 'cbxwpbookmark' ),
					'display_options' => $display_options,
					'privacy'         => esc_html__( 'Privacy', 'cbxwpbookmark' ),
					'privacy_options' => $privacy_options,
					'show_count'      => esc_html__( 'Show Count', 'cbxwpbookmark' ),
					'allowedit'       => esc_html__( 'Allow Edit', 'cbxwpbookmark' ),
					'show_bookmarks'  => esc_html__( 'Show Bookmarks', 'cbxwpbookmark' ),
					//show bookmark as sublist on click on category
				),
			) );

		wp_localize_script( 'cbxwpbookmark-mycat-block', 'cbxwpbookmark_mycat_block', $js_vars );

		register_block_type( 'codeboxr/cbxwpbookmark-mycat-block',
			array(
				'editor_script'   => 'cbxwpbookmark-mycat-block',
				'editor_style'    => 'cbxwpbookmark-block',
				'attributes'      => apply_filters( 'cbxwpbookmark_mycat_block_attributes',
					array(
						'title'          => array(
							'type'    => 'string',
							'default' => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
						),
						'order'          => array(
							'type'    => 'string',
							'default' => 'ASC',
						),
						'orderby'        => array(
							'type'    => 'string',
							'default' => 'cat_name',
						),
						'display'        => array(
							'type'    => 'integer',
							'default' => 0,
						),
						'privacy'        => array(
							'type'    => 'integer',
							'default' => 2,
						),
						'show_count'     => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'allowedit'      => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'show_bookmarks' => array(
							'type'    => 'boolean',
							'default' => false,
						),
					) ),
				'render_callback' => array( $this, 'cbxwpbookmark_mycat_block_render' ),
			) );
	}//end init_cbxwpbookmark_mycat_block

	/**
	 * Getenberg server side render for my bookmark category block
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function cbxwpbookmark_mycat_block_render( $attr ) {
		$arr = array();

		$arr['title']   = isset( $attr['title'] ) ? sanitize_text_field( $attr['title'] ) : '';
		$arr['order']   = isset( $attr['order'] ) ? sanitize_text_field( $attr['order'] ) : 'DESC';
		$arr['orderby'] = isset( $attr['orderby'] ) ? sanitize_text_field( $attr['orderby'] ) : 'cat_name';
		$arr['display'] = isset( $attr['display'] ) ? intval( $attr['display'] ) : 0;
		$arr['privacy'] = isset( $attr['privacy'] ) ? intval( $attr['privacy'] ) : 2;

		$arr['show_count'] = isset( $attr['show_count'] ) ? $attr['show_count'] : 'false';
		$arr['show_count'] = ( $arr['show_count'] == 'true' ) ? 1 : 0;

		$arr['allowedit'] = isset( $attr['allowedit'] ) ? $attr['allowedit'] : 'false';
		$arr['allowedit'] = ( $arr['allowedit'] == 'true' ) ? 1 : 0;

		$arr['show_bookmarks'] = isset( $attr['show_bookmarks'] ) ? $attr['show_bookmarks'] : 'false';
		$arr['show_bookmarks'] = ( $arr['show_bookmarks'] == 'true' ) ? 1 : 0;

		$attr_html = '';
		foreach ( $arr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		return do_shortcode( '[cbxwpbookmark-mycat ' . $attr_html . ']' );
		//return '[cbxwpbookmark-mycat '.$attr_html.']';


	}//end cbxwpbookmark_mycat_block_render

	/**
	 * Enqueue style for block editor
	 */
	public function enqueue_block_editor_assets() {
		do_action( 'cbxwpbookmark_css_start' );

		wp_register_style( 'cbxwpbookmarkpublic-css', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-public.css', array(), '2.0', 'all' );
		wp_enqueue_style( 'cbxwpbookmarkpublic-css' );

		do_action( 'cbxwpbookmark_css_end' );
	}//end enqueue_block_editor_assets

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return  array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a style="color:#005ae0 !important; font-weight: bold;" href="' . admin_url( 'admin.php?page=cbxwpbookmark_settings' ) . '" aria-label="' . esc_attr__( 'View settings', 'cbxwpbookmark' ) . '">' . esc_html__( 'Settings', 'cbxwpbookmark' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}//end plugin_action_links

	/**
	 * Filters the array of row meta for each/specific plugin in the Plugins list table.
	 * Appends additional links below each/specific plugin on the plugins page.
	 *
	 * @access  public
	 *
	 * @param array $links_array An array of the plugin's metadata
	 * @param string $plugin_file_name Path to the plugin file
	 * @param array $plugin_data An array of plugin data
	 * @param string $status Status of the plugin
	 *
	 * @return  array       $links_array
	 */
	public function plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {
		if ( strpos( $plugin_file_name, CBXWPBOOKMARK_BASE_NAME ) !== false ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://wordpress.org/support/plugin/cbxwpbookmark/" aria-label="' . esc_attr__( 'Free Support', 'cbxwpbookmark' ) . '">' . esc_html__( 'Free Support', 'cbxwpbookmark' ) . '</a>';

			$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://wordpress.org/plugins/cbxwpbookmark/#reviews" aria-label="' . esc_attr__( 'Reviews', 'cbxwpbookmark' ) . '">' . esc_html__( 'Reviews', 'cbxwpbookmark' ) . '</a>';

			$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://codeboxr.com/documentation-for-cbx-bookmark-for-wordpress/" aria-label="' . esc_attr__( 'Documentation', 'cbxwpbookmark' ) . '">' . esc_html__( 'Documentation', 'cbxwpbookmark' ) . '</a>';


			if ( in_array( 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXWPBOOKMARKADDON_PLUGIN_NAME' ) ) {

			} else {
				$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://codeboxr.com/product/cbx-wordpress-bookmark/" aria-label="' . esc_attr__( 'Try Pro Addon', 'cbxwpbookmark' ) . '">' . esc_html__( 'Try Pro Addon', 'cbxwpbookmark' ) . '</a>';
			}


		}

		return $links_array;
	}//end plugin_row_meta

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function pre_set_site_transient_update_plugins_pro_addon( $transient ) {
		// Extra check for 3rd plugins
		if ( isset( $transient->response['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_info = array();
		$all_plugins = get_plugins();
		if ( ! isset( $all_plugins['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'] ) ) {
			return $transient;
		} else {
			$plugin_info = $all_plugins['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'];
		}

		$remote_version = '1.3.3'; //not released yet

		if ( version_compare( $plugin_info['Version'], $remote_version, '<' ) ) {
			$obj                                                              = new stdClass();
			$obj->slug                                                        = 'cbxwpbookmarkaddon';
			$obj->new_version                                                 = $remote_version;
			$obj->plugin                                                      = 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php';
			$obj->url                                                         = '';
			$obj->package                                                     = false;
			$obj->name                                                        = 'CBX Bookmark & Favorite Pro Addon';
			$transient->response['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'] = $obj;
		}

		return $transient;
	}//end pre_set_site_transient_update_plugins_pro_addons

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function pre_set_site_transient_update_plugins_mycred_addon( $transient ) {
		// Extra check for 3rd plugins
		if ( isset( $transient->response['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_info = array();
		$all_plugins = get_plugins();
		if ( ! isset( $all_plugins['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'] ) ) {
			return $transient;
		} else {
			$plugin_info = $all_plugins['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'];
		}


		$remote_version = '1.0.3';

		if ( version_compare( $plugin_info['Version'], $remote_version, '<' ) ) {
			$obj                                                                = new stdClass();
			$obj->slug                                                          = 'cbxwpbookmarkmycred';
			$obj->new_version                                                   = $remote_version;
			$obj->plugin                                                        = 'cbxwpbookmarkmycred/cbxwpbookmarkmycred.php';
			$obj->url                                                           = '';
			$obj->package                                                       = false;
			$obj->name                                                          = 'CBX Bookmark & Favorite myCred Addon';
			$transient->response['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'] = $obj;
		}

		return $transient;
	}//end pre_set_site_transient_update_plugins_pro_addons

	/**
	 * Pro Addon update message
	 */
	public function plugin_update_message_pro_addons() {
		echo ' ' . sprintf( __( 'Check how to <a style="color:#005ae0 !important; font-weight: bold;" href="%s"><strong>Update manually</strong></a> , download latest version from <a style="color:#005ae0 !important; font-weight: bold;" href="%s"><strong>My Account</strong></a> section of Codeboxr.com', 'cbxwpbookmark' ), 'https://codeboxr.com/manual-update-pro-addon/', 'https://codeboxr.com/my-account/' );
	}//end plugin_update_message_pro_addons

	/**
	 * User's bookmarks listing screen option columns
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function log_listing_screen_cols( $columns ) {
		$columns = array(
			'id'           => esc_html__( 'ID', 'cbxwpbookmark' ),
			'object_id'    => esc_html__( 'Post', 'cbxwpbookmark' ),
			'object_type'  => esc_html__( 'Post Type', 'cbxwpbookmark' ),
			'user_id'      => esc_html__( 'User', 'cbxwpbookmark' ),
			'cat_id'       => esc_html__( 'Category', 'cbxwpbookmark' ),
			'created_date' => esc_html__( 'Created', 'cbxwpbookmark' ),
		);

		return apply_filters( 'cbxwpbookmark_bookmarks_listing_screen_option_columns', $columns );
	}//end log_listing_screen_cols

	/**
	 * User's bookmarks listing screen option columns
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function category_listing_screen_cols( $columns ) {
		$columns = array(
			'id'            => esc_html__( 'ID', 'cbxwpbookmark' ),
			'cat_name'      => esc_html__( 'Title', 'cbxwpbookmark' ),
			'user_id'       => esc_html__( 'User', 'cbxwpbookmark' ),
			'privacy'       => esc_html__( 'Privacy', 'cbxwpbookmark' ),
			'created_date'  => esc_html__( 'Created', 'cbxwpbookmark' ),
			'modyfied_date' => esc_html__( 'Modified', 'cbxwpbookmark' )
		);

		return apply_filters( 'cbxwpbookmark_category_listing_screen_option_columns', $columns );
	}//end category_listing_screen_cols
}//end class CBXWPBookmark_Admin