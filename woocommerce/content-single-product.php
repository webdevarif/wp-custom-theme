<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>


<?php

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-8">

		<div class="lg:sticky lg:top-8 [&_.images.woocommerce-product-gallery]:float-none [&_.images.woocommerce-product-gallery]:!w-full [&_.images.woocommerce-product-gallery]:!max-w-none [&_.flex-viewport]:border [&_.flex-viewport]:border-[#dee2e6] [&_.flex-viewport]:rounded-[12px] [&_.flex-viewport]:overflow-hidden">
			<?php
			/**
			 * Hook: woocommerce_before_single_product_summary.
			 *
			 * @hooked woocommerce_show_product_sale_flash - 10
			 * @hooked woocommerce_show_product_images - 20
			 */
			do_action( 'woocommerce_before_single_product_summary' );
			?>
		</div>

		<div class="summary entry-summary single-product-summary mt-8 lg:mt-0 float-none !w-full">
			<?php
				/**
				 * Hook: woocommerce_single_product_summary.
				 *
				 * @hooked woocommerce_template_single_title - 5
				 * @hooked woocommerce_template_single_rating - 10
				 * @hooked woocommerce_template_single_price - 10
				 * @hooked woocommerce_template_single_excerpt - 20
				 * @hooked woocommerce_template_single_add_to_cart - 30
				 * @hooked woocommerce_template_single_meta - 40
				 * @hooked woocommerce_template_single_sharing - 50
				 * @hooked WC_Structured_Data::generate_product_data() - 60
				 */
				// do_action( 'woocommerce_single_product_summary' );
				
				// Custom product summary layout starts here
				woocommerce_template_single_title();
			?>

			<div class="flex items-center flex-wrap gap-x-4 gap-y-2 mb-2">
				<?php woocommerce_template_single_rating(); ?>
				
				<?php // SKU display removed to match new design ?>

				<?php
				if ( $product->is_in_stock() ) {
					$stock_icon = get_icon('icon-stock', 'inline-block');
					$stock_text = sprintf( esc_html__( '%s in stock', 'shop-theme' ), $product->get_stock_quantity() );
					printf(
						'<p class="stock in-stock inline-flex items-center px-3 py-2 rounded-md text-sm font-semibold bg-[#e6fcf5] !text-[#099268] [&_svg]:h-5 [&_svg]:w-5 [&_svg]:mr-1">%s<span class="ml-1">%s</span></p>',
						$stock_icon,
						$stock_text
					);
				} else {
					$availability = $product->get_availability();
					printf(
						'<p class="stock %s inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-red-100 text-red-700">%s</p>',
						esc_attr( $availability['class'] ),
						esc_html( $availability['availability'] )
					);
				}
				?>

				<div class="flex items-center gap-x-2">
					<div class="text-sm text-gray-500">
						<?php echo getThemeimPostViews(get_the_ID()); ?>
					</div>
				</div>
			</div>

			<?php
				woocommerce_template_single_price();
				woocommerce_template_single_excerpt();
				woocommerce_template_single_add_to_cart();
			?>
			
			<?php // Wishlist and favorites section is removed to match the new design ?>

			<div class="my-6 border-t border-gray-200"></div>

			<?php get_template_part('template-parts/snippet-whatsapp-order'); ?>

			<div class="my-8 border-t border-gray-200"></div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
				<?php
					$features = [
						[
							'icon' => 'icon-low-prices',
							'title' => esc_html__('Low Prices', 'shop-theme'),
							'description' => esc_html__('Price match guarantee', 'shop-theme'),
						],
						[
							'icon' => 'icon-guaranteed-fitment',
							'title' => esc_html__('Guaranteed Fitment', 'shop-theme'),
							'description' => esc_html__('Always the correct part', 'shop-theme'),
						],
						[
							'icon' => 'icon-in-house-experts',
							'title' => esc_html__('In-House Experts', 'shop-theme'),
							'description' => esc_html__('We know our products', 'shop-theme'),
						],
						[
							'icon' => 'icon-easy-returns',
							'title' => esc_html__('Easy Returns', 'shop-theme'),
							'description' => esc_html__('Quick & Hassle Free', 'shop-theme'),
						],
					];

					foreach ($features as $feature) {
						printf(
							'<div class="flex items-center space-x-3">
								<div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 text-gray-700 [&_svg]:h-6 [&_svg]:w-6">
									%s
								</div>
								<div>
									<p class="font-semibold text-gray-800">%s</p>
									<p class="text-gray-500">%s</p>
								</div>
							</div>',
							get_icon($feature['icon']),
							$feature['title'],
							$feature['description']
						);
					}
				?>
			</div>

			<div class="my-6 border-t border-gray-200"></div>

			<?php
				// Simplified category display and removed sharing buttons
				echo wc_get_product_category_list( $product->get_id(), ', ', '<div class="product_meta text-sm text-gray-600"><span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'shop-theme' ) . ' ', '</span></div>' );
			?>
		</div>

	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
