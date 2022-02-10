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
$bookmark_category = new CBXWPBookmark_Category_Table(array( 'screen' => get_current_screen()->id ));

//Fetch, prepare, sort, and filter CBXSCRatingReviewLog data
$bookmark_category->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
		<?php esc_html_e( 'CBX WP Bookmark: Bookmark Category Manager', 'cbxwpbookmark' ); ?>
    </h1>
    <p><?php echo '<a class="button button-primary button-large" href="' . admin_url( 'admin.php?page=cbxwpbookmarkcats&view=edit&id=0' ) . '">' . esc_html__( 'Create New Category', 'cbxwpbookmark' ) . '</a>'; ?></p>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <!-- main content -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="inside">
                            <form id="cbxwpbookmark_bookmark_category" method="post">
								<?php $bookmark_category->views(); ?>

                                <input type="hidden" name="page" value="<?php echo esc_url($_REQUEST['page']); ?>"/>
								<?php $bookmark_category->search_box( esc_html__( 'Search Bookmark', 'cbxwpbookmark' ), 'cbxwpbookmarkcategory' ); ?>

								<?php $bookmark_category->display() ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear clearfix"></div>
    </div>
</div>