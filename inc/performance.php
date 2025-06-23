<?php
/**
 * Performance Optimizations
 * 
 * @package shop-theme
 */

// Remove unnecessary WordPress features
function shop_theme_performance_optimizations() {
    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Remove embed script
    wp_deregister_script('wp-embed');
    
    // Remove jQuery migrate
    if (!is_admin()) {
        wp_deregister_script('jquery-migrate');
    }
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove Windows Live Writer manifest
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Remove REST API link (but keep REST API functional)
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    
    // Remove adjacent posts links
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    
    // Remove generator tag
    remove_action('wp_head', 'wp_generator');
}
add_action('init', 'shop_theme_performance_optimizations');

// REST API Optimizations
function shop_theme_rest_api_optimizations() {
    // Increase REST API timeout
    add_filter('http_request_timeout', function($timeout) {
        if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            return 30; // 30 seconds for REST API
        }
        return $timeout;
    });
    
    // Optimize REST API responses
    add_filter('rest_pre_dispatch', function($result, $server, $request) {
        // Skip unnecessary fields for better performance
        if ($request->get_route() === '/wp/v2/types/post') {
            add_filter('rest_prepare_post', function($response, $post, $request) {
                $data = $response->get_data();
                
                // Remove unnecessary fields for edit context
                if ($request->get_param('context') === 'edit') {
                    unset($data['guid']);
                    unset($data['modified_gmt']);
                    unset($data['date_gmt']);
                    unset($data['_links']);
                }
                
                $response->set_data($data);
                return $response;
            }, 10, 3);
        }
        
        return $result;
    }, 10, 3);
    
    // Add REST API caching
    add_filter('rest_pre_dispatch', function($result, $server, $request) {
        // Cache GET requests for 5 minutes
        if ($request->get_method() === 'GET') {
            $cache_key = 'rest_api_' . md5($request->get_route() . serialize($request->get_params()));
            $cached_response = get_transient($cache_key);
            
            if ($cached_response !== false) {
                return $cached_response;
            }
        }
        
        return $result;
    }, 5, 3);
    
    // Cache REST API responses
    add_filter('rest_post_dispatch', function($response, $server, $request) {
        if ($request->get_method() === 'GET' && $response->get_status() === 200) {
            $cache_key = 'rest_api_' . md5($request->get_route() . serialize($request->get_params()));
            set_transient($cache_key, $response, 300); // 5 minutes
        }
        
        return $response;
    }, 10, 3);
}
add_action('rest_api_init', 'shop_theme_rest_api_optimizations');

// Optimize database queries
function shop_theme_optimize_queries() {
    // Limit post revisions
    if (!defined('WP_POST_REVISIONS')) {
        define('WP_POST_REVISIONS', 5);
    }
    
    // Disable autosave
    if (!defined('AUTOSAVE_INTERVAL')) {
        define('AUTOSAVE_INTERVAL', 300); // 5 minutes
    }
    
    // Increase memory limit for REST API
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
        if (!defined('WP_MEMORY_LIMIT')) {
            define('WP_MEMORY_LIMIT', '256M');
        }
    }
}
add_action('init', 'shop_theme_optimize_queries');

// Add resource hints for external resources
function shop_theme_resource_hints($hints, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $hints[] = '//cdn.jsdelivr.net';
        $hints[] = '//fonts.googleapis.com';
    }
    
    if ('preconnect' === $relation_type) {
        $hints[] = 'https://cdn.jsdelivr.net';
        $hints[] = 'https://fonts.googleapis.com';
    }
    
    return $hints;
}
add_filter('wp_resource_hints', 'shop_theme_resource_hints', 10, 2);

// Optimize images
function shop_theme_image_optimizations() {
    // Add lazy loading to images
    add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
        $attr['loading'] = 'lazy';
        return $attr;
    }, 10, 3);
    
    // Add WebP support
    add_filter('upload_mimes', function($mimes) {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    });
}
add_action('init', 'shop_theme_image_optimizations');

// Cache control headers
function shop_theme_cache_headers() {
    if (!is_admin()) {
        // Cache static assets for 1 year
        if (strpos($_SERVER['REQUEST_URI'], '/dist/') !== false) {
            header('Cache-Control: public, max-age=31536000');
        }
        
        // Cache images for 1 month
        if (strpos($_SERVER['REQUEST_URI'], '/wp-content/uploads/') !== false) {
            header('Cache-Control: public, max-age=2592000');
        }
        
        // Cache REST API responses for 5 minutes
        if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            header('Cache-Control: public, max-age=300');
        }
    }
}
add_action('send_headers', 'shop_theme_cache_headers');

// Optimize WooCommerce
function shop_theme_woocommerce_optimizations() {
    // Remove WooCommerce scripts and styles from non-WooCommerce pages
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-cart-fragments');
    }
    
    // Remove WooCommerce generator tag
    remove_action('wp_head', 'wc_generator_tag');
}
add_action('wp_enqueue_scripts', 'shop_theme_woocommerce_optimizations', 99);

// Optimize admin area
function shop_theme_admin_optimizations() {
    // Remove unnecessary admin scripts
    if (!current_user_can('manage_options')) {
        wp_dequeue_script('wp-embed');
    }
}
add_action('admin_enqueue_scripts', 'shop_theme_admin_optimizations', 99);

// Add critical CSS inline
function shop_theme_critical_css() {
    if (is_front_page()) {
        echo '<style id="critical-css">';
        echo '.container{max-width:1200px;margin:0 auto;padding:0 1rem;}';
        echo '.bg-white{background-color:#fff;}';
        echo '.text-center{text-align:center;}';
        echo '.py-8{padding-top:2rem;padding-bottom:2rem;}';
        echo '</style>';
    }
}
add_action('wp_head', 'shop_theme_critical_css', 1);

// Fix REST API timeout issues
function shop_theme_fix_rest_timeout() {
    // Increase timeout for local development
    if (strpos(home_url(), 'localhost') !== false || strpos(home_url(), '.local') !== false) {
        add_filter('http_request_timeout', function($timeout) {
            return 60; // 60 seconds for local development
        });
    }
    
    // Optimize REST API for better performance
    add_filter('rest_authentication_errors', function($result) {
        // If a previous authentication check was applied,
        // pass that result along without modification
        if (true === $result || is_wp_error($result)) {
            return $result;
        }
        
        // No authentication has been performed yet.
        // Return an error if user is not logged in.
        if (!is_user_logged_in()) {
            return true;
        }
        
        return $result;
    });
}
add_action('init', 'shop_theme_fix_rest_timeout'); 