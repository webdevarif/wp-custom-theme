<!-- SEARCH FORM -->
<form class="flex-1 mx-6 hidden lg:block" action="<?php echo esc_url(home_url('/')); ?>" method="get">
    <div class="relative">
        <input type="search" name="s" class="w-full border border-[#CED4DA] shadow-[rgba(33,37,41,0.09)_0px_1px_2px_0px] rounded-[12px] px-6 py-2 h-12 pe-12 focus:outline-none" placeholder="Search for products..." />
        <span class="absolute right-0 w-[3rem] top-0 h-full flex items-center justify-center text-xl">
            <?php echo get_icon('icon-search-alt'); ?>
        </span>
    </div>
</form>