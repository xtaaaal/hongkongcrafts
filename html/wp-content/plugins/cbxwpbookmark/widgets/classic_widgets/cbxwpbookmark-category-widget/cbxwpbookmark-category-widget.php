<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * My Bookmark Category Widget class
 *
 * Class CBXWPBookmark_Category
 */
class CBXWPBookmark_Category extends WP_Widget {

	/**
	 *
	 *
	 * Unique identifier for your widget.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * widget file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $widget_slug = 'cbxwpbookmarkcategory'; //id


	/**
	 * Constructor
	 *
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		//add_action( 'init', array( $this, 'cbxwpbookmark_textdomain' ) );

		parent::__construct(
			$this->get_widget_slug(), esc_html__( 'CBX Bookmark Categories', 'cbxwpbookmark' ), array(
				'classname'   => 'cbxbookmark-category-list-wrap cbxbookmark-category-list-wrap-widget ' . $this->get_widget_slug() . '-class',
				'description' => esc_html__( 'This widget shows bookmark categories from a logged in user.', 'cbxwpbookmark' )
			)
		);
	}// end constructor

	/**
	 * Return the widget slug.
	 *
	 * @return    Plugin slug variable.
	 * @since    1.0.0
	 *
	 */
	public function get_widget_slug() {
		return $this->widget_slug;
	}//end get_widget_slug

	/* -------------------------------------------------- */
	/* Widget API Functions
	  /*-------------------------------------------------- */

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		// Check if there is a cached output
		//$cache = wp_cache_get($this->bookmark_slug(), 'widget');

		/*if (!is_array($cache))
			$cache = array();*/

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		/* if (isset($cache[$args['widget_id']]))
			 return print $cache[$args['widget_id']];*/

		// go on with your widget logic, put everything into a string and …

		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;



		$default_title = esc_html__( 'Bookmark Categories', 'cbxwpbookmark' );

		$instance['honorauthor'] = isset( $instance['honorauthor'] ) ? intval( $instance['honorauthor'] ) : 0;

		if ( is_author() && absint($instance['honorauthor']) ) {
			$curauth            = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
			$instance['userid'] = absint( $curauth->ID );
			$default_title      = $title =  esc_html__( 'Author\'s Bookmark Categories', 'cbxwpbookmark' );
		}
		else{
			$current_user_id = absint(get_current_user_id());
			$instance['userid'] = $current_user_id;

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? $default_title : $instance['title'], $instance, $this->id_base );
		}


		// Wrapping title
		if ( $title !== '' ) {
			$widget_string .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$widget_string .= $args['before_title'] . $args['after_title'];
		}

		wp_enqueue_style( 'cbxwpbookmarkpublic-css' );

		// Checking if the user is logged in

		$settings_api  = new CBXWPBookmark_Settings_API();
		$bookmark_mode = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$instance['title'] = ''; // we will send our shortcode's title attribute blank so that in widget it doesn't show extra title



		//$widget_string .= '<div class="cbxbookmark-category-list-wrap cbxbookmark-category-list-wrap-widget">';

		if ( $bookmark_mode != 'no_cat' ) {
			/*$widget_string .= cbxwpbookmark_get_template_html( 'widgets/cbxwpbookmark-category-widget.php', array(
				'bookmark_mode' => $bookmark_mode,
				'instance'      => $instance
			) );*/

			$attr = array();
			$attr['title']          = $instance['title'];
			$attr['order']          = sanitize_text_field( $instance['order'] );
			$attr['orderby']        = sanitize_text_field( $instance['orderby'] );
			$attr['privacy']        = absint( $instance['privacy'] );
			$attr['display']        = absint( $instance['display'] );
			$attr['show_count']     = absint( $instance['show_count'] );
			$attr['show_bookmarks'] = absint( $instance['show_bookmarks'] );
			$attr['allowedit']      = absint( $instance['allowedit'] );

			$attr = apply_filters( 'cbxwpbookmark_widget_shortcode_builder_attr', $attr, $instance, 'cbxwpbookmark-mycat' );

			$attr_html = '';

			foreach ( $attr as $key => $value ) {
				$attr_html .= ' ' . $key . '="' . $value . '" ';
			}

			$widget_string .= do_shortcode( '[cbxwpbookmark-mycat ' . $attr_html . ']' );

		} else {
			$widget_string .= __( '<strong>Sorry, This widget is not compatible as per setting. This widget can be used only if bookmark mode is "User owns category"</strong>', 'cbxwpbookmark' );
		}

		//$widget_string .= '</div>';


		$widget_string .= $after_widget;

		//$cache[$args['widget_id']] = $widget_string;
		//wp_cache_set($this->cbxwpbookmarkcategory_slug, $cache, 'widget');

		print $widget_string;
	}// end widget

	/*public function flush_widget_cache() {
		wp_cache_delete($this->cbxwpbookmarkcategory_slug, 'widget');
	}*/

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = esc_attr( $new_instance['title'] );
		$instance['order']          = esc_attr( $new_instance['order'] );
		$instance['orderby']        = esc_attr( $new_instance['orderby'] );
		$instance['privacy']        = intval( $new_instance['privacy'] );
		$instance['display']        = intval( $new_instance['display'] );
		$instance['show_count']     = intval( $new_instance['show_count'] );
		$instance['show_bookmarks'] = intval( $new_instance['show_bookmarks'] );
		$instance['allowedit']      = intval( $new_instance['allowedit'] );

		//for author archive url only
		$instance['honorauthor']    = intval( $new_instance['honorauthor'] );

		return $instance;
	}//end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'          => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
				'order'          => 'ASC',
				'orderby'        => 'cat_name',
				'privacy'        => 2, // all  = 2, 0 = private 1 = public
				'display'        => 0,  //0 = list, 1 = dropdown
				'show_count'     => 0,
				'show_bookmarks' => 0,  // 0 = don't  1 = shows bookmark as sublist item
				'allowedit'      => 0,  // 0 = don't  1 = yes,  allow edit and delete
				'honorauthor'    => 0
			)
		);


		$title          = wp_strip_all_tags( $instance['title'] );
		$order          = esc_attr( $instance['order'] );
		$orderby        = esc_attr( $instance['orderby'] );
		$privacy        = intval( $instance['privacy'] );
		$display        = intval( $instance['display'] );
		$show_count     = intval( $instance['show_count'] );
		$show_bookmarks = intval( $instance['show_bookmarks'] );
		$allowedit      = intval( $instance['allowedit'] );
		$honorauthor    = intval( $instance['honorauthor'] );


		// Display the admin form
		include( plugin_dir_path( __FILE__ ) . 'views/admin.php' );
	}//end form
}//end class CBXWPBookmark_Category