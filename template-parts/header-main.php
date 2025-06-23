<div class="w-full bg-white border-b border-gray-200">
    <div class="container flex items-center justify-between py-2 lg:py-4 px-4 relative">
        <!-- Mobile: Hamburger, Logo, Cart -->
        <div class="flex items-center w-full lg:hidden justify-between">
            <button id="mobile-menu-toggle" class="rounded hover:bg-gray-100" aria-label="Open menu">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex-1 flex justify-center">
                <div class="h-12 w-28 flex items-center justify-center">
                    <?php the_custom_logo(); ?>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <?php get_template_part('template-parts/snippet-mini-cart'); ?>
                <a href="#" class="inline-flex items-center text-black [&_svg]:w-[24px] [&_svg]:h-[24px]">
                    <?php echo get_icon('icon-help-alt'); ?>
                </a>
            </div>
        </div>
        <!-- Desktop: Logo, Search, Account, Cart -->
        <div class="flex items-center space-x-3 hidden lg:flex md:w-[17rem]">
            <?php if (has_custom_logo()) : ?>
                <div class="h-16 w-36 flex-shrink-0 flex items-center">
                    <?php the_custom_logo(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php get_template_part('template-parts/snippet-searchform'); ?>
        <div class="items-center space-x-6 hidden lg:flex">
            <?php get_template_part('template-parts/snippet-myaccount'); ?>
            <?php get_template_part('template-parts/snippet-mini-cart'); ?>            
        </div>
        <!-- Mobile Drawer (HTML, not JS-injected) -->
        <?php get_template_part('template-parts/mobile-drawer'); ?>
    </div>
</div>