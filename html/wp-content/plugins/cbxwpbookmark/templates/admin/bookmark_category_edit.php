<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<?php

global $wpdb;
$log_id = ( isset( $_GET['id'] ) && intval( $_GET['id'] ) > 0 ) ? intval( $_GET['id'] ) : 0;

$setting = new CBXWPBookmark_Settings_API();
?>
<div class="wrap">
    <h2>
		<?php echo sprintf( esc_html__( 'Category ID: %d', 'cbxwpbookmark' ), $log_id ); ?>
    </h2>
    <p><?php echo '<a class="button button-primary button-large" href="' . admin_url( 'admin.php?page=cbxwpbookmarkcats' ) . '">' . esc_html__( 'Back to Category Lists', 'cbxwpbookmark' ) . '</a>'; ?></p>
	<?php
	$cat_addedit_error = get_transient( 'cbxwpbookmark_cat_addedit_error' );
	if ( $cat_addedit_error ) {
		$validation = $cat_addedit_error;
		delete_transient( 'cbxwpbookmark_cat_addedit_error' );

		$error_class = ( isset( $validation['error'] ) && intval( $validation['error'] ) == 1 ) ? 'notice notice-error' : 'notice notice-success';

		if ( isset( $validation['msg'] ) ) {
			echo '<div class="' . esc_attr( $error_class ) . '"><p>' . $validation['msg'] . '</p></div>';
		}
	}


	?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">

            <!-- main content -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h3><span><?php esc_html_e( 'Edit Category', 'cbxwpbookmark' ); ?></span></h3>
                        <div class="inside">
							<?php
							$category_info = null;
							$privacy       = 1;
							$cat_name      = '';

							if ( $log_id > 0 ) {
								$category_info = CBXWPBookmarkHelper::singleCategory( $log_id );

								if ( ! is_null( $category_info ) ) {

									$privacy  = isset( $category_info['privacy'] ) ? intval( $category_info['privacy'] ) : 1;
									$cat_name = isset( $category_info['cat_name'] ) ? stripslashes( $category_info['cat_name'] ) : '';
									$user_id  = isset( $category_info['user_id'] ) ? intval( $category_info['user_id'] ) : 0;


									$created_date = '';
									if ( $category_info['created_date'] != '0000-00-00 00:00:00' ) {
										$created_date = CBXWPBookmarkHelper::dateReadableFormat( stripslashes( $category_info['created_date'] ) );
									}

									$date_modified = '';
									if ( $category_info['modyfied_date'] != '0000-00-00 00:00:00' ) {
										$date_modified = CBXWPBookmarkHelper::dateReadableFormat( stripslashes( $category_info['modyfied_date'] ) );
									}

								}//end review information
							}
							?>
                            <div class="cbxwpbookmarkadminwrap" id="cbxwpbookmarkadminwrap">
                                <div class="cbxwpbookmark-form-section">
                                    <div class="cbxwpbookmark_global_msg"></div>
									<?php
									do_action( 'cbxwpbookmark_category_admineditform_before', $log_id, $category_info );
									?>
                                    <form class="cbxwpbookmark-form" method="post" enctype="multipart/form-data"
                                          data-busy="0"
                                          action="<?php echo admin_url( 'admin.php?page=cbxwpbookmarkcats&view=edit' ); ?>">
                                        <table class="widefat">
                                            <tbody>
											<?php if ( $log_id > 0 ) : ?>
                                                <tr>
                                                    <td class="row-title">
                                                        <label for="tablecell"><?php esc_html_e( 'Created By', 'cbxwpbookmark' ); ?></label>
                                                    </td>
                                                    <td>
                                                        <a target="_blank"
                                                           href="<?php echo esc_url( get_edit_user_link( intval( $user_id ) ) ) ?>"><?php echo $user_id; ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr class="alternate">
                                                    <td class="row-title">
                                                        <label for="tablecell"><?php esc_html_e( 'Created', 'cbxwpbookmark' ); ?></label>
                                                    </td>
                                                    <td>
														<?php echo esc_html( $created_date ); ?>
                                                    </td>
                                                </tr>

                                                <tr class="alternate">
                                                    <td class="row-title">
                                                        <label for="tablecell"><?php esc_html_e( 'Updated', 'cbxwpbookmark' ); ?></label>
                                                    </td>
                                                    <td>
														<?php echo esc_html( $date_modified ); ?>
                                                    </td>
                                                </tr>
											<?php endif; ?>
                                            </tbody>
                                        </table>
                                        <br/>
										<?php
										do_action( 'cbxwpbookmark_category_admineditform_start', $log_id, $category_info );
										?>


                                        <div class="cbxwpbookmark-form-field">
                                            <label for="cbxwpbookmark_cat_name"
                                                   class=""><?php esc_html_e( 'Category Name', 'cbxwpbookmark' ); ?></label><br/>
                                            <input type="text" name="cbxwpbookmark_form[cat_name]"
                                                   id="cbxwpbookmark_cat_name"
                                                   class="regular-text cbxwpbookmark-form-field-input cbxwpbookmark-form-field-input-text cbxwpbookmark_cat_name"
                                                   required
                                                   placeholder="<?php esc_html_e( 'Category Name', 'cbxwpbookmark' ); ?>"
                                                   value="<?php echo esc_attr( wp_unslash( $cat_name ) ); ?>"/>
                                        </div>
                                        <div class="cbxwpbookmark-form-field">
                                            <label for="cbxwpbookmark_privacy"><?php esc_html_e( 'Status', 'cbxwpbookmark' ); ?></label><br/>
                                            <select name="cbxwpbookmark_form[privacy]" id="cbxwpbookmark_privacy">
                                                <option <?php selected( 1, $privacy, true ); ?>
                                                        value="1"><?php esc_html_e( 'Public', 'cbxwpbookmark' ); ?></option>
                                                <option <?php selected( 0, $privacy, true ); ?>
                                                        value="0"><?php esc_html_e( 'Private', 'cbxwpbookmark' ); ?></option>
                                            </select>

                                        </div>

										<?php
										do_action( 'cbxwpbookmark_category_admineditform_end', $log_id, $category_info );
										?>
                                        <input type="hidden" name="cbxwpbookmark_cat_addedit" value="1"/> <input
                                                type="hidden" name="cbxwpbookmark_form[ajax]" value="0"/>
										<?php wp_nonce_field( 'cbxwpbookmark_cat_addedit', 'cbxwpbookmark_cat_nonce' ); ?>

                                        <input type="hidden" id="cbxwpbookmark_id" name="cbxwpbookmark_form[id]"
                                               value="<?php echo $log_id; ?>"/>
                                        <p class="label-cbxwpbookmark-submit-processing"
                                           style="display: none;"><?php esc_html_e( 'Please wait, do not close this window.', 'cbxwpbookmark' ) ?></p>
                                        <br/>
                                        <button type="submit"
                                                class="btn btn-primary button button-primary btn-cbxwpbookmark-submit"><?php echo ( $log_id > 0 ) ? esc_html__( 'Submit Edit', 'cbxwpbookmark' ) : esc_html__( 'Submit Create', 'cbxwpbookmark' ); ?></button>

                                    </form>
									<?php
									do_action( 'cbxwpbookmark_category_admineditform_after', $log_id, $category_info );
									?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear clearfix"></div>
    </div>
</div>