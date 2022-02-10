<?php

/**
 * Fired during plugin activation
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		CBXWPBookmarkHelper::create_tables();

		set_transient( 'cbxwpbookmark_activated_notice', 1 );
	}//end activate

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 */
	public static function cbxbookmark_create_pages() {
		$pages = apply_filters( 'cbxwpbookmark_create_pages',
			array(
				'mybookmark_pageid' => array(
					//'slug'    => _x( 'cbxbookmark', 'Page slug', 'cbxwpbookmark' ),
					'slug'    => _x( 'mybookmarks', 'Page slug', 'cbxwpbookmark' ),
					'title'   => _x( 'My Bookmarks', 'Page title', 'cbxwpbookmark' ),
					'content' => '[cbxwpbookmark-mycat][cbxwpbookmark]',
				),
			) );

		foreach ( $pages as $key => $page ) {
			CBXWPBookmark_Activator::cbxbookmark_create_page( $key, esc_sql( $page['slug'] ), $page['title'], $page['content'] );
		}
	}//end cbxbookmark_create_pages

	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param string $key
	 * @param string $slug
	 * @param string $page_title
	 * @param string $page_content
	 *
	 * @return int|string|WP_Error|null
	 */
	public static function cbxbookmark_create_page( $key = '', $slug = '', $page_title = '', $page_content = '' ) {
		global $wpdb;

		if ( $key == '' ) {
			return null;
		}
		if ( $slug == '' ) {
			return null;
		}

		//$settings_api = new CBXWPBookmark_Settings_API();

		$cbxwpbookmark_basics = get_option( 'cbxwpbookmark_basics' );

		$option_value = isset( $cbxwpbookmark_basics[ $key ] ) ? intval( $cbxwpbookmark_basics[ $key ] ) : 0;


		$page_id     = 0;
		$page_status = '';
		//if valid page id already exists
		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( is_object( $page_object ) ) {
				//at least found a valid post
				$page_id     = $page_object->ID;
				$page_status = $page_object->post_status;

				if ( 'page' === $page_object->post_type && $page_object->post_status == 'publish' ) {

					return $page_id;
				}
			}
		}


		$page_id = intval( $page_id );
		if ( $page_id > 0 ) {
			//page found
			if ( $page_status == 'trash' ) {
				//if trashed then untrash it, it will be published automatically
				wp_untrash_post( $page_id );
			} else {

				$page_data = array(
					'ID'          => $page_id,
					'post_status' => 'publish',
				);

				wp_update_post( $page_data );
			}

		} else {
			//search by slug for nontrashed and then trashed, then if not found create one

			if ( ( $page_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status != 'trash' AND post_name = %s LIMIT 1;", $slug ) ) ) ) > 0 ) {

				//non trashed post found by slug
				//page found but not publish, so publish it
				//$page_id   = $page_found_by_slug;
				$page_data = array(
					'ID'          => $page_id,
					'post_status' => 'publish',
				);
				wp_update_post( $page_data );
			} else if ( ( $page_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug . '__trashed' ) ) ) ) > 0 ) {

				//trash post found and unstrash/publish it
				wp_untrash_post( $page_id );
			} else {
				$page_data = array(
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_title'     => $page_title,
					'post_name'      => $slug,
					'post_content'   => $page_content,
					'comment_status' => 'closed',
				);
				$page_id   = wp_insert_post( $page_data );
			}
		}

		//let's update the option
		$cbxwpbookmark_basics[ $key ] = $page_id;
		update_option( 'cbxwpbookmark_basics', $cbxwpbookmark_basics );

		return $page_id;
	}//end cbxbookmark_create_page

}//end class CBXWPBookmark_Activator
