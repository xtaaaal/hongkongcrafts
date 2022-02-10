<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/admin/partials
 */
?>
<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <div class="cbx-backend_container_header">
        <div class="cbx-backend_wrapper cbx-backend_header_wrapper">
            <div class="menu-heading">
                <img title="CBX WP Bookmark - Setting"
                     src="<?php echo CBXWPBOOKMARK_ROOT_URL . 'assets/img/icon_log.svg'
				     ?>" alt="CBX WP Bookmark - Setting" width="32" height="32">
                <h2 class="wp-heading-inline wp-heading-inline-setting">
					<?php esc_html_e( 'CBX WP Bookmark - Setting', 'cbxwpbookmark' ); ?>
                </h2>
            </div>
            <div class="setting_tool">
                <a href="#" id="save_settings" class="button button-primary"><img
                            src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABmJLR0QA/wD/AP+gvaeTAAAA+0lEQVRIie2UMUpEMRRFT9RGCyO4BBXcgli5AHEBLkF0C84ipnJaWwttBEGsXYJ7sHBmA8dC//An5OdP5Iugni4vyb03vPDgnxrULfVanaqjz1oXzf5YXe3SXEnWY+AU2KzIdQbcqOvLGBxXCLc5AR7V7T6DmuQpB8CzutcurhUuNGaXwEZm/0GNSW0HuAd2s4pJE1/VQzV9ZXM2qpNc99vnQmpQeNHShBDmutl0Q/KjBldADD0AEZh0iZR6EIF94Kgn5BPwArzNRVs9WCD9CeqoMCrSkZH9Rb+7yd9iMBtAc1oyuBvAYEEjHXbngHyM31pmwC1w8bVcf5Z3dIDGLQz4Au0AAAAASUVORK5CYII="/>
					<?php esc_html_e( 'Save Settings', 'cbxwpbookmark' ); ?></a>
                <a href="<?php echo admin_url() . 'admin.php?page=cbxwpbookmarkdash' ?>"
                   class="doc_image"><img title="Helps & Updates"
                                          src="<?php echo CBXWPBOOKMARK_ROOT_URL . 'assets/img/helps.svg'
				                          ?>" alt="" width="30" height="30"></a>
            </div>
        </div>
    </div>

    <div class="cbx-backend_container cbx-backend-settings-container">
        <div class="cbx-backend_wrapper cbx-backend_setting_wrapper">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <div class="inside setting-from-warp">
									<?php
									$settings_api->show_navigation();
									$settings_api->show_forms();
									?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>