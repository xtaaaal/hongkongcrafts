<?php

/**
 * Fired during plugin uninstall
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      1.0.0
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Uninstall {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {

		global $wpdb;
		$settings = new CBXWPBookmark_Settings_API( CBXWPBOOKMARK_PLUGIN_NAME, CBXWPBOOKMARK_PLUGIN_VERSION );

		$delete_global_config = $settings->get_option( 'delete_global_config', 'cbxwpbookmark_tools', 'no' );

		if ( $delete_global_config == 'yes' ) {
			$option_prefix = 'cbxwpbookmark_';

			//delete plugin global options


			$option_values = CBXWPBookmarkHelper::getAllOptionNames();

			foreach ( $option_values as $key => $option_value ) {
				delete_option( $option_value['option_name'] );
			}

			do_action( 'cbxwpbookmark_plugin_option_delete' );

			//delete tables created by this plugin

			$table_names  = CBXWPBookmarkHelper::getAllDBTablesList();
			$sql          = "DROP TABLE IF EXISTS " . implode( ', ', array_values( $table_names ) );
			$query_result = $wpdb->query( $sql );

			do_action( 'cbxwpbookmark_plugin_table_delete' );


			do_action( 'cbxwpbookmark_plugin_uninstall', $table_names, $option_prefix );

		}
	}

}
