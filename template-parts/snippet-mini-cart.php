<?php
$count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$subtotal = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_subtotal() : '';
?>
<a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="flex gap-x-3 items-center transition-all duration-300 hover:text-primary">
    <span class="relative inline-flex items-center">
        <span class="text-2xl lg:text-4xl"><?php echo get_icon('icon-basket-outline'); ?></span>
        <span class="absolute -top-1 -right-1 bg-primary text-xs rounded-full px-1 text-white"><?php echo esc_html($count); ?></span>
    </span>
    <div class="hidden lg:flex lg:flex-col gap-y-1">
        <span class="text-[75%] text-black/50 font-normal leading-[1]">
            <?php printf(esc_html__('%d item', 'shop-theme'), $count); ?><?php if ($count != 1) echo 's'; ?>
        </span>
        <span class="text-md leading-[1] font-semibold"><?php echo wp_kses_post($subtotal); ?></span>
    </div>
</a>