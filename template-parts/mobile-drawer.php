<div id="mobile-drawer" class="fixed inset-0 z-50 pointer-events-none">
  <div id="drawer-backdrop" class="absolute inset-0 bg-black bg-opacity-40 opacity-0 transition-opacity duration-300 pointer-events-none"></div>
  <aside id="drawer-panel" class="fixed top-0 left-0 h-full w-80 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-50 flex flex-col pointer-events-auto" tabindex="-1" aria-label="Mobile menu">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <div class="h-10 w-28 flex items-center justify-center">
        <?php the_custom_logo(); ?>
      </div>
      <div class="flex items-center space-x-2">  
        <?php get_template_part('template-parts/snippet-mini-cart'); ?>
        <button id="drawer-close" class="text-gray-700 ml-2" aria-label="Close menu">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
    <nav class="flex-1 px-6 py-4 border-b">
      <div class="text-xs uppercase text-gray-400 font-bold mb-2"><?php echo esc_html__('Main Menu', 'shop-theme'); ?></div>
      <?php
      $menu_items = wp_get_nav_menu_items(get_nav_menu_locations()['menu-1'] ?? 0);
      if ($menu_items) {
        // Build parent/child tree
        $menu_tree = [];
        foreach ($menu_items as $item) {
          if ($item->menu_item_parent == 0) {
            $menu_tree[$item->ID] = [ 'item' => $item, 'children' => [] ];
          }
        }
        foreach ($menu_items as $item) {
          if ($item->menu_item_parent && isset($menu_tree[$item->menu_item_parent])) {
            $menu_tree[$item->menu_item_parent]['children'][] = $item;
          }
        }
        foreach ($menu_tree as $node) {
          $item = $node['item'];
          $children = $node['children'];
          $has_children = !empty($children);
          if ($has_children) {
            echo '<div class="drawer-accordion">';
            echo '<button class="flex items-center w-full py-2 font-semibold text-gray-800 hover:text-primary focus:outline-none" data-accordion-toggle>';
            printf(esc_html__('%s', 'shop-theme'), esc_html($item->title));
            echo '<svg class="w-4 h-4 ml-auto transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>';
            echo '</button>';
            echo '<div class="pl-4 hidden" data-accordion-content>';
            foreach ($children as $child) {
              printf('<a href="%s" class="block py-2 text-gray-700 hover:text-primary">%s</a>', esc_url($child->url), esc_html__($child->title, 'shop-theme'));
            }
            echo '</div>';
            echo '</div>';
          } else {
            printf('<a href="%s" class="block py-2 text-gray-800 font-semibold hover:text-primary">%s</a>', esc_url($item->url), esc_html__($item->title, 'shop-theme'));
          }
        }
      }
      ?>
      <a href="#" class="block py-2 text-primary font-semibold"><?php echo esc_html__('Help Center', 'shop-theme'); ?></a>
    </nav>
    <div class="px-6 py-4 overflow-y-auto">
      <div class="text-xs uppercase text-gray-400 font-bold mb-2"><?php echo esc_html__('Category Menu', 'shop-theme'); ?></div>
      <ul class="space-y-2">
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
          <li class="drawer-accordion">
            <button class="flex items-center w-full py-2 font-semibold text-gray-800 hover:text-primary focus:outline-none" data-accordion-toggle>
              <?php if ($thumb_url): ?>
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($cat->name); ?>" class="w-5 h-5 mr-2 object-cover rounded" />
              <?php endif; ?>
              <?php printf(esc_html__('%s', 'shop-theme'), esc_html($cat->name)); ?>
              <?php if ($has_children): ?>
                <svg class="w-4 h-4 ml-auto transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
              <?php endif; ?>
            </button>
            <?php if ($has_children): ?>
            <div class="pl-6 hidden" data-accordion-content>
              <?php foreach ($children as $child):
                $child_thumb_id = get_term_meta($child->term_id, 'thumbnail_id', true);
                $child_thumb_url = $child_thumb_id ? wp_get_attachment_image_url($child_thumb_id, 'thumbnail') : '';
                ?>
                <a href="<?php echo get_term_link($child); ?>" class="block py-2 text-gray-700 hover:text-primary flex items-center">
                  <?php if ($child_thumb_url): ?>
                    <img src="<?php echo esc_url($child_thumb_url); ?>" alt="<?php echo esc_attr($child->name); ?>" class="w-5 h-5 mr-2 object-cover rounded" />
                  <?php endif; ?>
                  <?php printf(esc_html__('%s', 'shop-theme'), esc_html($child->name)); ?>
                </a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </li>
        <?php } ?>
      </ul>
    </div>
  </aside>
</div> 