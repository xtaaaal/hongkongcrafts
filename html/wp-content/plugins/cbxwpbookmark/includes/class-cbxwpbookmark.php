<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CBXWPBookmark_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = CBXWPBOOKMARK_PLUGIN_NAME;
		$this->version     = CBXWPBOOKMARK_PLUGIN_VERSION;

		$this->load_dependencies();
		$this->set_locale(); //language
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$plugin_customizer = new CBXWPBookmark_Customizer( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - CBXWPBookmark_Loader. Orchestrates the hooks of the plugin.
	 * - CBXWPBookmark_i18n. Defines internationalization functionality.
	 * - CBXWPBookmark_Admin. Defines all hooks for the admin area.
	 * - CBXWPBookmark_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cbxwpbookmark-tpl-loader.php';

		// Loading Settings Class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-setting.php';


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-helper.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-list.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-category.php';

		/*//add widget class ( CBX BOOKMARK CATEGORY )
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/classic_widgets/cbxwpbookmark-category-widget/cbxwpbookmark-category-widget.php';

		// CBX WP BOOKMARK Widget
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/classic_widgets/cbxwpbookmark-widget/cbxwpbookmark-widget.php';

		// CBX WP Most BOOKMARKED Widget
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/classic_widgets/cbxwpbookmarkmost-widget/cbxwpbookmarkmost-widget.php';*/

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cbxwpbookmark-admin.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cbxwpbookmark-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-customizer.php';


		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cbxwpbookmark-functions.php';


		$this->loader = new CBXWPBookmark_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the CBXWPBookmark_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CBXWPBookmark_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $wp_version;

		$plugin_admin = new CBXWPBookmark_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'upgrader_post_install', $plugin_admin, 'upgrader_post_install', 0, 3 );

		//full reset
		$this->loader->add_action( 'admin_init', $plugin_admin, 'plugin_fullreset' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'fullreset_message_display' );

		//add/edit category submission
		$this->loader->add_action( 'admin_init', $plugin_admin, 'add_edit_category' );

		//admin menus
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_pages' );

		//screen options for admin item listing
		$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'cbxwpbookmark_bookmark_list_per_page', 10, 3 );
		$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'cbxwpbookmark_bookmark_category_per_page', 10, 3 );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//adding the setting action
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setting_init' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'on_bookmarkpost_delete' );


		//plugin notices, active, upgrade, deactivation
		$this->loader->add_filter( 'plugin_action_links_' . CBXWPBOOKMARK_BASE_NAME, $plugin_admin, 'plugin_action_links' );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 4 );
		$this->loader->add_action( 'upgrader_process_complete', $plugin_admin, 'plugin_upgrader_process_complete', 10, 2 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'plugin_activate_upgrade_notices' );

		//gutenberg blocks
		if ( version_compare($wp_version,'5.8') >= 0) {
			$this->loader->add_filter( 'block_categories_all', $plugin_admin, 'gutenberg_block_categories', 10, 2 );
		}
		else{
			$this->loader->add_filter( 'block_categories', $plugin_admin, 'gutenberg_block_categories', 10, 2 );
		}

		$this->loader->add_action( 'init', $plugin_admin, 'gutenberg_blocks' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $plugin_admin, 'enqueue_block_editor_assets' );//Hook: Editor assets.


		//not implemented yet
		//$this->loader->add_action( 'enqueue_block_assets', $plugin_admin, 'block_assets' );// Hook: Frontend assets.

		//page auto created
		$this->loader->add_action( 'wp_ajax_cbxwpbookmark_autocreate_page', $plugin_admin, 'cbxwpbookmark_autocreate_page' );

		//update manager
		$this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_admin, 'pre_set_site_transient_update_plugins_pro_addon' );
		$this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_admin, 'pre_set_site_transient_update_plugins_mycred_addon' );
		$this->loader->add_action( 'in_plugin_update_message-' . 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php', $plugin_admin, 'plugin_update_message_pro_addons' );
		$this->loader->add_action( 'in_plugin_update_message-' . 'cbxwpbookmarkmycred/cbxwpbookmarkmycred.php', $plugin_admin, 'plugin_update_message_pro_addons' );

		//for bookmark log listing screens
		$this->loader->add_filter( 'manage_cbx-bookmark_page_cbxwpbookmark_columns', $plugin_admin, 'log_listing_screen_cols' );
		$this->loader->add_filter( 'manage_cbx-bookmark_page_cbxwpbookmarkcats_columns', $plugin_admin, 'category_listing_screen_cols' );


	}//end define_admin_hooks


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CBXWPbookmark_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//$this->loader->add_filter( 'the_content', $plugin_public, "bookmark_auto_integration" );
		$this->loader->add_filter( 'the_content', $plugin_public, 'the_content_auto_integration' );
		$this->loader->add_filter( 'the_excerpt', $plugin_public, 'the_excerpt_auto_integration' );

		$this->loader->add_filter( 'the_content', $plugin_public, 'the_content_customizer_method' );

		$this->loader->add_filter( 'body_class', $plugin_public, 'add_theme_class' );


		$this->loader->add_action( 'wp_ajax_cbx_add_bookmark_category', $plugin_public, 'add_category' ); //from popup
		$this->loader->add_action( 'wp_ajax_cbx_add_bookmark_category_std', $plugin_public, 'add_category_std' ); //from category listing
		$this->loader->add_action( 'wp_ajax_cbx_edit_bookmark_category', $plugin_public, 'edit_category' );


		// Delete Category from Front Admin
		$this->loader->add_action( 'wp_ajax_cbx_delete_bookmark_category', $plugin_public, 'delete_bookmark_category' );
		//$this->loader->add_action('wp_ajax_nopriv_cbx_delete_bookmark_category', $plugin_public, 'delete_bookmark_category');

		// Update Category from Front User Admin
		$this->loader->add_action( 'wp_ajax_cbx_update_bookmark_category', $plugin_public, 'update_bookmark_category' );


		// Delete Category from Front Admin (delete_bookmark_post)
		$this->loader->add_action( 'wp_ajax_cbx_delete_bookmark_post', $plugin_public, 'delete_bookmark_post' );


		//find all boomkark category by loggedin user ajax hook
		$this->loader->add_action( 'wp_ajax_cbx_find_category', $plugin_public, 'find_category' );


		//add bookmark for logged-in user ajax hook
		$this->loader->add_action( 'wp_ajax_cbx_add_bookmark', $plugin_public, 'add_bookmark' );


		//loadmore bookmark ajax
		$this->loader->add_action( 'wp_ajax_cbx_bookmark_loadmore', $plugin_public, 'bookmark_loadmore' );

		//shortcode
		$this->loader->add_action( 'init', $plugin_public, 'init_shortcodes' );

		//classic widget
		$this->loader->add_action( 'widgets_init', $plugin_public, 'init_widgets' );

		//elementor Widget
		$this->loader->add_action( 'elementor/widgets/widgets_registered', $plugin_public, 'init_elementor_widgets' );
		$this->loader->add_action( 'elementor/elements/categories_registered', $plugin_public, 'add_elementor_widget_categories' );
		$this->loader->add_action( 'elementor/editor/before_enqueue_scripts', $plugin_public, 'elementor_icon_loader', 99999 );


		//visual composer widget
		//$this->loader->add_action( 'vc_before_init', $plugin_public, 'vc_before_init_actions', 12 );//priority 12 works for both old and new version of vc
		$this->loader->add_action( 'vc_before_init', $plugin_public, 'vc_before_init_actions' );//priority 12 works for both old and new version of vc

		//load bookmarks on click on category
		$this->loader->add_action( 'wp_ajax_cbx_load_bookmarks_sublist', $plugin_public, 'load_bookmarks_sublist' );
		$this->loader->add_action( 'wp_ajax_nopriv_cbx_load_bookmarks_sublist', $plugin_public, 'load_bookmarks_sublist' );



		//$this->loader->add_action('admin_init', $plugin_public,  'admin_init_ajax_lang');

		//delete all bookmarks of any user by user from frontend
		//loadmore bookmark ajax
		$this->loader->add_action( 'wp_ajax_cbxwpbkmark_delete_all_bookmarks_by_user', $plugin_public, 'delete_all_bookmarks_by_user' );

		//bbpress
		//$this->loader->add_filter('bbp_get_single_forum_description', $plugin_public, 'bbp_get_single_forum_description', 10, 3);
		$this->loader->add_filter('bbp_template_before_single_forum', $plugin_public, 'bbp_template_before_single_forum');
		$this->loader->add_filter('bbp_template_after_single_forum', $plugin_public, 'bbp_template_after_single_forum');
		$this->loader->add_action('bbp_template_before_single_topic', $plugin_public, 'bbp_template_before_single_topic' );
		$this->loader->add_action('bbp_template_after_single_topic', $plugin_public, 'bbp_template_after_single_topic' );


	}//end define_public_hooks

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    CBXWPBookmark_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}
}//end class CBXWPBookmark
