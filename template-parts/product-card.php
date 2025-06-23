<?php
// Ensure $args is defined and is an array
if (!isset($args) || !is_array($args)) {
    $args = [];
}

$product_id = isset($args['product']) ? $args['product'] : null;
$style = isset($args['style']) ? $args['style'] : 'style-1';

$product = wc_get_product($product_id);
if (!$product) return;

// Common product data
$product_name = $product->get_name();
$product_price = $product->get_price_html();
$product_link = $product->get_permalink();
$product_image = $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover object-center']);
$product_short_description = $product->get_short_description();

if ($style === 'style-1') : ?>
    <div class="h-auto group z-0 hover:z-10 product-card product-card-style-1">
        <div class="relative h-full">
            <div class="relative z-[1] pb-4">
                <!-- THUMBNAIL AREA -->
                <a href="<?php echo esc_url($product_link); ?>" class="block border rounded-lg overflow-hidden aspect-square mb-4"><?php echo $product_image; ?></a>
                <!-- CONTENT AREA -->
                <a href="<?php echo esc_url($product_link); ?>">
                    <h3 class="text-base line-clamp-2 text-dark mb-3 leading-[1.2] transition-all duration-300 hover:text-primary font-semibold"><?php echo esc_html($product_name); ?></h3>
                </a>
                <div class="price mb-4 [&_del>.amount]:text-gray-500 [&_del>.amount]:text-lg [&_del]:no-underline [&_del>.amount]:line-through [&_del>.amount]:mr-1 [&_ins>.amount]:text-primary [&_ins>.amount]:font-bold [&_ins>.amount]:text-xl"><?php echo $product_price; ?></div>
                <!-- IS STOCK -->
                <?php if ($product->is_in_stock()) : ?>
                    <div class="is-stock flex items-center gap-x-2 text-green-600">
                        <span class="text-sm"><?php echo get_icon('icon-sandbox'); ?></span>
                        <span class="text-sm font-normal"><?php echo esc_html__('In Stock', 'shop-theme'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <!-- ACTIONS -->
            <div class="flex flex-col gap-4 py-4 absolute top-full start-0 end-0 opacity-0 group-hover:opacity-100 h-[190px] before:content-[''] before:absolute before:w-[calc(100%+3rem)] before:h-[1px] before:bg-gray-200 before:top-0 before:-start-[1.5rem]">
                <div>
                    <!-- ADD PRODUCT SHORT DESCRIPTION -->
                    <?php if (!empty($product_short_description)) : ?>
                        <div class="text-sm text-gray-500 mb-2"><?php echo _e($product_short_description); ?></div>
                    <?php endif; ?>
                </div>
                <div class="mt-auto">
                    <a href="<?php echo esc_url($product_link); ?>" class="btn btn-primary rounded-full w-full"><?php echo esc_html__('View Product', 'shop-theme'); ?></a>
                </div>
            </div>
            <!-- SHADOW -->
            <div class="absolute shadow-[0_25px_50px_-12px_rgba(33,37,41,0.25)] border rounded-lg opacity-0 group-hover:opacity-100 w-[calc(100%+3rem)] h-[calc(100%+3rem+190px)] z-[-1] bg-white -top-[1.5rem] -start-[1.5rem]"></div>
        </div>
    </div>
<?php elseif ($style === 'style-2') : ?>
    <div class="relative z-[1] pb-4">
        <!-- THUMBNAIL AREA -->
        <a href="<?php echo esc_url($product_link); ?>" class="block border rounded-lg overflow-hidden aspect-square mb-4"><?php echo $product_image; ?></a>
        <!-- CONTENT AREA -->
        <a href="<?php echo esc_url($product_link); ?>">
            <h3 class="text-base line-clamp-2 text-dark mb-3 leading-[1.2] transition-all duration-300 hover:text-primary font-semibold"><?php echo esc_html($product_name); ?></h3>
        </a>
        <div class="price mb-4"><?php echo $product_price; ?></div>
        <!-- IS STOCK -->
        <?php if ($product->is_in_stock()) : ?>
            <div class="is-stock flex items-center gap-x-2 text-green-600">
                <span class="text-sm"><?php echo get_icon('icon-sandbox'); ?></span>
                <span class="text-sm font-normal"><?php echo esc_html__('In Stock', 'shop-theme'); ?></span>
            </div>
        <?php endif; ?>
        <!-- product progress -->
        <div class="product-progress space-y-2 mt-4">
            <?php
            $stock_quantity = $product->get_stock_quantity();
            $total_sold = $product->get_total_sales();
            $sold_percentage = ($stock_quantity + $total_sold) > 0 ? ($total_sold / ($stock_quantity + $total_sold)) * 100 : 0;
            ?>
            <div class="w-full h-2 flex justify-end bg-[linear-gradient(to_right,#F03E3E,#F76028,#FFD43B,#12B886)] rounded overflow-hidden">
                <div class="block h-full bg-gray-200" style="width: <?php echo esc_attr(max(2, $sold_percentage)); ?>%"></div>
            </div>
            <div class="flex items-center gap-x-2 justify-between">
                <span class="text-sm"><?php echo esc_html__('Available:', 'shop-theme'); ?> <span class="font-bold"><?php echo esc_html($stock_quantity); ?></span></span>
                <span class="text-sm"><?php echo esc_html__('Sold:', 'shop-theme'); ?> <span class="font-bold"><?php echo esc_html($total_sold); ?></span></span>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="px-3 mb-4"><?php echo esc_html__('Please Select a Valid Style', 'shop-theme'); ?></div>
<?php endif; ?>