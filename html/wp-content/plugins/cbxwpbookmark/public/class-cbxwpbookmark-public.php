<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CBXWPbookmark
 * @subpackage CBXWPbookmark/public
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Public {

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

	private $settings_api;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->version = $version;
		if ( defined( 'WP_DEBUG' ) ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}

		$this->settings_api = new CBXWPBookmark_Settings_API();
	}//end constructor

	public function init_shortcodes() {
		//bookmark button using shortcode
		add_shortcode( 'cbxwpbookmarkbtn', array( $this, 'bookmark_button_shortcode' ) );         //bookmark button(old)


		//show bookmark list using shortcode
		add_shortcode( 'cbxwpbookmark', array( $this, 'bookmarked_posts_shortcode' ) );           //my bookmarks(old)


		//show bookmark categories using shortcode
		add_shortcode( 'cbxwpbookmark-mycat', array( $this, 'bookmark_mycat_shortcode' ) );       //bookmark category(old but can be use as new)

		//show most bookmarked posts using shortcode
		add_shortcode( 'cbxwpbookmark-most', array( $this, 'most_bookmarked_posts_shortcode' ) ); //bookmarked post
	}//end init_shortcodes

	/**
	 * Register Widget
	 */
	public function init_widgets() {
		if ( ! class_exists( 'CBXWPBookmark_Category' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/classic_widgets/cbxwpbookmark-category-widget/cbxwpbookmark-category-widget.php';
		}

		if ( ! class_exists( 'CBXWPBookmark_Widget' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/classic_widgets/cbxwpbookmark-widget/cbxwpbookmark-widget.php';
		}

		if ( ! class_exists( 'CBXWPBookmarkedMost_Widget' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/classic_widgets/cbxwpbookmarkmost-widget/cbxwpbookmarkmost-widget.php';
		}

		register_widget( "CBXWPBookmark_Category" );     //my bookmark category //if user category mode enabled
		register_widget( "CBXWPBookmark_Widget" );       //my bookmarks
		register_widget( "CBXWPBookmarkedMost_Widget" ); //most bookmarked items
	}//end init_widgets

	/**
	 * Get all categories
	 *
	 * Get all categories global or by user
	 *
	 * @global type $wpdb
	 */
	public function find_category() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		global $wpdb;

		$setting       = $this->settings_api;
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );


		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';
		$user_id        = get_current_user_id(); //get the current logged in user id
		$object_id      = intval( $_POST['object_id'] );
		$object_type    = isset( $_POST['object_type'] ) ? esc_attr( $_POST['object_type'] ) : 'post'; //post, page, user, product, any thing custom

		if ( $bookmark_mode == 'user_cat' ) {

			$cats_by_user = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $category_table WHERE user_id = %d", array( $user_id ) ), ARRAY_A );
		} else {
			$cats_by_user = $wpdb->get_results( "SELECT * FROM $category_table WHERE 1", ARRAY_A );
		}

		$post_in_cats_t = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT cat_id FROM $bookmark_table WHERE object_type = %s AND user_id = %d AND object_id = %d", array(
			$object_type,
			$user_id,
			$object_id
		) ), ARRAY_A
		);

		//
		$post_in_cats = array();
		foreach ( $post_in_cats_t as $cat ) {
			$post_in_cats[] = $cat['cat_id'];
		}

		foreach ( $cats_by_user as &$row ) {
			if ( in_array( $row['id'], $post_in_cats ) ) {
				$row['incat'] = 1;
			} else {
				$row['incat'] = 0;
			}
		}


		$bookmark_total   = CBXWPBookmarkHelper::getTotalBookmark( $object_id );
		$bookmark_by_user = CBXWPBookmarkHelper::isBookmarkedByUser( $object_id );

		$message = array();
		//code 1 = category found
		//code 0 = category not found

		$cats_by_user = apply_filters( 'cbxwpbookmark_user_cats_found', $cats_by_user, $user_id, $object_id, $object_type );

		if ( $cats_by_user != null ) {

			$message['code'] = 1;

			$message['msg'] = esc_html__( 'Categories loaded', 'cbxwpbookmark' );
			if ( $cats_by_user !== false ) {
				$message['cats'] = json_encode( $cats_by_user );
			}
		} else {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category not found, create one.', 'cbxwpbookmark' );
		}

		$message['bookmark_count']  = $bookmark_total;
		$message['bookmark_byuser'] = ( $bookmark_by_user ) ? 1 : 0;

		echo json_encode( $message );

		wp_die();
	}//end find_category

	/**
	 * Auto integration for 'the_content'
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function the_content_auto_integration( $content ) {
		if ( in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
			return $content;
		}

		return $this->bookmark_auto_integration( $content );
	}//end  the_content_auto_integration

	/**
	 * Customizer method for my bookmark page
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function the_content_customizer_method( $content ) {
		if ( ! is_singular( 'page' ) ) {
			return $content;
		}

		$settings_api = $this->settings_api;

		$mybookmark_pageid = absint( $settings_api->get_option( 'mybookmark_pageid', 'cbxwpbookmark_basics', 0 ) );
		$mybookmark_way    = $settings_api->get_option( 'mybookmark_way', 'cbxwpbookmark_basics', 'shortcode' );


		global $post;
		$post_id = intval( $post->ID );

		if ( $post_id == $mybookmark_pageid && $mybookmark_way == 'customizer' ) {
			$content = CBXWPBookmarkHelper::strip_shortcode( 'cbxwpbookmark-mycat', $content );
			$content = CBXWPBookmarkHelper::strip_shortcode( 'cbxwpbookmark', $content );

			//$content = CBXWPBookmarkHelper::strip_shortcode( 'cbxwpbookmarkgrid', $content );

			$content = apply_filters( 'cbxwpbookmark_customizer_strip_shortcodes', $content );


			$customizer = CBXWPBookmarkHelper::customizer_default_adjust( false, true );


			if ( is_array( $customizer ) && sizeof( $customizer ) > 0 ) {
				$shortcodes = isset( $customizer['shortcodes'] ) ? $customizer['shortcodes'] : '';
				if ( $shortcodes != '' ) {
					$shortcodes = explode( ',', $shortcodes );
					if ( is_array( $shortcodes ) && sizeof( $shortcodes ) > 0 ) {
						foreach ( $shortcodes as $shortcode ) {
							if ( isset( $customizer[ $shortcode ] ) ) {

								$shortcode_params = $customizer[ $shortcode ];
								$attr_html        = '';
								foreach ( $shortcode_params as $shortcode_key => $shortcode_value ) {
									$attr_html .= ' ' . $shortcode_key . '="' . $shortcode_value . '" ';
								}

								$content .= do_shortcode( '[' . $shortcode . ' ' . $attr_html . ' ]' );
							}
						}
					}
				}//if any shortcode enabled
			}//if there is customizer values
		}//if customizer way


		return $content;
	}//end the_content_customizer_method

	/**
	 * Auto integration for 'the_excerpt'
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function the_excerpt_auto_integration( $content ) {
		return $this->bookmark_auto_integration( $content );
	}//end  the_excerpt_auto_integration


	/**
	 * Show Bookmark button before or after the content
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function bookmark_auto_integration( $content ) {
		//disable for woocommerce pages
		if ( function_exists( 'is_account_page' ) ) {
			if ( is_account_page() || is_cart() || is_checkout() || is_checkout_pay_page() ) {
				return $content;
			}
		}

		/**
		 * Restrict auto integrtion in admin
		 *
		 * Some 3rd party plugins implements the_content hooks in dashboard and this auto integration creates problem
		 *
		 * @since  1.6.12
		 */
		if ( is_admin() ) {
			return $content;
		}

		$setting = $this->settings_api;


		$mybookmark_pageid = absint( $setting->get_option( 'mybookmark_pageid', 'cbxwpbookmark_basics', 0 ) );


		$user_id = get_current_user_id();
		global $post;

		if ( ! isset( $post->ID ) ) {
			return $content;
		}

		$post_id = intval( $post->ID );


		if ( $post_id > 0 && ( $post_id == $mybookmark_pageid ) ) {
			return $content;
		}


		$post_type = $post->post_type;

		$post_types_to_show_bookmark = $setting->get_option( 'cbxbookmarkposttypes', 'cbxwpbookmark_basics', array(
			'post',
			'page'
		) );
		if(!is_array($post_types_to_show_bookmark)) $post_types_to_show_bookmark = array();

		$post_types_automation = $setting->get_option( 'post_types_automation', 'cbxwpbookmark_basics', array());
		if(!is_array($post_types_automation)) $post_types_automation = array();

		$position        = $setting->get_option( 'cbxbookmarkpostion', 'cbxwpbookmark_basics', 'after_content' );
		$skip_ids        = $setting->get_option( 'skip_ids', 'cbxwpbookmark_basics', '' );
		$skip_roles      = $setting->get_option( 'skip_roles', 'cbxwpbookmark_basics', '' );
		$show_in_archive = intval( $setting->get_option( 'showinarchive', 'cbxwpbookmark_basics', 0 ) );
		$show_in_home    = intval( $setting->get_option( 'showinhome', 'cbxwpbookmark_basics', 0 ) );
		$showcount       = intval( $setting->get_option( 'showcount', 'cbxwpbookmark_basics', 0 ) );


		//if disabled return content
		if ( $position == 'disable' ) {
			return $content;
		}

		//global $wp_the_query;


		/*if(is_main_query() && is_singular()){
			if($skip_ids != ''){
				$skip_ids_arr = explode( ',', $skip_ids );
				if ( sizeof( $skip_ids_arr ) > 0 ) {
					if ( in_array( $skip_ids_arr, $skip_ids_arr ) ) {
						return $content;
					}
				}
			}
		}*/

		//if(!is_main_query()) return $content;


		//if bookmark allowed for post types
		if ( ! in_array( $post_type, $post_types_to_show_bookmark ) ) {
			return $content;
		}

		//if automation allowed for post types
		if ( ! in_array( $post_type, $post_types_automation ) ) {
			return $content;
		}

		//if archive and show archive false then return content
		if ( ! $show_in_archive && is_archive() ) {
			return $content;
		}

		//if home and show in home false then return content
		if ( ! $show_in_home && ( is_home() && is_front_page() ) ) {
			return $content;
		}


		//grab bookmark button html
		if ( is_array( $skip_roles ) ) {
			$skip_roles = implode( ',', $skip_roles );
		}

		$auto_integration_ok = true;

		$bookmark_html = apply_filters( 'cbxwpbookmark_auto_integration', $auto_integration_ok, $post_id, $post_type, $showcount, $skip_ids, $skip_roles ) ? show_cbxbookmark_btn( $post_id, $post_type, $showcount, '', $skip_ids, $skip_roles ) : '';


		//attach the bookmark button html before or after the content
		if ( $position == 'after_content' ) {
			return $content . $bookmark_html;
		} elseif ( $position == 'before_content' ) {
			return $bookmark_html . $content;
		}
	}//end bookmark_auto_integration

	/**
	 * Render bookmark button - shortcode callback
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function bookmark_button_shortcode( $attr ) {
		// Checking Available Parameter
		global $post;

		$attr = shortcode_atts(
			array(
				'object_id'        => intval( $post->ID ),
				'object_type'      => $post->post_type,
				'show_count'       => 1,
				'extra_wrap_class' => '',
				'skip_ids'         => '',
				'skip_roles'       => '' //example 'administrator, editor, author, contributor, subscriber'
			), $attr, 'cbxwpbookmarkbtn' );

		extract( $attr );


		return show_cbxbookmark_btn( $object_id, $object_type, $show_count, $extra_wrap_class, $skip_ids, $skip_roles );
	}//end bookmark_button_shortcode

	/**
	 * Shows any user's bookmarked categories using shortcode
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function bookmark_mycat_shortcode( $attr ) {
		$setting       = new CBXWPBookmark_Settings_API();
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$current_user_id = get_current_user_id();

		$attr = shortcode_atts(
			array(
				'title'          => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
				//if empty title will not be shown
				'order'          => 'ASC',
				//DESC, ASC
				'orderby'        => 'cat_name',	//other possible values  id, cat_name, privacy
				'privacy'        => 2, //1 = public 0 = private  2= ignore
				'display'        => 0, 	//0 = list  1= dropdown,
				'show_count'     => 0,
				'allowedit'      => 0,
				'show_bookmarks' => 0,
				//show bookmark as sublist on click on category
				'userid'         => $current_user_id,
				'base_url'       => cbxwpbookmarks_mybookmark_page_url()
			), $attr, 'cbxwpbookmark-mycat'
		);

		$userid_temp = $attr['userid'];

		//let's find out if the userid is email or username
		if ( is_email( $userid_temp ) ) {
			$user_temp = get_user_by( 'email', $userid_temp );
			if ( $user_temp !== false ) {
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = $userid_temp;
				}
			} else {
				//email but user not found so reset it to guest
				$attr['userid'] = 0;
			}

		} else if ( ! is_numeric( $userid_temp ) ) {
			if ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else if ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}
		}

		//if the shortcode page linked with user id
		//if ( isset( $_GET['userid'] ) && absint( $_GET['userid'] ) > 0 ) {
		//$attr['userid'] = absint( $_GET['userid'] );
		//}

		//if ( $attr['userid'] == '' || $attr['userid'] == 0 ) {
		//$attr['userid'] = $current_user_id;
		//}

		if ( isset( $_GET['userid'] ) ) {
			$userid_temp = $_GET['userid'];

			if ( is_numeric( $userid_temp ) ) {
				//if user id is used
				$attr['userid'] = absint( $userid_temp );
			} else if ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else if ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}


		}

		$attr['userid'] = absint( $attr['userid'] );

		$output = '';

		//if other than no_cat mode we will have category
		if ( $bookmark_mode != 'no_cat' ) {
			$output .= '<div class="cbxbookmark-category-list-wrap">';
			if ( $attr['title'] != '' ) {
				$output .= '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-mycat">' . $attr['title'] . '</h3>';
			}

			$create_category_html = '';

			if ($bookmark_mode == 'user_cat' && is_user_logged_in() && (absint( $attr['userid'] ) == $current_user_id) ) {
				$create_category_html = CBXWPBookmarkHelper::create_category_html( $attr );
			}

			$output .= ( intval( $attr['display'] ) == 0 ) ? $create_category_html : '';
			$output .= ( intval( $attr['display'] ) == 0 ) ? '<ul class="cbxwpbookmark-list-generic cbxbookmark-category-list cbxbookmark-category-list-' . $bookmark_mode . ' cbxbookmark-category-list-sc">' : '';
			$output .= cbxbookmark_mycat_html( $attr );
			$output .= ( intval( $attr['display'] ) == 0 ) ? '</ul>' : '';
			$output .= '</div>';
		} else {
			//this message is better to hide
			$output .= __( '<strong>Sorry, This widget is not compatible as per setting. This widget can be used only if bookmark mode is "User owns category" or "Global Category"</strong>', 'cbxwpbookmark' );
		}


		return $output;
	}//end bookmark_mycat_shortcode

	/**
	 * Bookmarked Posts shortcode callback
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function bookmarked_posts_shortcode( $attr ) {
		// Checking Available Parameter
		global $wpdb;
		$cbxwpbookmrak_table         = $wpdb->prefix . 'cbxwpbookmark';
		$cbxwpbookmak_category_table = $wpdb->prefix . 'cbxwpbookmarkcat';


		$setting       = $this->settings_api;
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$current_user_id = get_current_user_id();

		$attr = shortcode_atts(
			array(
				'title'          => esc_html__( 'All Bookmarks', 'cbxwpbookmark' ),
				'order'          => 'DESC',
				'orderby'        => 'id', //id, object_id, object_type, title
				'limit'          => 10,
				'type'           => '', //post or object type, multiple post type in comma
				'catid'          => '', //category id
				'loadmore'       => 1,  //this is shortcode only params
				'cattitle'       => 1,  //show category title,
				'catcount'       => 1,  //show item count per category
				'allowdelete'    => 0,
				'allowdeleteall' => 0,
				'userid'         => $current_user_id,
				'offset'         => 0
			), $attr, 'cbxwpbookmark' );

		$attr['title'] = $title = sanitize_text_field( $attr['title'] );


		//if the url has cat id (cbxbmcatid get param) thenm use it or try it from shortcode
		$attr['catid'] = ( isset( $_GET['cbxbmcatid'] ) && $_GET['cbxbmcatid'] != null ) ? sanitize_text_field( $_GET['cbxbmcatid'] ) : $attr['catid'];

		if ( $attr['catid'] == 0 ) {
			$attr['catid'] = '';
		}//compatibility with previous shortcode default values
		$attr['catid'] = array_filter( explode( ',', $attr['catid'] ) );


		$userid_temp = $attr['userid'];

		//let's find out if the userid is email or username
		if ( is_email( $userid_temp ) ) {
			//possible email
			$user_temp = get_user_by( 'email', $userid_temp );

			if ( $user_temp !== false ) {
				$userid_temp = absint( $user_temp->ID );

				if ( $userid_temp > 0 ) {
					$attr['userid'] = $userid_temp;
				}
			} else {
				//email but user not found so reset it to guest
				$attr['userid'] = 0;
			}

		} else if ( ! is_numeric( $userid_temp ) ) {
			if ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else if ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}
		}


		//get userid from url linked from other page
		//if ( isset( $_GET['userid'] ) && absint( $_GET['userid'] ) > 0 ) {

		if ( isset( $_GET['userid'] ) ) {
			$userid_temp = $_GET['userid'];

			if ( is_numeric( $userid_temp ) ) {
				//if user id is used
				$attr['userid'] = absint( $userid_temp );
			} else if ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else if ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}


		}

		$attr['userid'] = absint( $attr['userid'] );

		//determine if we allow user to delete
		$allow_delete_all_html  = '';
		$attr['allowdeleteall'] = $allow_delete_all = absint( $attr['allowdeleteall'] );
		if ( $allow_delete_all && is_user_logged_in() && $attr['userid'] == $current_user_id ) {
			$allow_delete_all      = 1;
			$allow_delete_all_html = '<a data-busy="0" class="cbxwpbookmark_deleteall" href="#" class="">' . esc_html__( 'Delete All', 'cbxwpbookmark' ) . '</a>';
		}


		//if ( $attr['userid'] == '' || $attr['userid'] == 0 ) {
		/*if ( $attr['userid'] == 0 ) {
			$attr['userid'] = $current_user_id;
		}*/


		$attr['type'] = array_filter( explode( ',', $attr['type'] ) );

		extract( $attr );

		$limit = intval( $limit );
		if ( $limit == 0 ) {
			$limit = 10;
		}


		$show_loadmore_html = '';
		//$loadmore_busy_icon = '';

		$wpbm_ajax_icon = CBXWPBOOKMARK_ROOT_URL . 'assets/img/busy.gif';

		$privacy = 2; //all
		if ( $userid == 0 || ( $userid != get_current_user_id() ) ) {
			$privacy     = 1;
			$allowdelete = 0;

			$attr['privacy']     = $privacy;
			$attr['allowdelete'] = $allowdelete;
		}

		$total_sql            = '';
		$cat_sql              = '';
		$category_privacy_sql = '';
		$type_sql             = '';


		//if ($catid != 0 && $bookmark_mode != 'no_cat')
		if ( is_array( $catid ) && sizeof( $catid ) > 0 && ( $bookmark_mode != 'no_cat' ) ) {
			$cats_ids_str = implode( ',', $catid );
			$cat_sql      .= " AND cat_id IN ($cats_ids_str) ";
		}

		//get cats
		if ( $bookmark_mode == 'user_cat' ) {
			//same user seeing
			if ( $privacy != 2 ) {
				$cats = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE privacy = %d", intval( $privacy ) ), ARRAY_A );
			} else {
				$cats = $wpdb->get_results( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE 1", ARRAY_A );
			}

			$cats_ids = array();
			if ( is_array( $cats ) && sizeof( $cats ) > 0 ) {
				foreach ( $cats as $cat ) {
					$cats_ids[] = intval( $cat['id'] );
				}

				$cats_ids_str         = implode( ', ', $cats_ids );
				$category_privacy_sql .= " AND cat_id IN ($cats_ids_str) ";
			}
		}


		if ( sizeof( $type ) == 0 ) {
			$param = array( $userid );
			//$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $category_privacy_sql group by object_id  ORDER BY $orderby $order) AS TotalData";
			$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $category_privacy_sql group by object_id) AS TotalData";
		} else {
			$type_sql .= " AND object_type IN ('" . implode( "','", $type ) . "') ";


			$param = array( $userid );
			//$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $type_sql $category_privacy_sql group by object_id   ORDER BY $orderby $order) AS TotalData";
			$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $type_sql $category_privacy_sql group by object_id) AS TotalData";

		}

		$total_count = intval( $wpdb->get_var( $wpdb->prepare( $total_sql, $param ) ) );

		$total_page = ceil( $total_count / $limit );

		$extra_css_class = '';
		if ( $attr['loadmore'] == 1 && $total_page > 1 ) {
			$extra_css_class    = 'cbxwpbookmark-mylist-sc-more';
			$offset             += $limit;
			$loadmore_busy_icon = '<span data-busy="0" class="cbxwpbm_ajax_icon">' . esc_html__( 'Loading ...', 'cbxwpbookmark' ) . '<img src = "' . $wpbm_ajax_icon . '"/></span>';
			$show_loadmore_html = '<p class="cbxbookmark-more-wrap"><a href="#" class="cbxbookmark-more" data-cattitle="' . $cattitle . '" data-order="' . $order . '" data-orderby="' . $orderby . '"  data-userid="' . $userid . '" data-limit="' . $limit . '" data-offset="' . $offset . '" data-catid="' . implode( ',', $catid ) . '" data-type="' . implode( ',', $type ) . '" data-totalpage="' . $total_page . '" data-currpage="1" data-allowdelete="' . intval( $allowdelete ) . '">' . esc_html__( 'Load More', 'cbxwpbookmark' ) . '</a>' . $loadmore_busy_icon . '</p>';
		}



		//if only bookmark mode is user or global cat
		if ( intval( $cattitle ) && $bookmark_mode != 'no_cat' ) {
			if ( sizeof( $catid ) == 0 ) {
				//$category_title = '<h3 class="cbxwpbookmark-title cbxwpbookmark-postlist">' . esc_html__( 'All Bookmarks', 'cbxwpbookmark' ) . '</h3>';
			} else {
				if ( sizeof( $catid ) == 1 ) {
					$cat_info = CBXWPBookmarkHelper::getBookmarkCategoryById( reset( $catid ) );

					if ( is_array( $cat_info ) && sizeof( $cat_info ) > 0 ) {
						$catcount_html = '';
						if ( $catcount ) {
							$cat_bookmark_count = CBXWPBookmarkHelper::getTotalBookmarkByCategory( reset( $catid ) );
							$catcount_html      = '<i>(' . number_format_i18n( $cat_bookmark_count ) . ')</i>';
						}

						$title = $cat_info['cat_name'] . $catcount_html;
					}
				}

			}
		}


		if ( $title != '' ) {
			$title = '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-postlist">' . $title . $allow_delete_all_html . '</h3>';
		} else {
			$title = '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-postlist">' . $allow_delete_all_html . '</h3>';
		}

		return '<div class="cbxwpbookmark-mylist-wrap">' . $title . '<ul class="cbxwpbookmark-list-generic cbxwpbookmark-mylist cbxwpbookmark-mylist-sc ' . $extra_css_class . '" >' . cbxbookmark_post_html( $attr ) . '</ul>' . $show_loadmore_html . '</div>';
	}//end bookmarked_posts_shortcode

	/**
	 * My Bookmarked posts Load more ajax hook
	 */
	public function bookmark_loadmore() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		$instance = array();
		$message  = array();

		if ( isset( $_POST['limit'] ) && $_POST['limit'] != null ) {
			$instance['limit'] = intval( $_POST['limit'] );
		}

		if ( isset( $_POST['offset'] ) && $_POST['offset'] != null ) {
			$instance['offset'] = intval( $_POST['offset'] );
		}

		if ( isset( $_POST['catid'] ) ) {
			$catid             = sanitize_text_field( $_POST['catid'] );
			$instance['catid'] = array_filter( explode( ',', $catid ) );
		}

		if ( isset( $_POST['type'] ) ) {
			$type             = sanitize_text_field( $_POST['type'] );
			$instance['type'] = array_filter( explode( ',', $type ) );
		}

		if ( isset( $_POST['userid'] ) && $_POST['userid'] != 0 ) {
			$instance['userid'] = intval( $_POST['userid'] );
		}

		if ( isset( $_POST['order'] ) && $_POST['order'] != null ) {
			$instance['order'] = esc_attr( $_POST['order'] );
		}

		if ( isset( $_POST['orderby'] ) && $_POST['orderby'] != null ) {
			$instance['orderby'] = esc_attr( $_POST['orderby'] );
		}

		$instance['allowdelete'] = intval( $_POST['allowdelete'] );

		if ( function_exists( 'cbxbookmark_post_html' ) && cbxbookmark_post_html( $instance, false ) ) {
			$message['code'] = 1;
			$message['data'] = cbxbookmark_post_html( $instance, false );
		} else {
			$message['code'] = 0;
		}

		echo json_encode( $message );
		wp_die();
	}//end bookmark_loadmore


	/**
	 * Most bookmarked post shortcode
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function most_bookmarked_posts_shortcode( $attr ) {
		$attr = shortcode_atts(
			array(
				'title'      => esc_html__( 'Most Bookmarked Posts', 'cbxwpbookmark' ),	//if empty title will not be shown
				'order'      => 'DESC',
				'orderby'    => 'object_count',	//id, object_id, object_type, object_count, title
				'limit'      => 10,
				'type'       => '',	//db col name object_type,  post types eg, post, page, any custom post type, for multiple comma separated
				'daytime'    => 0,// 0 means all time,  any numeric values as days
				'show_count' => 1,
				'show_thumb' => 1,
				'ul_class'   => '',
				'li_class'   => ''
			), $attr, 'cbxwpbookmark-most' );

		$style_attr = array(
			'ul_class' => esc_attr($attr['ul_class']),
			'li_class' => esc_attr($attr['li_class'])
		);

		$attr['type'] = array_filter( explode( ',', $attr['type'] ) );

		return '<div class="cbxwpbookmark-mostlist-wrap">' . cbxbookmark_most_html( $attr, $style_attr ) . '</div>';
	}//end most_bookmarked_posts_shortcode


	/**
	 *  Add new category from the category listing
	 */
	public function add_category_std() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		$message = array();

		//$settings_api  = new CBXWPBookmark_Settings_API();
		$settings_api  = $this->settings_api;
		$bookmark_mode = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		if ( $bookmark_mode != 'user_cat' ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category add failed!', 'cbxwpbookmark' );
			echo json_encode( $message );

			wp_die();
		}

		$user_id = get_current_user_id(); //get the current logged in user id

		$can_user_create_own_category = apply_filters( 'cbxwpbookmark_can_user_create_own_category', true, $user_id );


		if ( ! $can_user_create_own_category ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Sorry, you can not create any more category, maximum allowed reached or you do not have enough permission.', 'cbxwpbookmark' );
			echo json_encode( $message );

			wp_die();
		}

		global $wpdb;
		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$cat_name    = isset( $_POST['cat_name'] ) ? sanitize_text_field( $_POST['cat_name'] ) : '';
		$cat_privacy = intval( $_POST['privacy'] );


		$message = array();

		$sql = $wpdb->prepare( "SELECT id FROM $category_table WHERE cat_name = %s and user_id = %d", $cat_name, $user_id );
		if ( $cat_name != '' ) {
			$duplicate = $wpdb->get_var( $sql );
		}


		if ( $cat_name == '' ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category name can not be empty', 'cbxwpbookmark' );
		} else if ( intval( $duplicate ) > 0 ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category with same name already exists!', 'cbxwpbookmark' );
		} else {
			//create category
			$return = $wpdb->query( $wpdb->prepare( "INSERT INTO $category_table ( cat_name, user_id, privacy ) VALUES ( %s, %d, %d )", array(
				$cat_name,
				$user_id,
				$cat_privacy
			) ) );

			if ( $return !== false ) {
				$cat_id = $wpdb->insert_id; //get the newly created category id or already exists one
				do_action( 'cbxbookmark_category_added', $cat_id, $user_id, $cat_name );

				$user_bookmark_page_url = cbxwpbookmarks_mybookmark_page_url();
				$cat_pernalink          = $user_bookmark_page_url;
				$cat_pernalink          = add_query_arg( array(
					'cbxbmcatid' => $cat_id,
					'userid'     => $user_id
				), $cat_pernalink );

				$message['code']      = 1;
				$message['msg']       = esc_html__( 'Category created successfully!', 'cbxwpbookmark' );
				$message['id']        = $cat_id;
				$message['userid']    = $user_id;
				$message['privacy']   = $cat_privacy;
				$message['list_html'] = '<li class="cbxbookmark-category-list-item " data-id="' . intval( $cat_id ) . '" data-userid="' . intval( $user_id ) . '" data-privacy="' . intval( $cat_privacy ) . '" data-name="' . wp_strip_all_tags( $cat_name ) . '"> <a href="' . esc_url( $cat_pernalink ) . '" class="cbxlbjs-item-widget" data-privacy="' . intval( $cat_privacy ) . '">' . wp_strip_all_tags( $cat_name ) . '</a><i>(0)</i><span title="' . esc_html__( 'Click to edit', 'cbxwpbookmark' ) . '" class="cbxbookmark-edit-btn"></span> <span title="' . esc_html__( 'Click to delete', 'cbxwpbookmark' ) . '" class="cbxbookmark-delete-btn" data-id="' . intval( $cat_id ) . '"></span></li>';
			} else {
				$message['code'] = 0;
				$message['msg']  = esc_html__( 'Category add failed!', 'cbxwpbookmark' );
			}
		}


		echo json_encode( $message );

		wp_die();
	}//end add_category_std

	/**
	 *  Add new category
	 */
	public function add_category() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		global $wpdb;
		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$cat_id      = isset( $_POST['cat_id'] ) ? intval( $_POST['cat_id'] ) : 0;
		$cat_name    = isset( $_POST['cat_name'] ) ? sanitize_text_field( $_POST['cat_name'] ) : '';
		$cat_privacy = intval( $_POST['privacy'] );
		$object_id   = intval( $_POST['object_id'] );
		$object_type = isset( $_POST['object_type'] ) ? esc_attr( $_POST['object_type'] ) : 'post'; //post, page, user, product, any thing custom


		$user_id = get_current_user_id(); //get the current logged in user id
		$message = array();


		if ( $cat_id == 0 ) {
			//category create mode
			$sql = $wpdb->prepare( "SELECT id FROM $category_table WHERE cat_name = %s and user_id = %d", $cat_name, $user_id );
		} else {
			//category edit mode
			$sql = $wpdb->prepare( "SELECT id FROM $category_table WHERE cat_name = %s AND id != %d AND user_id = %d", $cat_name, $cat_id, $user_id );
		}

		if ( $cat_name != '' ) {
			$duplicate = $wpdb->get_var( $sql );
		}


		if ( $cat_name == '' ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category name can not be empty', 'cbxwpbookmark' );
		} else if ( intval( $duplicate ) > 0 ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category with same name already exists!', 'cbxwpbookmark' );
		} else {

			if ( $cat_id == 0 ) {
				//create category
				$return = $wpdb->query( $wpdb->prepare( "INSERT INTO $category_table ( cat_name, user_id, privacy ) VALUES ( %s, %d, %d )", array(
					$cat_name,
					$user_id,
					$cat_privacy
				) ) );
			} else {
				//Update category
				$return = $wpdb->update(
					$category_table, array(
					'cat_name' => $cat_name,   // string
					'privacy'  => $cat_privacy // integer (number)
				), array(
					'id'      => $cat_id,
					'user_id' => $user_id
				), array(
					'%s', // value1
					'%d'  // value2
				), array(
						'%d',
						'%d'
					)
				);
			}


			if ( $return !== false ) {

				$mode = 'update';
				if ( $cat_id == 0 ) {
					$mode   = 'add';
					$cat_id = $wpdb->insert_id; //get the newly created category id or already exists one
				}


				$cats_by_user = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $category_table WHERE user_id = %d", array( $user_id ) ), ARRAY_A );

				$post_in_cats_t = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT cat_id FROM $bookmark_table WHERE object_type = %s AND  user_id = %d AND object_id = %d", array(
					$object_type,
					$user_id,
					$object_id
				) ), ARRAY_A );


				$post_in_cats = array();
				foreach ( $post_in_cats_t as $cat ) {
					$post_in_cats[] = $cat['cat_id'];
				}

				foreach ( $cats_by_user as &$row ) {
					if ( in_array( $row['id'], $post_in_cats ) ) {
						$row['incat'] = 1;
					} else {
						$row['incat'] = 0;
					}
				}

				$message['code']   = 1;
				$message['msg']    = esc_html__( 'Category created/edited successfully!', 'cbxwpbookmark' );
				$message['cat_id'] = $cat_id;
				if ( $cats_by_user !== false ) {
					$message['cats'] = json_encode( $cats_by_user );
				} else {
					$message['cats'] = 0;
				}

				if ( $mode == 'add' ) {
					do_action( 'cbxbookmark_category_added', $cat_id, $user_id, $cat_name );
				} else {
					do_action( 'cbxbookmark_category_edit', $cat_id, $user_id, $cat_name );
				}

			} else {
				$message['code'] = 0;
				$message['msg']  = esc_html__( 'Category add/edit failed!', 'cbxwpbookmark' );
			}

		}

		echo json_encode( $message );

		wp_die();
	}//end add_category

	/**
	 *  Edit a Category (From bookmark popup panel)
	 */
	public function edit_category() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		global $wpdb;
		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';


		$cat_id      = isset( $_POST['cat_id'] ) ? intval( $_POST['cat_id'] ) : 0;
		$cat_name    = isset( $_POST['cat_name'] ) ? sanitize_text_field( $_POST['cat_name'] ) : '';
		$cat_privacy = intval( $_POST['privacy'] );
		$object_id   = intval( $_POST['object_id'] );
		$object_type = isset( $_POST['object_type'] ) ? esc_attr( $_POST['object_type'] ) : 'post'; //post, page, user, product, any thing custom

		$user_id = get_current_user_id(); //get the current logged in user id
		$message = array();

		$sql = $wpdb->prepare( "SELECT id FROM $category_table WHERE cat_name = %s AND id != %d AND user_id = %d", $cat_name, $cat_id, $user_id );

		if ( $cat_name != '' ) {
			$duplicate = $wpdb->get_var( $sql );
		}


		if ( $cat_name == '' ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category name can not be empty', 'cbxwpbookmark' );
		} else if ( $cat_id == 0 ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Category id missing, are you cheating?', 'cbxwpbookmark' );
		} else if ( intval( $duplicate ) > 0 ) {
			$message['code'] = 0;
			$message['msg']  = esc_html__( 'Another Category with same name already exists!', 'cbxwpbookmark' );
		} else {
			// Update Query
			$return = $wpdb->update(
				$category_table, array(
				'cat_name' => $cat_name,   // string
				'privacy'  => $cat_privacy // integer (number)
			), array(
				'id'      => $cat_id,
				'user_id' => $user_id
			), array(
				'%s', // value1
				'%d'  // value2
			), array(
					'%d',
					'%d'
				)
			);


			if ( $return !== false ) {

				$cats_by_user = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $category_table WHERE user_id = %d", array( $user_id ) ), ARRAY_A );

				$post_in_cats_t = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT cat_id FROM $bookmark_table WHERE object_type = %s AND  user_id = %d AND object_id = %d", array(
					$object_type,
					$user_id,
					$object_id
				) ), ARRAY_A );


				$post_in_cats = array();
				foreach ( $post_in_cats_t as $cat ) {
					$post_in_cats[] = $cat['cat_id'];
				}

				foreach ( $cats_by_user as &$row ) {
					if ( in_array( $row['id'], $post_in_cats ) ) {
						$row['incat'] = 1;
					} else {
						$row['incat'] = 0;
					}
				}

				$message['code']   = 1;
				$message['msg']    = esc_html__( 'Category updated successfully!', 'cbxwpbookmark' );
				$message['cat_id'] = $cat_id;

				if ( $cats_by_user !== false ) {
					$message['cats'] = json_encode( $cats_by_user );
				} else {
					$message['cats'] = 0;
				}

				do_action( 'cbxbookmark_category_edit', $cat_id, $user_id, $cat_name );
			} else {
				$message['code'] = 0;
				$message['msg']  = esc_html__( 'Category add/edit failed!', 'cbxwpbookmark' );
			}

		}

		echo json_encode( $message );

		wp_die();
	}//end edit_category


	/**
	 * Update Category(from user edit panel)
	 *
	 */
	public function update_bookmark_category() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );
		if ( isset( $_POST ) ) {
			global $wpdb;

			$data = array();

			$cat_name = sanitize_text_field( wp_unslash( $_POST['catname'] ) );
			$cat_id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			$privacy  = intval( $_POST['privacy'] );
			$user_id  = get_current_user_id();

			// Category Table with database Prefix
			$bookmarkcategory_table = $wpdb->prefix . 'cbxwpbookmarkcat';

			// Update Query
			$update = $wpdb->update(
				$bookmarkcategory_table, array(
				'cat_name' => $cat_name, // string
				'privacy'  => $privacy   // integer (number)
			), array(
				'id'      => $cat_id,
				'user_id' => $user_id
			), array(
				'%s', // value1
				'%d'  // value2
			), array(
					'%d',
					'%d'
				)
			);

			if ( $update !== false ) {

				do_action( 'cbxbookmark_category_edit', $cat_id, $user_id, $cat_name );

				$data['msg']     = esc_html__( "Data Updated Successfully", "cbxwpbookmark" );
				$data['flag']    = 1;
				$data['catname'] = $cat_name;
				$data['privacy'] = $privacy;
			} else {

				$data['msg']  = esc_html__( "Update Failed", "cbxwpbookmark" );
				$data['flag'] = 0;
			}

			echo $data = json_encode( $data );
		}
		wp_die();
	}//end update_bookmark_category

	/**
	 *
	 * Delete Category
	 */
	public function delete_bookmark_category() {

		check_ajax_referer( 'cbxbookmarknonce', 'security' );
		$message = array();

		global $wpdb;


		$setting       = $this->settings_api;
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		if ( isset( $_POST ) && $bookmark_mode == 'user_cat' ) {
			$cat_id = intval( $_POST['id'] );


			$bookmarkcategory_table = $wpdb->prefix . 'cbxwpbookmarkcat';
			$bookmark_table         = $wpdb->prefix . 'cbxwpbookmark';

			$user_id = get_current_user_id();

			do_action( 'cbxbookmark_category_deleted_before', $cat_id, $user_id );

			$delete_category = $wpdb->delete( $bookmarkcategory_table, array(
				'id'      => $cat_id,
				'user_id' => $user_id
			), array( '%d', '%d' ) );

			if ( $delete_category !== false ) {
				//deleted successfully
				$message['msg'] = 1;

				do_action( 'cbxbookmark_category_deleted', $cat_id, $user_id );

				//now delete any bookmark entry for that category
				//$delete_bookmark = $wpdb->delete($bookmark_table, array('cat_id' => $cat_id, 'user_id' => $user_id), array('%d', '%d'));

				$bookmarks_by_category = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $bookmark_table WHERE cat_id = %d", array( $cat_id ) ), ARRAY_A );

				if ( $bookmarks_by_category != null ) {
					foreach ( $bookmarks_by_category as $single_bookmark ) {
						do_action( 'cbxbookmark_bookmark_removed_before', $single_bookmark['id'], $single_bookmark['user_id'], $single_bookmark['object_id'], $single_bookmark['object_type'] );

						$delete_status = $wpdb->query( $wpdb->prepare( "DELETE FROM $bookmark_table WHERE id=%d", intval( $single_bookmark['id'] ) ) );

						if ( $delete_status !== false ) {
							do_action( 'cbxbookmark_bookmark_removed', $single_bookmark['id'], $single_bookmark['user_id'], $single_bookmark['object_id'], $single_bookmark['object_type'] );
						}
					}
				}


				if ( isset( $_POST['object_id'] ) ) {
					$object_id   = intval( $_POST['object_id'] );
					$object_type = isset( $_POST['object_type'] ) ? esc_attr( $_POST['object_type'] ) : 'post'; //post, page, user, product, any thing custom

					$cats_by_user = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $bookmarkcategory_table WHERE user_id = %d", array( $user_id ) ), ARRAY_A );

					$post_in_cats_t = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT cat_id FROM $bookmark_table WHERE object_type = %s AND  user_id = %d AND object_id = %d", array(
						$object_type,
						$user_id,
						$object_id
					) ), ARRAY_A );


					$post_in_cats = array();
					foreach ( $post_in_cats_t as $cat ) {
						$post_in_cats[] = $cat['cat_id'];
					}

					foreach ( $cats_by_user as &$row ) {
						if ( in_array( $row['id'], $post_in_cats ) ) {
							$row['incat'] = 1;
						} else {
							$row['incat'] = 0;
						}
					}

					$message['cats'] = json_encode( $cats_by_user );

				}


			} else {

				$message['msg'] = 0;
			}
		} else {

			$message['msg'] = esc_html__( "No data available", "cbxwpbookmark" );
		}
		echo json_encode( $message );
		wp_die();
	}//end delete_bookmark_category

	/**
	 * Add Bookmark ajax request and response
	 *
	 */
	public function add_bookmark() {
		global $wpdb;

		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		//$setting       = new CBXWPBookmark_Settings_API();
		$setting       = $this->settings_api;
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$user_id   = get_current_user_id();
		$cat_id    = intval( $_POST['cat_id'] );
		$object_id = intval( $_POST['object_id'] );

		$object_type = isset( $_POST['object_type'] ) ? esc_attr( $_POST['object_type'] ) : 'post'; //post, page or any custom post and later any object type

		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$category_privacy = 1;

		if ( $bookmark_mode == 'no_cat' ) {
			$duplicate = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $bookmark_table WHERE object_type = %s AND object_id = %d AND user_id = %d", array(
				$object_type,
				$object_id,
				$user_id
			) ) );

			$category_privacy = 1; //no category that means publicly sharable information

		} else {
			$duplicate = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $bookmark_table WHERE object_type = %s AND object_id = %d AND cat_id = %d AND user_id = %d", array(
				$object_type,
				$object_id,
				$cat_id,
				$user_id
			) ) );

			$single_category  = CBXWPBookmarkHelper::singleCategory( $cat_id );
			$category_privacy = $single_category['privacy'];
		}


		$message = array();


		if ( intval( $duplicate ) > 0 ) {
			if ( $bookmark_mode == 'no_cat' ) {
				//already exists, so remove
				$return = $wpdb->query( $wpdb->prepare( "DELETE FROM $bookmark_table WHERE object_type = %s AND object_id = %d AND user_id = %d", array(
					$object_type,
					$object_id,
					$user_id
				) ) );
			} else {
				//already exists, so remove
				$return = $wpdb->query( $wpdb->prepare( "DELETE FROM $bookmark_table WHERE object_type = %s AND object_id = %d AND cat_id = %d AND user_id = %d", array(
					$object_type,
					$object_id,
					$cat_id,
					$user_id
				) ) );
			}


			if ( $return !== false ) {
				$message['code']      = 1; //operation success
				$message['msg']       = esc_html__( 'Bookmark removed!', 'cbxwpbookmark' );
				$message['operation'] = 0;
				$bookmark_id          = $duplicate;

				do_action( 'cbxbookmark_bookmark_removed', $bookmark_id, $user_id, $object_id, $object_type );
			} else {
				$message['code'] = 0; //operation failed
				$message['msg']  = esc_html__( 'Bookmark remove failed!', 'cbxwpbookmark' );
			}
		} else {
			//doesn't exists, so add
			$return = $wpdb->query( $wpdb->prepare( "INSERT INTO $bookmark_table ( object_id, object_type, cat_id, user_id ) VALUES ( %d,%s, %d, %d )", array(
				$object_id,
				$object_type,
				$cat_id,
				$user_id
			) ) );

			if ( $return !== false ) {
				$message['code']      = 1; //db operation success
				$message['msg']       = esc_html__( 'Bookmark added!', 'cbxwpbookmark' );
				$message['operation'] = 1;

				$bookmark_id = $wpdb->insert_id;

				do_action( 'cbxbookmark_bookmark_added', $bookmark_id, $user_id, $object_id, $object_type, $category_privacy );

			} else {
				$message['code'] = 0; //db operation failed
				$message['msg']  = esc_html__( 'Bookmark add failed', 'cbxwpbookmark' );
			}
		}

		$bookmark_total   = intval( CBXWPBookmarkHelper::getTotalBookmark( $object_id ) );
		$bookmark_by_user = CBXWPBookmarkHelper::isBookmarkedByUser( $object_id );

		$message['bookmark_count']  = $bookmark_total;
		$message['bookmark_byuser'] = ( $bookmark_by_user ) ? 1 : 0;

		echo json_encode( $message );
		wp_die();
	}//end add_bookmark

	/**
	 * Delete bookmarked Post
	 */
	public function delete_bookmark_post() {

		global $wpdb;
		$data = array();

		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		if ( isset( $_POST ) ) {
			$bookmark_id = intval( $_POST['bookmark_id'] );
			$object_id   = intval( $_POST['object_id'] );
			$object_type = isset( $_POST['object_type'] ) ? esc_attr( wp_unslash( $_POST['object_type'] ) ) : 'post'; //post, page or any custom post and later any object type


			$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

			$user_id = get_current_user_id();

			//$single_bookmark = CBXWPBookmarkHelper::singleBookmarkByObjectUser( $object_id, $user_id );

			do_action( 'cbxbookmark_bookmark_removed_before', $bookmark_id, $user_id, $object_id, $object_type );

			$delete_bookmark = $wpdb->delete( $bookmark_table, array(
				'object_id' => $object_id,
				'user_id'   => $user_id
			), array( '%d', '%d' ) );

			if ( $delete_bookmark !== false ) {
				$data['msg'] = 0;

				do_action( 'cbxbookmark_bookmark_removed', $bookmark_id, $user_id, $object_id, $object_type );
			} else {

				$data['msg'] = 1;
			}
		} else {

			$data['msg'] = esc_html__( "No data available", "cbxwpbookmark" );
		}

		echo json_encode( $data );
		wp_die();
	}//end delete_bookmark_post

	/**
	 * enqueue styles
	 */
	public function enqueue_styles() {
		do_action( 'cbxwpbookmark_css_start' );

		wp_register_style( 'cbxwpbookmarkpublic-css', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpbookmark-public.css', array(), CBXWPBOOKMARK_PLUGIN_VERSION, 'all' );
		wp_enqueue_style( 'cbxwpbookmarkpublic-css' );

		do_action( 'cbxwpbookmark_css_end' );
	}//end enqueue_styles

	/**
	 * enqueue scripts
	 */
	public function enqueue_scripts() {
		$setting = $this->settings_api;

		$bookmark_mode           = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );
		$category_default_status = intval( $setting->get_option( 'category_status', 'cbxwpbookmark_basics', 1 ) );

		$hide_cat_privacy = intval( $setting->get_option( 'hide_cat_privacy', 'cbxwpbookmark_basics', 0 ) );

		$cat_hide_class = ( $hide_cat_privacy == 1 ) ? 'cbxwpbkmark_cat_hide' : '';

		do_action( 'cbxwpbookmark_js_start' );

		wp_enqueue_script( 'jquery' );


		$category_template = '
            <div class="cbxbookmark-mycat-editbox">
                <input class="cbxbmedit-catname cbxbmedit-catname-edit" name="catname" value="##catname##" placeholder="' . esc_html__( 'Category title', 'cbxwpbookmark' ) . '" />                
                <select class="cbxbmedit-privacy input-catprivacy ' . $cat_hide_class . '" name="catprivacy">
                  <option value="1" title="' . esc_html__( 'Public Category', 'cbxwpbookmark' ) . '">' . esc_html__( 'Public', 'cbxwpbookmark' ) . '</option>
                  <option value="0" title="' . esc_html__( 'Private Category', 'cbxwpbookmark' ) . '">' . esc_html__( 'Private', 'cbxwpbookmark' ) . '</option>
                </select>
                <a href="#" class="cbxbookmark-btn cbxbookmark-cat-save">' . esc_html__( 'Update', 'cbxwpbookmark' ) . ' <span class="cbxbm_busy" style="display:none;"></span></a>
                <a href="#" class="cbxbookmark-btn cbxbookmark-cat-close">' . esc_html__( 'Close', 'cbxwpbookmark' ) . '</a>
                <div class="clear clearfix cbxwpbkmark-clearfix"></div>
            </div>';

		if ( $bookmark_mode != 'user_cat' ) {
			$category_template = '';
		}

		wp_register_script( 'cbxwpbookmark-events', plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-events.js', array(), CBXWPBOOKMARK_PLUGIN_VERSION, true );
		wp_register_script( 'cbxwpbookmarkpublicjs', plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpbookmark-public.js', array(
			'cbxwpbookmark-events',
			'jquery'
		), CBXWPBOOKMARK_PLUGIN_VERSION, true );

		$cbxwpbookmark_translation = array(
			'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
			'nonce'                      => wp_create_nonce( "cbxbookmarknonce" ),
			//'cbxbookmark_lang'           => get_user_locale(),
			'cat_template'               => json_encode( $category_template ),
			'category_delete_success'    => esc_html__( 'Category deleted successfully', 'cbxwpbookmark' ),
			'category_delete_error'      => esc_html__( 'Unable to delete the category', 'cbxwpbookmark' ),
			'areyousuretodeletecat'      => esc_html__( 'Are you sure you want to delete this Bookmark Category?', 'cbxwpbookmark' ),
			'areyousuretodeletebookmark' => esc_html__( 'Are you sure you want to delete this Bookmark?', 'cbxwpbookmark' ),
			'bookmark_failed'            => esc_html__( 'Faild to Bookmark', 'cbxwpbookmark' ),
			'bookmark_removed'           => esc_html__( 'Bookmark Removed', 'cbxwpbookmark' ),
			'bookmark_removed_empty'     => esc_html__( 'All Bookmarks Removed', 'cbxwpbookmark' ),
			'bookmark_removed_failed'    => esc_html__( 'Bookmark Removed Failed', 'cbxwpbookmark' ),
			'error_msg'                  => esc_html__( 'Error loading data. Response code = ', 'cbxwpbookmark' ),
			'category_name_empty'        => esc_html__( 'Category name can not be empty', 'cbxwpbookmark' ),
			'add_to_head_default'        => esc_html__( 'Click Category to Bookmark', 'cbxwpbookmark' ),
			'add_to_head_cat_list'       => esc_html__( 'Click to Edit Category', 'cbxwpbookmark' ),
			'add_to_head_cat_edit'       => esc_html__( 'Edit Category', 'cbxwpbookmark' ),
			'add_to_head_cat_create'     => esc_html__( 'Create Category', 'cbxwpbookmark' ),
			'add_to_head_max_cat'        => esc_html__( 'Maximum category limit reached', 'cbxwpbookmark' ),
			//'category_loaded_add'        => esc_html__('Click Category to Bookmark', 'cbxwpbookmark'),
			'max_cat_limit'              => 0,
			'max_cat_limit_error'        => esc_html__( 'Sorry, you reached the maximum category limit and to create one one, please delete unnecessary categories first', 'cbxwpbookmark' ),
			'user_current_cat_count'     => 0,
			'user_current_cats'          => '',
			'user_can_create_cat'        => 1,
			'bookmark_mode'              => $bookmark_mode,
			'bookmark_not_found'         => esc_html__( 'No bookmarks found', 'cbxwpbookmark' ),
			'load_more'                  => esc_html__( 'Load More ...', 'cbxwpbookmark' ),
			'category_default_status'    => $category_default_status,
			'delete_all_bookmarks_by_user_confirm'    => esc_html__( 'Are you sure to delete all of your bookmarks? This process can not be undone.', 'cbxwpbookmark' )
			//default category status if user category mode is enabled
		);

		$cbxwpbookmark_translation = apply_filters( 'cbxwpbookmark_public_jsvar', $cbxwpbookmark_translation );

		wp_localize_script( 'cbxwpbookmarkpublicjs', 'cbxwpbookmark', $cbxwpbookmark_translation );
		wp_enqueue_script( 'cbxwpbookmark-events' );

		do_action( 'cbxwpbookmark_js_before_cbxwpbookmarkpublicjs' );

		wp_enqueue_script( 'cbxwpbookmarkpublicjs' );

		do_action( 'cbxwpbookmark_js_end' );
	}//end enqueue_scripts

	/**
	 * Load bookmark sublist via ajax
	 */
	public function load_bookmarks_sublist() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		global $wpdb;
		$cbxwpbookmrak_table         = $wpdb->prefix . 'cbxwpbookmark';
		$cbxwpbookmak_category_table = $wpdb->prefix . 'cbxwpbookmarkcat';

		$setting       = $this->settings_api;
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );


		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';
		$user_id        = get_current_user_id(); //get the current logged in user id

		$cat_id    = absint( $_POST['cat_id'] );
		$cat_total = absint( $_POST['cat_total'] );
		$privacy   = absint( $_POST['privacy'] );
		$userid    = absint( $_POST['userid'] );
		$totalpage = absint( $_POST['totalpage'] );
		$page      = absint( $_POST['page'] );


		$perpage    = apply_filters( 'cbxwpbookmark_sublist_perpage', 10 );
		$total_page = ceil( $cat_total / $perpage );

		if ( $userid == 0 ) {
			$userid = get_current_user_id();
		}

		if ( $userid == 0 ) {
			$privacy = 1; //only public
		}

		$main_sql             = '';
		$cat_sql              = '';
		$category_privacy_sql = '';


		//$page = 1;


		$start_point = ( $page * $perpage ) - $perpage;
		$limit_sql   = "LIMIT";
		$limit_sql   .= ' ' . $start_point . ',';
		$limit_sql   .= ' ' . $perpage;

		$orderby = 'object_id';
		$order   = 'DESC';

		if ( $bookmark_mode == 'user_cat' ) {
			$param    = array( $userid, $cat_id );
			$main_sql .= "SELECT *  FROM $cbxwpbookmrak_table  WHERE user_id=%d AND cat_id = %d group by object_id  ORDER BY $orderby $order $limit_sql";

		} else {
			$param    = array( $cat_id );
			$main_sql .= "SELECT *  FROM $cbxwpbookmrak_table  WHERE cat_id = %d group by object_id  ORDER BY $orderby $order $limit_sql";
		}

		$items = $wpdb->get_results( $wpdb->prepare( $main_sql, $param ) );

		$output = '';

		if ( $items === null || sizeof( $items ) > 0 ) {
			$object_types = CBXWPBookmarkHelper::object_types( true ); //get plain post type as array

			$instance               = array();
			$instance['show_thumb'] = 0;


			foreach ( $items as $item ) {
				$action_html = '';

				$sub_item_class = 'cbxbookmark-category-list-sublist-item';

				if ( in_array( $item->object_type, $object_types ) ) {

					$li_output = cbxwpbookmark_get_template_html( 'bookmarkpost/single.php', array(
						'item'           => $item,
						'action_html'    => $action_html,
						'sub_item_class' => $sub_item_class
					) );

					$output .= $li_output;

				} else {
					ob_start();

					do_action( 'cbxwpbookmark_othertype_item', $instance, $item, $action_html, $sub_item_class );

					$li_output = ob_get_clean();
					$output    .= $li_output;
				}
			}
		}


		$message = array();
		//code 1 = bookmarks found
		//code 0 = bookmarks not found

		if ( $output != '' ) {
			$message['page']      = $page;
			$message['totalpage'] = $totalpage;
			$message['show_more'] = ( $page < $totalpage ) ? 1 : 0;
			$message['code']      = 1;
			$message['msg']       = esc_html__( 'Bookmarks loaded', 'cbxwpbookmark' );
			$message['output']    = json_encode( $output );
		} else {
			$message['page']      = $page;
			$message['totalpage'] = $totalpage;
			$message['show_more'] = ( $page < $totalpage ) ? 1 : 0;
			$message['code']      = 0;
			$message['msg']       = esc_html__( 'Bookmark not found', 'cbxwpbookmark' );
		}

		echo json_encode( $message );

		wp_die();
	}//end load_bookmarks_sublist

	/**
	 * Init elementor widget
	 *
	 * @throws Exception
	 */
	public function init_elementor_widgets() {
		//register the bookmark button widget
		if ( ! class_exists( 'CBXWPBookmarkbtn_ElemWidget' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor_widgets/class-cbxwpbookmark-bookmarkbtn-elemwidget.php';
		}

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CBXWPBookmark_ElemWidget\Widgets\CBXWPBookmarkbtn_ElemWidget() );

		//register the my bookmarks widget
		if ( ! class_exists( 'CBXWPBookmarks_ElemWidget' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor_widgets/class-cbxwpbookmark-bookmarks-elemwidget.php';
		}

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CBXWPBookmark_ElemWidget\Widgets\CBXWPBookmarks_ElemWidget() );

		//register the bookmark category widget
		if ( ! class_exists( 'CBXWPBookmarkCategory_ElemWidget' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor_widgets/class-cbxwpbookmark-category-elemwidget.php';
		}

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CBXWPBookmark_ElemWidget\Widgets\CBXWPBookmarkCategory_ElemWidget() );


		//register the most bookmarked posts widget
		if ( ! class_exists( 'CBXWPBookmarkmost_ElemWidget' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor_widgets/class-cbxwpbookmark-bookmarkmost-elemwidget.php';
		}

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CBXWPBookmark_ElemWidget\Widgets\CBXWPBookmarkmost_ElemWidget() );


	}//end widgets_registered

	/**
	 * Add new category to elementor
	 *
	 * @param $elements_manager
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'cbxwpbookmark',
			array(
				'title' => esc_html__( 'CBX Bookmark Widgets', 'cbxwpbookmark' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}//end add_elementor_widget_categories

	/**
	 * Load Elementor Custom Icon
	 */
	public function elementor_icon_loader() {
		wp_register_style( 'cbxwpbookmark_elementor_icon',
			CBXWPBOOKMARK_ROOT_URL . 'assets/css/cbxwpbookmark-elementor.css', false, $this->version );
		wp_enqueue_style( 'cbxwpbookmark_elementor_icon' );

	}//end elementor_icon_loader

	/**
	 * WPBakery Widgets registers
	 *
	 * Before WPBakery inits includes the new widgets
	 */
	public function vc_before_init_actions() {
		//include vc params
		if ( ! class_exists( 'CBXWPBookmark_VCParam_DropDownMulti' ) ) {
			require_once CBXWPBOOKMARK_ROOT_PATH . 'widgets/vc_widgets/params/class-cbxwpbookmark-vc-param-dropdown-multi.php';
		}

		//includes the vc widgets
		//bookmark button widget
		if ( ! class_exists( 'CBXWPBookmarkbtn_VCWidget' ) ) {
			require_once CBXWPBOOKMARK_ROOT_PATH . 'widgets/vc_widgets/class-cbxwpbookmark-bookmarkbtn-vcwidget.php';
		}
		new CBXWPBookmarkbtn_VCWidget();

		//my bookmarked post widget
		if ( ! class_exists( 'CBXWPBookmarks_VCWidget' ) ) {
			require_once CBXWPBOOKMARK_ROOT_PATH . 'widgets/vc_widgets/class-cbxwpbookmark-bookmarks-vcwidget.php';
		}
		new CBXWPBookmarks_VCWidget();

		//my bookmark category widget
		if ( ! class_exists( 'CBXWPBookmarkCategory_VCWidget' ) ) {
			require_once CBXWPBOOKMARK_ROOT_PATH . 'widgets/vc_widgets/class-cbxwpbookmark-category-vcwidget.php';
		}
		new CBXWPBookmarkCategory_VCWidget();


		//most bookmarked post widget
		if ( ! class_exists( 'CBXWPBookmarkmost_VCWidget' ) ) {
			require_once CBXWPBOOKMARK_ROOT_PATH . 'widgets/vc_widgets/class-cbxwpbookmark-bookmarkmost-vcwidget.php';
		}
		new CBXWPBookmarkmost_VCWidget();
	}//end vc_before_init_actions

	public function admin_init_ajax_lang() {
		if ( defined( 'DOING_AJAX' ) ) {
			//write_log($_REQUEST);
		}
	}

	/**
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_theme_class( $classes ): array {
		$setting              = $this->settings_api;
		$bookmark_theme_class = $setting->get_option( 'display_theme', 'cbxwpbookmark_basics', 'cbxwpbookmark-blue' );

		return array_merge( $classes, array( $bookmark_theme_class ) );
	}//end method add_theme_class

	/**
	 * Delete all bookmarks of any user by user from frontend
	 */
	public function delete_all_bookmarks_by_user() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );
		$message         = array();
		if(!is_user_logged_in()){
			$message['code'] = 1;
			$message['msg']  = esc_html__( 'Please login to delete your bookmarks.', 'cbxwpbookmark' );

			wp_send_json( $message );
		}

		$user_id   = absint( get_current_user_id() );
		$bookmarks = CBXWPBookmarkHelper::getBookmarksByUser( $user_id );

		if ( is_array( $bookmarks ) && sizeof( $bookmarks ) > 0 ) {
			global $wpdb;
			$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

			foreach ( $bookmarks as $single_bookmark ) {
				$id = absint( $single_bookmark['id'] );
				do_action( 'cbxbookmark_bookmark_removed_before', $id, $single_bookmark['user_id'], $single_bookmark['object_id'], $single_bookmark['object_type'] );

				$delete_status = $wpdb->query( $wpdb->prepare( "DELETE FROM $cbxwpbookmrak_table WHERE id=%d", intval( $id ) ) );

				if ( $delete_status !== false ) {
					do_action( 'cbxbookmark_bookmark_removed', $id, $single_bookmark['user_id'], $single_bookmark['object_id'], $single_bookmark['object_type'] );
				}
			}
		}


		$message['code'] = 1;
		$message['msg']  = esc_html__( 'All bookmarks deleted.', 'cbxwpbookmark' );

		wp_send_json( $message );
	}//end delete_all_bookmarks_by_user

	public function bbp_template_before_single_topic(){
		$this->bbp_template_single_topic_automation('before');
	}//end bbp_template_before_single_topic

	public function bbp_template_after_single_topic(){
		$this->bbp_template_single_topic_automation('after');
	}//end bbp_template_after_single_topic

	public function bbp_template_before_single_forum(){
		$this->bbp_template_single_forum_automation('before');
	}//end bbp_template_before_single_forum
	public function bbp_template_after_single_forum(){
		$this->bbp_template_single_forum_automation('after');
	}//end bbp_template_after_single_forum

	/**
	 * bbpress forum : display bookmark automatically(not in used)
	 *
	 * @param $content
	 * @param $r
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function bbp_get_single_forum_description($content, $r, $args){


		$post_id = bbp_get_forum_id( $r['forum_id'] );


		$post_type = 'forum';

		$setting = $this->settings_api;

		$post_types_to_show_bookmark = $setting->get_option( 'cbxbookmarkposttypes', 'cbxwpbookmark_basics', array(
			'post',
			'page'
		) );
		if(!is_array($post_types_to_show_bookmark)) $post_types_to_show_bookmark = array();

		$post_types_automation = $setting->get_option( 'post_types_automation', 'cbxwpbookmark_basics', array());
		if(!is_array($post_types_automation)) $post_types_automation = array();


		$position        = $setting->get_option( 'cbxbookmarkpostion', 'cbxwpbookmark_basics', 'after_content' );
		$skip_ids        = $setting->get_option( 'skip_ids', 'cbxwpbookmark_basics', '' );
		$skip_roles      = $setting->get_option( 'skip_roles', 'cbxwpbookmark_basics', '' );
		$showcount       = intval( $setting->get_option( 'showcount', 'cbxwpbookmark_basics', 0 ) );

		if ( $position == 'disable' ) {
			return $content;
		}

		//if bookmark allowed for post types
		if ( ! in_array( $post_type, $post_types_to_show_bookmark ) ) {
			return $content;
		}

		//if automation allowed for post types
		if ( ! in_array( $post_type, $post_types_automation ) ) {
			return $content;
		}

		//grab bookmark button html
		if ( is_array( $skip_roles ) ) {
			$skip_roles = implode( ',', $skip_roles );
		}


		$auto_integration_ok = true;

		$bookmark_html = apply_filters( 'cbxwpbookmark_auto_integration', $auto_integration_ok, $post_id, $post_type, $showcount, $skip_ids, $skip_roles ) ? show_cbxbookmark_btn( $post_id, $post_type, $showcount, '', $skip_ids, $skip_roles ) : '';


		//attach the bookmark button html before or after the content
		if ( $position == 'after_content' ) {
			return $content . $bookmark_html;
		} elseif ( $position == 'before_content' ) {
			return $bookmark_html . $content;
		}

		return $content;
	}//end  bbp_get_single_forum_description

	/**
	 * bbpress forum : display bookmark automatically
	 *
	 *
	 * @return mixed|string
	 */
	public function bbp_template_single_forum_automation($pos = 'before'){

		$post_id = bbp_get_forum_id( );



		$post_type = 'forum';

		$setting = $this->settings_api;

		$post_types_to_show_bookmark = $setting->get_option( 'cbxbookmarkposttypes', 'cbxwpbookmark_basics', array(
			'post',
			'page'
		) );
		if(!is_array($post_types_to_show_bookmark)) $post_types_to_show_bookmark = array();

		$post_types_automation = $setting->get_option( 'post_types_automation', 'cbxwpbookmark_basics', array());
		if(!is_array($post_types_automation)) $post_types_automation = array();


		$position        = $setting->get_option( 'cbxbookmarkpostion', 'cbxwpbookmark_basics', 'after_content' );
		$skip_ids        = $setting->get_option( 'skip_ids', 'cbxwpbookmark_basics', '' );
		$skip_roles      = $setting->get_option( 'skip_roles', 'cbxwpbookmark_basics', '' );
		$showcount       = intval( $setting->get_option( 'showcount', 'cbxwpbookmark_basics', 0 ) );

		if ( $position == 'disable' ) {
			return;
		}

		//if bookmark allowed for post types
		if ( ! in_array( $post_type, $post_types_to_show_bookmark ) ) {
			return;
		}

		//if automation allowed for post types
		if ( ! in_array( $post_type, $post_types_automation ) ) {
			return;
		}

		//grab bookmark button html
		if ( is_array( $skip_roles ) ) {
			$skip_roles = implode( ',', $skip_roles );
		}


		$auto_integration_ok = true;

		$bookmark_html = apply_filters( 'cbxwpbookmark_auto_integration', $auto_integration_ok, $post_id, $post_type, $showcount, $skip_ids, $skip_roles ) ? show_cbxbookmark_btn( $post_id, $post_type, $showcount, '', $skip_ids, $skip_roles ) : '';


		//attach the bookmark button html before or after the content
		if ( $position == 'after_content' && $pos  == 'after' ) {
			echo  $bookmark_html;
		} elseif ( $position == 'before_content' && $pos == 'before' ) {
			echo $bookmark_html;
		}
	}//end  bbp_template_single_forum_automation



	/**
	 * bbpress topic : display bookmark automatically
	 *
	 *
	 * @return mixed|string
	 */
	public function bbp_template_single_topic_automation($pos = 'before'){
		$content = '';
		$post_id = bbp_get_topic_id();


		$post_type = 'topic';

		$setting = $this->settings_api;

		$post_types_to_show_bookmark = $setting->get_option( 'cbxbookmarkposttypes', 'cbxwpbookmark_basics', array(
			'post',
			'page'
		) );
		if(!is_array($post_types_to_show_bookmark)) $post_types_to_show_bookmark = array();

		$post_types_automation = $setting->get_option( 'post_types_automation', 'cbxwpbookmark_basics', array());
		if(!is_array($post_types_automation)) $post_types_automation = array();


		$position        = $setting->get_option( 'cbxbookmarkpostion', 'cbxwpbookmark_basics', 'after_content' );
		$skip_ids        = $setting->get_option( 'skip_ids', 'cbxwpbookmark_basics', '' );
		$skip_roles      = $setting->get_option( 'skip_roles', 'cbxwpbookmark_basics', '' );
		$showcount       = intval( $setting->get_option( 'showcount', 'cbxwpbookmark_basics', 0 ) );

		if ( $position == 'disable' ) {
			return;
		}

		//if bookmark allowed for post types
		if ( ! in_array( $post_type, $post_types_to_show_bookmark ) ) {
			return $content;
		}

		//if automation allowed for post types
		if ( ! in_array( $post_type, $post_types_automation ) ) {
			return $content;
		}

		//grab bookmark button html
		if ( is_array( $skip_roles ) ) {
			$skip_roles = implode( ',', $skip_roles );
		}


		$auto_integration_ok = true;

		$bookmark_html = apply_filters( 'cbxwpbookmark_auto_integration', $auto_integration_ok, $post_id, $post_type, $showcount, $skip_ids, $skip_roles ) ? show_cbxbookmark_btn( $post_id, $post_type, $showcount, '', $skip_ids, $skip_roles ) : '';


		//attach the bookmark button html before or after the content
		if ( $position == 'after_content' && $pos  == 'after' ) {
			echo  $content . $bookmark_html;
		} elseif ( $position == 'before_content' && $pos == 'before' ) {
			echo $bookmark_html . $content;
		}
	}//end bbp_template_single_topic_automation





}//end class CBXWPbookmark_Public