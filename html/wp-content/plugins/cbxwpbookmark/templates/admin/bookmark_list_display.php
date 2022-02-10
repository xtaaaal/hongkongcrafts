<?php
/**
 * Provide a dashboard bookmark log listing
 *
 * This file is used to markup the admin-facing bookmark log listing
 *
 * @link       https://codeboxr.com
 * @since      1.0.7
 *
 * @package    cbxwpbookmark
 * @subpackage cbxwpbookmark/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<?php
$bookmark_list = new CBXWPBookmark_List_Table(array( 'screen' => get_current_screen()->id ) );

//Fetch, prepare, sort, and filter CBXSCRatingReviewLog data
$bookmark_list->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
		<?php esc_html_e( 'CBX WP Bookmark: Bookmark Manager', 'cbxwpbookmark' ); ?>
    </h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <!-- main content -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="inside">
                            <form id="cbxwpbookmark_bookmark_list" method="post">
								<?php $bookmark_list->views(); ?>

                                <input type="hidden" name="page" value="<?php echo esc_url($_REQUEST['page']) ?>"/>
								<?php //$bookmark_list->search_box( esc_html__( 'Search Bookmark', 'cbxwpbookmark' ), 'cbxwpbookmarklist' ); ?>

								<?php $bookmark_list->display() ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear clearfix"></div>
    </div>
</div>