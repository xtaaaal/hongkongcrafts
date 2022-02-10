<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$object_id   = $item->object_id;
$object_type = $item->object_type;

echo '<li class="cbxwpbookmark-mylist-item ' . $sub_item_class . '">';
do_action( 'cbxwpbookmark_bookmarkpost_single_item_start', $object_id, $item );
echo '<a href="' . get_permalink( $object_id ) . '">' . wp_strip_all_tags( get_the_title( $object_id ) ) . '</a>' . $action_html;
do_action( 'cbxwpbookmark_bookmarkpost_single_item_end', $object_id, $item );
echo '</li>';