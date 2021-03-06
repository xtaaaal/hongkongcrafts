<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Minimal_Grid
 */
?>
</div>
<?php
if( is_front_page() ) {
    /**
     * Hook - minimal_grid_home_section.
     */
    do_action('minimal_grid_home_footer_section');
} ?>

<footer id="colophon" class="site-footer">
    <?php if (is_active_sidebar('footer-col-one') || is_active_sidebar('footer-col-two') || is_active_sidebar('footer-col-three')): ?>
    <div class="footer-widget-area">
        <div class="row row-collapse">
            <?php if (is_active_sidebar('footer-col-one')) : ?>
            <div class="col-md-4">
                <?php dynamic_sidebar('footer-col-one'); ?>
            </div>
            <?php endif; ?>
            <?php if (is_active_sidebar('footer-col-two')) : ?>
            <div class="col-md-4">
                <?php dynamic_sidebar('footer-col-two'); ?>
            </div>
            <?php endif; ?>
            <?php if (is_active_sidebar('footer-col-three')) : ?>
            <div class="col-md-4">
                <?php dynamic_sidebar('footer-col-three'); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $copyright_text = minimal_grid_get_option('copyright_text', true);
    if ($copyright_text):
    ?>
    <div class="site-copyright">
        <span><?php echo wp_kses_post($copyright_text);?></span>

    </div>
    <?php endif;?>
</footer>
</div>
</div>

<?php if ( class_exists( 'WooCommerce' ) ): ?>
<section class="minicart-section">
    <?php minimal_grid_woocommerce_header_cart(); ?>
</section>
<?php endif; ?>

<a id="scroll-up" class="secondary-background"><i class="ion-ios-arrow-up"></i></a>
<?php wp_footer(); ?>

</body>

</html>