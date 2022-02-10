<?php

/**
 * The file that defines the cutom fucntions of the plugin
 *
 *
 *
 * @link       codeboxr.com
 * @since      1.4.6
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

if ( ! function_exists( 'cbxwpbookmark_object_types' ) ) {

	/**
	 * Return post types list, if plain is true then send as plain array , else array as post type groups
	 *
	 * @param bool|false $plain
	 *
	 * @return array
	 */
	function cbxwpbookmark_object_types( $plain = false ) {
		return CBXWPBookmarkHelper::post_types( $plain );
	}//end cbxwpbookmark_object_types
}


if ( ! function_exists( 'show_cbxbookmark_btn' ) ):

	/**
	 * Returns bookmark button html markup
	 *
	 * @param int $object_id post id
	 * @param null $object_type post type
	 * @param int $show_count if show bookmark counts
	 * @param string $extra_wrap_class style css class
	 * @param string $skip_ids post ids to skip
	 * @param string $skip_roles user roles
	 *
	 * @return string
	 */
	function show_cbxbookmark_btn( $object_id = 0, $object_type = null, $show_count = 1, $extra_wrap_class = '', $skip_ids = '', $skip_roles = '' ) {
		return CBXWPBookmarkHelper::show_cbxbookmark_btn( $object_id, $object_type, $show_count, $extra_wrap_class, $skip_ids, $skip_roles );
	}
endif;


if ( ! function_exists( 'cbxbookmark_post_html' ) ) {
	/**
	 * Returns bookmarks as per $instance attribues
	 *
	 * @param array $instance
	 * @param bool $echo
	 *
	 * @return false|string
	 */
	function cbxbookmark_post_html( $instance, $echo = false ) {
		$output = CBXWPBookmarkHelper::cbxbookmark_post_html( $instance );

		if ( $echo ) {
			echo '<ul class="cbxwpbookmark-list-generic cbxwpbookmark-mylist">' . $output . '</ul>';
		} else {
			return $output;
		}
	}//end function cbxbookmark_post_html
}


if ( ! function_exists( 'cbxbookmark_mycat_html' ) ) {

	/**
	 * Return users bookmark categories
	 *
	 * @param array $instance
	 * @param bool $echo
	 *
	 * @return false|string
	 */
	function cbxbookmark_mycat_html( $instance, $echo = false ) {

		$settings_api  = new CBXWPBookmark_Settings_API();
		$bookmark_mode = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		if ( $bookmark_mode == 'user_cat' || $bookmark_mode == 'global_cat' ) {
			$output = CBXWPBookmarkHelper::cbxbookmark_mycat_html( $instance );
		} else {
			$output = '<li>' . __( '<strong>Sorry, User categories or global categories can not be shown if bookmark mode is not "No Category"', 'cbxwpbookmark' ) . '</strong></li>';
		}

		$create_category_html = CBXWPBookmarkHelper::create_category_html( $instance );

		if ( $echo ) {
			echo $create_category_html . '<ul class="cbxwpbookmark-list-generic cbxbookmark-category-list cbxbookmark-category-list-' . $bookmark_mode . '">' . $create_category_html . $output . '</ul>';
		} else {
			return $output;
		}
	}//end function cbxbookmark_mycat_html
}

if ( ! function_exists( 'cbxbookmark_most_html' ) ) {
	/**
	 * Returns most bookmarked posts
	 *
	 * @param array $instance
	 * @param array $attr
	 * @param bool $echo
	 *
	 * @return false|string
	 */
	function cbxbookmark_most_html( $instance, $attr = array(), $echo = false ) {
		$output = CBXWPBookmarkHelper::cbxbookmark_most_html( $instance, $attr );

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}//end cbxbookmark_most_html
}//end exists cbxbookmark_most_html


if ( ! function_exists( 'get_author_cbxwpbookmarks_url' ) ) {
	function get_author_cbxwpbookmarks_url( $author_id = 0 ) {
		return CBXWPBookmarkHelper::get_author_cbxwpbookmarks_url( $author_id );
	}

}//end exists get_author_cbxwpbookmarks_url

if ( ! function_exists( 'cbxwpbookmarks_mybookmark_page_url' ) ) {
	/**
	 * Get mybookmark page url
	 *
	 * @return false|string
	 */
	function cbxwpbookmarks_mybookmark_page_url() {
		return CBXWPBookmarkHelper::cbxwpbookmarks_mybookmark_page_url();
	}//end cbxwpbookmarks_mybookmark_page_url
}//end exists cbxwpbookmarks_mybookmark_page_url