<!-- Top Bar -->
<div class="w-full bg-[linear-gradient(-45deg,_#ed1c66,_#6a23d5,_#23a6d5,_#23d5ab,_#f03e3e,_#e8890c,_#e73c7e)] [background-size:400%_400%] animate-[gradient_15s_ease_infinite] text-white border-b border-gray-200 text-sm">
    <div class="container mx-auto flex justify-between items-center py-2 px-4">
        <div class="flex space-x-4">
            <a href="#" class="text-white hover:text-white"><?php echo esc_html__('Order Tracking', 'shop-theme'); ?></a>
            <a href="#" class="text-white hover:text-white"><?php echo esc_html__('Wishlist', 'shop-theme'); ?></a>
        </div>
        <div class="flex space-x-4 items-center">
            <span class="hidden md:inline">
                <?php printf(
                    esc_html__('Need help? Call us: %1$s or %2$s', 'shop-theme'),
                    '<a href="tel:+8001234567890" class="text-white font-semibold">(+800) 1234 5678 90</a>',
                    '<a href="mailto:info@company.com" class="text-white font-semibold">info@company.com</a>'
                ); ?>
            </span>
            <a href="#" class="text-white hover:text-white"><?php echo esc_html__('BDT', 'shop-theme'); ?></a>
        </div>
    </div>
</div>