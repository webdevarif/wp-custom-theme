<?php
if (!is_user_logged_in()) : ?>
<div class="relative group">
    <a href="#" class="flex flex-col items-start justify-center hover:text-primary transition-all duration-300 focus:outline-none">
        <span class="flex items-center font-semibold text-base">
            <?php echo esc_html__('My Account', 'shop-theme'); ?>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
        </span>
        <span class="text-xs text-gray-500 mt-0.5"><?php printf(esc_html__('Hello, %s', 'shop-theme'), '<span class="underline">' . esc_html__('Sign In', 'shop-theme') . '</span>'); ?></span>
    </a>
    <!-- Bridge to prevent accidental mouseout -->
    <div class="absolute left-1/2 -translate-x-1/2 top-full w-72 h-4 z-40"></div>
    <div class="absolute left-1/2 -translate-x-1/2 mt-3 w-72 bg-white rounded-xl shadow-xl border border-gray-100 p-6 z-50 hidden group-hover:block group-focus-within:block">
        <div class="text-center text-gray-700 mb-4"><?php echo esc_html__('Sign up now and enjoy discounted shopping!', 'shop-theme'); ?></div>
        <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="btn btn-primary w-full text-white font-semibold rounded-lg py-3 text-center transition"><?php echo esc_html__('Log In', 'shop-theme'); ?></a>
        <div class="text-center mt-4 text-sm text-gray-500">
            <?php printf(
                esc_html__('New Customer? %s', 'shop-theme'),
                '<a href="' . esc_url(wc_get_page_permalink('myaccount') . '?action=register') . '" class="text-primary hover:underline">' . esc_html__('Sign Up', 'shop-theme') . '</a>'
            ); ?>
        </div>
    </div>
</div>
<?php else : ?>
<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="flex flex-col items-start justify-center hover:text-primary transition-all duration-300">
    <span class="flex items-center font-semibold text-base">
        <?php echo esc_html__('My Account', 'shop-theme'); ?>
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
    </span>
    <span class="text-xs text-gray-500 mt-0.5"><?php printf(esc_html__('Hello, %s', 'shop-theme'), esc_html(wp_get_current_user()->display_name)); ?></span>
</a>
<?php endif; ?>