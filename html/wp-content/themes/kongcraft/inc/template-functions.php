<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Minimal_Grid
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function minimal_grid_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

    $site_layout = minimal_grid_get_option('site_layout',true);

	if( 'fullwidth' == $site_layout){
        $classes[] = 'thememattic-full-layout';
    }
    if( 'boxed' == $site_layout ){
        $classes[] = 'thememattic-boxed-layout';
    }
    if ( class_exists( 'wooCommerce' ) ) {
        if ( is_woocommerce()) {
            $classes[] = 'thememattic-no-shop-sidebar';
        }
    }


    $page_layout = minimal_grid_get_page_layout();
    $classes[] = esc_attr($page_layout);

	return $classes;
}
add_filter( 'body_class', 'minimal_grid_body_classes' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function minimal_grid_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'minimal_grid_pingback_header' );

/**
 * A get_post_gallery() polyfill for Gutenberg
 *
 * @param string $gallery The current gallery html that may have already been found (through shortcodes).
 * @param int $post The post id.
 * @return string The gallery html.
 */
function minimal_grid_get_post_gallery( $gallery, $post ) {
    // Already found a gallery so lets quit.
    if ( $gallery ) {
        return $gallery;
    }
    // Check the post exists.
    $post = get_post( $post );
    if ( ! $post ) {
        return $gallery;
    }
    // Not using Gutenberg so let's quit.
    if ( ! function_exists( 'has_blocks' ) ) {
        return $gallery;
    }
    // Not using blocks so let's quit.
    if ( ! has_blocks( $post->post_content ) ) {
        return $gallery;
    }
    /**
     * Search for gallery blocks and then, if found, return the html from the
     * first gallery block.
     *
     * Thanks to Gabor for help with the regex:
     * https://twitter.com/javorszky/status/1043785500564381696.
     */
    $pattern = "/<!--\ wp:gallery.*-->([\s\S]*?)<!--\ \/wp:gallery -->/i";
    preg_match_all( $pattern, $post->post_content, $the_galleries );
    // Check a gallery was found and if so change the gallery html.
    if ( ! empty( $the_galleries[1] ) ) {
        $gallery = reset( $the_galleries[1] );
    }
    return $gallery;
}
add_filter( 'get_post_gallery', 'minimal_grid_get_post_gallery', 10, 2 );