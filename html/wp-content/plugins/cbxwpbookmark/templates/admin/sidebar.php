<div id="postbox-container-1" class="postbox-container">
    <div class="meta-box-sortables">
        <div class="postbox">
            <h3><?php esc_html_e( 'Help & Supports', 'cbxwpbookmark' ) ?></h3>
            <div class="inside">
				<?php
				$plugin_url = CBXWPBookmarkHelper::url_utmy( 'https://codeboxr.com/product/cbx-wordpress-bookmark/' );
				$doc_url    = CBXWPBookmarkHelper::url_utmy( 'https://codeboxr.com/documentation-for-cbx-bookmark-for-wordpress/' );
				?>
                <p><?php esc_html_e( 'Support', 'cbxwpbookmark' ); ?>: <a href="https://codeboxr.com/contact-us"
                                                                          target="_blank"><span
                                class="dashicons dashicons-external"></span> <?php esc_html_e( 'Contact Us', 'cbxwpbookmark' ); ?>
                    </a></p>
                <p><span class="dashicons dashicons-email"></span> <a
                            href="mailto:info@codeboxr.com">info@codeboxr.com</a></p>
                <p><span class="dashicons dashicons-info"></span> <a href="<?php echo esc_url( $plugin_url ); ?>"
                                                                     target="_blank">Plugin Details</a></p>
                <p><span class="dashicons dashicons-editor-help"></span> <a href="<?php echo esc_url( $doc_url ); ?>"
                                                                            target="_blank">Plugin Documentation</a></p>
                <p><span class="dashicons dashicons-star-half"></span> <a
                            href="https://wordpress.org/support/plugin/cbxwpbookmark/reviews/#new-post" target="_blank">Review
                        This Plugin</a></p>
            </div>
        </div>
        <div class="postbox">
            <h3><?php esc_html_e( 'CBX Bookmark Addons', 'cbxwpbookmark' ) ?></h3>
            <div class="inside">
                <div id="cbxbookmark_addon_wrap">
                    <div class="cbxbookmark_addon cbxbookmark_addon_proaddon">
                        <div class="addons-banner-block-item-icon">
                            <a href="https://codeboxr.com/product/cbx-wordpress-bookmark/?utm_source=plgsidebarinfo&utm_medium=plgsidebar&utm_campaign=wpfreemium"
                               target="_blank"> <img
                                        src="https://codeboxr.com/wp-content/uploads/productshots/445-profile.png"
                                        alt="CBX Bookmark for WordPress"/> </a>
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>
                                <a href="https://codeboxr.com/product/cbx-wordpress-bookmark/?utm_source=plgsidebarinfo&utm_medium=plgsidebar&utm_campaign=wpfreemium"
                                   target="_blank">CBX Bookmark Pro Addon</a></h3>
                            <p>Pro features for CBX Bookmark plugin.</p>
                        </div>
                    </div>
                    <div class="cbxbookmark_addon cbxbookmark_addon_mycred">
                        <div class="addons-banner-block-item-icon">
                            <a href="https://codeboxr.com/product/cbx-bookmark-mycred-addon/?utm_source=plgsidebarinfo&utm_medium=plgsidebar&utm_campaign=wpfreemium"
                               target="_blank"> <img
                                        src="https://codeboxr.com/wp-content/uploads/productshots/11792-profile.png"
                                        alt="CBX Bookmark myCred Addon"/> </a>
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>
                                <a href="https://codeboxr.com/product/cbx-bookmark-mycred-addon/?utm_source=plgsidebarinfo&utm_medium=plgsidebar&utm_campaign=wpfreemium"
                                   target="_blank">CBX Bookmark myCred Addon</a></h3>
                            <p>This plugin integrates CBX Bookmark plugin with myCred plugin. Users gets point by
                                bookmarking a post</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="postbox">
            <h3><?php esc_html_e( 'Other WordPress Plugins', 'cbxmcratingreview' ); ?></h3>
            <div class="inside">
				<?php

				include_once( ABSPATH . WPINC . '/feed.php' );
				if ( function_exists( 'fetch_feed' ) ) {
					//$feed = fetch_feed( 'https://codeboxr.com/feed?post_type=product' );
					$feed = fetch_feed( 'https://codeboxr.com/product_cat/wordpress/feed/' );
					if ( ! is_wp_error( $feed ) ) : $feed->init();
						$feed->set_output_encoding( 'UTF-8' ); // this is the encoding parameter, and can be left unchanged in almost every case
						$feed->handle_content_type(); // this double-checks the encoding type
						$feed->set_cache_duration( 21600 ); // 21,600 seconds is six hours
						$limit = $feed->get_item_quantity( 20 ); // fetches the 18 most recent RSS feed stories
						$items = $feed->get_items( 0, $limit ); // this sets the limit and array for parsing the feed

						$blocks = array_slice( $items, 0, 20 );

						echo '<ul>';

						foreach ( $blocks as $block ) {
							$url = $block->get_permalink();
							$url = CBXWPBookmarkHelper::url_utmy( $url );

							echo '<li style="clear:both;  margin-bottom:5px;"><a target="_blank" href="' . $url . '">';
							//echo '<img style="float: left; display: inline; width:70px; height:70px; margin-right:10px;" src="https://codeboxr.com/wp-content/uploads/productshots/'.$id.'-profile.png" alt="wpboxrplugins" />';
							echo '<strong>' . $block->get_title() . '</strong></a></li>';
						}//end foreach

						echo '</ul>';


					endif;
				}
				?>
            </div>
        </div>
        <div class="postbox">
            <h3><?php esc_html_e( 'Codeboxr News Updates', 'cbxmcratingreview' ) ?></h3>
            <div class="inside">
				<?php

				include_once( ABSPATH . WPINC . '/feed.php' );
				if ( function_exists( 'fetch_feed' ) ) {
					//$feed = fetch_feed( 'https://codeboxr.com/feed' );
					$feed = fetch_feed( 'https://codeboxr.com/feed?post_type=post' );
					// $feed = fetch_feed('http://feeds.feedburner.com/codeboxr'); // this is the external website's RSS feed URL
					if ( ! is_wp_error( $feed ) ) : $feed->init();
						$feed->set_output_encoding( 'UTF-8' ); // this is the encoding parameter, and can be left unchanged in almost every case
						$feed->handle_content_type(); // this double-checks the encoding type
						$feed->set_cache_duration( 21600 ); // 21,600 seconds is six hours
						$limit = $feed->get_item_quantity( 10 ); // fetches the 10 most recent RSS feed stories
						$items = $feed->get_items( 0, $limit ); // this sets the limit and array for parsing the feed

						$blocks = array_slice( $items, 0, 10 ); // Items zero through six will be displayed here
						echo '<ul>';
						foreach ( $blocks as $block ) {
							$url = $block->get_permalink();
							$url = CBXWPBookmarkHelper::url_utmy( $url );

							echo '<li><a target="_blank" href="' . $url . '">';
							echo '<strong>' . $block->get_title() . '</strong></a></li>';
						}//end foreach
						echo '</ul>';


					endif;
				}
				?>
            </div>
        </div>
    </div>
</div>