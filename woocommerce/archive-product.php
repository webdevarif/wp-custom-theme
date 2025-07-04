<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<main id="primary" class="site-main site-archive-product">
<div class="container py-8">
    <?php
    /**
     * Hook: woocommerce_before_main_content.
     *
     * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
     * @hooked woocommerce_breadcrumb - 20
     * @hooked WC_Structured_Data::generate_website_data() - 30
     */
    do_action( 'woocommerce_before_main_content' );
    ?>

    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
        <div class="pb-4">
            <?php     
                get_template_part('template-parts/featured-card', null, [
                'banner' => 451,
                'heading' => get_the_archive_title(),
                'wrapperClass' => 'featured-card h-[275px]',
                'contentClass' => 'featured-card-content xl:p-10 xl:h-[275px] xl:justify-center xl:[&_.featured-card-heading]:text-4xl xl:[&_.featured-card-heading]:max-w-[600px] xl:w-full'
            ]); ?>
        </div>
    <?php endif; ?>

    <?php
    /**
     * Hook: woocommerce_archive_description.
     *
     * @hooked woocommerce_taxonomy_archive_description - 10
     * @hooked woocommerce_product_archive_description - 10
     */
    do_action( 'woocommerce_archive_description' );
    ?>

    <?php if ( woocommerce_product_loop() ) : ?>
        <?php
        /**
         * Hook: woocommerce_before_shop_loop.
         *
         * @hooked woocommerce_output_all_notices - 10
         * @hooked woocommerce_result_count - 20
         * @hooked woocommerce_catalog_ordering - 30
         */
        do_action( 'woocommerce_before_shop_loop' );
        ?>

        <?php woocommerce_product_loop_start(); ?>

        <?php if ( wc_get_loop_prop( 'total' ) ) : ?>
            <?php while ( have_posts() ) : ?>
                <?php the_post(); ?>
                
                <?php
                /**
                 * Hook: woocommerce_shop_loop.
                 */
                do_action( 'woocommerce_shop_loop' );
                ?>
                
                <?php wc_get_template_part( 'content', 'product' ); ?>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php woocommerce_product_loop_end(); ?>

        <?php
        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action( 'woocommerce_after_shop_loop' );
        ?>
    <?php else : ?>
        <?php
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action( 'woocommerce_no_products_found' );
        ?>
    <?php endif; ?>

    <?php
    /**
     * Hook: woocommerce_after_main_content.
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     */
    do_action( 'woocommerce_after_main_content' );
    ?>
    </div>
</main>

<?php
/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );