<div class="w-full bg-dark shadow-[rgba(33,37,41,0.07)_0px_2px_3px]">
    <div class="container mx-auto flex items-center px-4 relative">
        <!-- Categories Sidebar -->
        <?php if (is_front_page()): ?>
        <div class="hidden lg:block w-[17rem]">
            <div class="">
                <div class="relative">
                    <div class="bg-primary text-white px-6 py-3 font-semibold flex items-center justify-between cursor-pointer">
                        <span><?php echo esc_html__('All Categories', 'shop-theme'); ?></span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <ul class="absolute left-0 top-full w-full bg-white divide-y divide-gray-100 rounded-b-lg shadow-lg border border-gray-200 z-30">
                        <?php
                        // Get all checked categories
                        $args = [
                            'taxonomy' => 'product_cat',
                            'hide_empty' => false,
                            'parent' => 0,
                            'meta_query' => [
                                [
                                    'key' => 'show_browse_category',
                                    'value' => '1',
                                    'compare' => '='
                                ]
                            ]
                        ];
                        $categories = get_terms($args);
                        foreach ($categories as $cat) {
                            // Get checked children
                            $children = get_terms([
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                                'parent' => $cat->term_id,
                                'meta_query' => [
                                    [
                                        'key' => 'show_browse_category',
                                        'value' => '1',
                                        'compare' => '='
                                    ]
                                ]
                            ]);
                            $has_children = !empty($children);
                            // Get thumbnail for parent
                            $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                            $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                            ?>
                            <li class="group relative">
                                <a href="<?php echo get_term_link($cat); ?>" class="flex items-center px-4 py-3 hover:bg-gray-50">
                                    <?php if ($thumb_url): ?>
                                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($cat->name); ?>" class="w-5 h-5 mr-3 object-cover rounded" />
                                    <?php endif; ?>
                                    <?php echo esc_html($cat->name); ?>
                                    <?php if ($has_children): ?>
                                        <svg class="w-4 h-4 ml-auto text-gray-400 group-hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                    <?php endif; ?>
                                </a>
                                <?php if ($has_children): ?>
                                <!-- Subcategory Panel -->
                                <div class="absolute left-full top-0 w-64 bg-white shadow-lg rounded-lg border border-gray-200 p-4 hidden group-hover:block z-30">
                                    <div class="font-semibold text-gray-700/50 mb-2"><?php printf(esc_html__('%s', 'shop-theme'), esc_html($cat->name)); ?></div>
                                    <ul class="space-y-2">
                                        <?php foreach ($children as $child): ?>
                                            <?php $child_thumb_id = get_term_meta($child->term_id, 'thumbnail_id', true);
                                            $child_thumb_url = $child_thumb_id ? wp_get_attachment_image_url($child_thumb_id, 'thumbnail') : '';
                                            ?>
                                            <li>
                                                <a href="<?php echo get_term_link($child); ?>" class="block text-gray-700 hover:text-primary flex items-center">
                                                    <?php if ($child_thumb_url): ?>
                                                        <img src="<?php echo esc_url($child_thumb_url); ?>" alt="<?php echo esc_attr($child->name); ?>" class="w-5 h-5 mr-2 object-cover rounded" />
                                                    <?php endif; ?>
                                                    <?php echo esc_html($child->name); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php else: ?>
        <browse-category class="hidden lg:block w-64">
            <div class="relative">
                <div data-browse-toggle class="bg-primary text-white px-6 py-3 font-semibold flex items-center justify-between cursor-pointer select-none">
                    <span><?php echo esc_html__('All Categories', 'shop-theme'); ?></span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div data-browse-dropdown class="absolute left-0 top-full w-full rounded-b-lg shadow-[1px_2px_5px_rgba(134,142,150,0.12)] bg-white hidden z-30">
                    <ul class="divide-y divide-gray-100">
                        <?php
                        $args = [
                            'taxonomy' => 'product_cat',
                            'hide_empty' => false,
                            'parent' => 0,
                            'meta_query' => [
                                [
                                    'key' => 'show_browse_category',
                                    'value' => '1',
                                    'compare' => '='
                                ]
                            ]
                        ];
                        $categories = get_terms($args);
                        foreach ($categories as $cat) {
                            $children = get_terms([
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                                'parent' => $cat->term_id,
                                'meta_query' => [
                                    [
                                        'key' => 'show_browse_category',
                                        'value' => '1',
                                        'compare' => '='
                                    ]
                                ]
                            ]);
                            $has_children = !empty($children);
                            $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                            $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                            ?>
                            <li class="group relative">
                                <a href="<?php echo get_term_link($cat); ?>" class="flex items-center px-4 py-3 hover:bg-gray-50">
                                    <?php if ($thumb_url): ?>
                                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($cat->name); ?>" class="w-5 h-5 mr-3 object-cover rounded" />
                                    <?php endif; ?>
                                    <?php echo esc_html($cat->name); ?>
                                    <?php if ($has_children): ?>
                                        <svg class="w-4 h-4 ml-auto text-gray-400 group-hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                    <?php endif; ?>
                                </a>
                                <?php if ($has_children): ?>
                                <!-- Subcategory Panel -->
                                <div class="absolute left-full top-0 w-64 bg-white shadow-lg rounded-lg border border-gray-200 p-4 hidden group-hover:block z-30">
                                    <div class="font-semibold text-gray-700/50 mb-2"><?php printf(esc_html__('%s', 'shop-theme'), esc_html($cat->name)); ?></div>
                                    <ul class="space-y-2">
                                        <?php foreach ($children as $child):
                                            $child_thumb_id = get_term_meta($child->term_id, 'thumbnail_id', true);
                                            $child_thumb_url = $child_thumb_id ? wp_get_attachment_image_url($child_thumb_id, 'thumbnail') : '';
                                            ?>
                                            <li>
                                                <a href="<?php echo get_term_link($child); ?>" class="block text-gray-700 hover:text-primary flex items-center">
                                                    <?php if ($child_thumb_url): ?>
                                                        <img src="<?php echo esc_url($child_thumb_url); ?>" alt="<?php echo esc_attr($child->name); ?>" class="w-5 h-5 mr-2 object-cover rounded" />
                                                    <?php endif; ?>
                                                    <?php echo esc_html($child->name); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </browse-category>
        <?php endif; ?>
        <!-- Main Navigation -->
        <nav class="flex-1 items-center ml-6 hidden lg:flex justify-between">
            <ul class="flex space-x-6 text-gray-700 font-semibold">
                <?php
                    wp_nav_menu([
                        'theme_location' => 'menu-1',
                        'container' => false,
                        'items_wrap' => '%3$s',
                        'walker' => new class extends Walker_Nav_Menu {
                            function start_lvl(&$output, $depth = 0, $args = null) {
                                $indent = str_repeat("\t", $depth);
                                $output .= "\n$indent<ul class=\"absolute left-0 top-[calc(100%+0.5rem)] bg-[#2d2d2d] px-4 py-2 transition-all duration-200 invisible group-hover:visible group-hover:top-full z-30 min-w-[200px] before:content-[''] before:absolute before:inset-0 before:-top-4 before:left-0 before:right-0 before:h-4\">\n";
                            }
                            
                            function end_lvl(&$output, $depth = 0, $args = null) {
                                $indent = str_repeat("\t", $depth);
                                $output .= "$indent</ul>\n";
                            }
                            
                            function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                                $indent = ($depth) ? str_repeat("\t", $depth) : '';
                                
                                $classes = empty($item->classes) ? array() : (array) $item->classes;
                                $has_children = in_array('menu-item-has-children', $classes);
                                
                                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
                                $class_names = $class_names ? ' class="' . esc_attr($class_names) . ($has_children ? ' group relative' : '') . '"' : '';
                                
                                $output .= $indent . '<li' . $class_names . '>';
                                
                                $attributes = '';
                                if (!empty($item->attr_title)) {
                                    $attributes .= ' title="' . esc_attr($item->attr_title) . '"';
                                }
                                if (!empty($item->target)) {
                                    $attributes .= ' target="' . esc_attr($item->target) . '"';
                                }
                                if (!empty($item->xfn)) {
                                    $attributes .= ' rel="' . esc_attr($item->xfn) . '"';
                                }
                                if (!empty($item->url)) {
                                    $attributes .= ' href="' . esc_attr($item->url) . '"';
                                }
                                
                                $link_classes = 'flex items-center text-white hover:text-white/75';
                                $link_classes .= $depth === 0 ? ' py-3' : ' py-2';
                                
                                if ($has_children) {
                                    $link_classes .= ' group-hover:text-white/75';
                                }
                                
                                $item_output = $args->before;
                                $item_output .= '<a' . $attributes . ' class="' . $link_classes . '">';
                                $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
                                if ($has_children) {
                                    $item_output .= '<svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>';
                                }
                                $item_output .= '</a>';
                                $item_output .= $args->after;
                                
                                $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
                            }
                            
                            function end_el(&$output, $item, $depth = 0, $args = null) {
                                $output .= "</li>\n";
                            }
                        }
                    ]);
                ?>
            </ul>
        </nav>

        <!-- NOTICE BUTTON -->
        <div class="hidden lg:inline-flex items-center">
            <a href="#" class="flex gap-x-1 items-center text-white hover:text-white/75 py-3 font-semibold font-sans">
                <?php echo get_icon('icon-help-alt'); ?>
                <?php echo esc_html__('Help Center', 'shop-theme'); ?>
            </a>
        </div>
    </div>
</div>