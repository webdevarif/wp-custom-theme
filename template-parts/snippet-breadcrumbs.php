<?php
// Breadcrumbs
if ( function_exists('woocommerce_breadcrumb') ) {
    woocommerce_breadcrumb([
        'delimiter'   => get_icon('icon-chevron-right'),
        'wrap_before' => '<div class="breadcrumbs flex items-center gap-1 text-gray-500 [&_a]:text-black [&_a]:underline [&_a]:hover:text-primary">',
        'wrap_after'  => '</div>',
        'before'      => '',
        'after'       => '',
        'home'        => _x( 'Home', 'breadcrumb', 'woocommerce' ),
    ]);
}
?>