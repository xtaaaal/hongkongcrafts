<?php
/**
 * Recommended plugins
 *
 * @package minimal-grid
 */
if ( ! function_exists( 'minimal_grid_recommended_plugins' ) ) :
	/**
	 * Recommend plugins.
	 *
	 * @since 1.0.0
	 */
	function minimal_grid_recommended_plugins() {
		$plugins = array(
			array(
                'name'     => __( 'Rankchecker.io Integration', 'minimal-grid' ),
                'slug'     => 'rankchecker-io-integration',
                'required' => false,
            ),
		);
		tgmpa( $plugins );
	}
endif;
add_action( 'tgmpa_register', 'minimal_grid_recommended_plugins' );
