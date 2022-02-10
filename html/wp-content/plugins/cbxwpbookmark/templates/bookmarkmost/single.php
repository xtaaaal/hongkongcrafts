<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


$object_id   = $item->object_id;
$object_type = $item->object_type;

$show_thumb = isset( $instance['show_thumb'] ) ? intval( $instance['show_thumb'] ) : 1;
$thumb_size = 'thumbnail';
$thumb_attr = array();

echo '<li class="cbxwpbookmark-mostlist-item' . $li_class . '" >';

do_action( 'cbxwpbookmark_bookmarkmost_single_item_start', $object_id, $item );

echo '<a href="' . get_permalink( $object_id ) . '">';
$thumb_html = '';
if ( $show_thumb ) {
	if ( has_post_thumbnail( $object_id ) ) {
		$thumb_html = get_the_post_thumbnail( $object_id, $thumb_size, $thumb_attr );
	} elseif ( ( $parent_id = wp_get_post_parent_id( $object_id ) ) && has_post_thumbnail( $parent_id ) ) {
		$thumb_html = get_the_post_thumbnail( $parent_id, $thumb_size, $thumb_attr );
	}

	echo $thumb_html;
}
echo wp_strip_all_tags( get_the_title( $object_id ) ) . $show_count_html;
echo '</a>';
do_action( 'cbxwpbookmark_bookmarkmost_single_item_end', $object_id, $item );
echo '</li>';