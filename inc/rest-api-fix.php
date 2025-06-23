<?php
/**
 * REST API Fixes and Optimizations
 * 
 * @package shop-theme
 */

// Fix REST API timeout issues
function shop_theme_rest_api_fixes() {
    // Increase timeout for all HTTP requests
    add_filter('http_request_timeout', function($timeout) {
        // Increase timeout for REST API requests
        if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            return 60; // 60 seconds for REST API
        }
        
        // Increase timeout for local development
        if (strpos(home_url(), 'localhost') !== false || strpos(home_url(), '.local') !== false) {
            return 60; // 60 seconds for local development
        }
        
        return 30; // Default 30 seconds for other requests
    });
    
    // Increase memory limit for REST API
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
        if (!defined('WP_MEMORY_LIMIT')) {
            define('WP_MEMORY_LIMIT', '512M');
        }
        if (!defined('WP_MAX_MEMORY_LIMIT')) {
            define('WP_MAX_MEMORY_LIMIT', '512M');
        }
    }
}
add_action('init', 'shop_theme_rest_api_fixes');

// Optimize REST API performance
function shop_theme_rest_api_performance() {
    // Remove unnecessary REST API endpoints
    add_filter('rest_endpoints', function($endpoints) {
        // Remove unused endpoints to reduce overhead
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }
        
        return $endpoints;
    });
    
    // Optimize REST API responses
    add_filter('rest_prepare_post', function($response, $post, $request) {
        $data = $response->get_data();
        
        // Remove unnecessary fields for better performance
        if ($request->get_param('context') === 'edit') {
            unset($data['guid']);
            unset($data['modified_gmt']);
            unset($data['date_gmt']);
            unset($data['_links']);
            unset($data['meta']);
        }
        
        $response->set_data($data);
        return $response;
    }, 10, 3);
    
    // Optimize post type endpoint
    add_filter('rest_prepare_post_type', function($response, $post_type, $request) {
        $data = $response->get_data();
        
        // Remove unnecessary fields
        unset($data['_links']);
        unset($data['capabilities']);
        
        $response->set_data($data);
        return $response;
    }, 10, 3);
}
add_action('rest_api_init', 'shop_theme_rest_api_performance');

// Add REST API caching
function shop_theme_rest_api_caching() {
    // Cache REST API responses
    add_filter('rest_pre_dispatch', function($result, $server, $request) {
        // Only cache GET requests
        if ($request->get_method() === 'GET') {
            $cache_key = 'rest_api_' . md5($request->get_route() . serialize($request->get_params()));
            $cached_response = get_transient($cache_key);
            
            if ($cached_response !== false) {
                return $cached_response;
            }
        }
        
        return $result;
    }, 5, 3);
    
    // Store REST API responses in cache
    add_filter('rest_post_dispatch', function($response, $server, $request) {
        if ($request->get_method() === 'GET' && $response->get_status() === 200) {
            $cache_key = 'rest_api_' . md5($request->get_route() . serialize($request->get_params()));
            set_transient($cache_key, $response, 300); // 5 minutes cache
        }
        
        return $response;
    }, 10, 3);
}
add_action('rest_api_init', 'shop_theme_rest_api_caching');

// Fix cURL timeout issues
function shop_theme_fix_curl_timeout() {
    // Increase cURL timeout
    add_filter('http_request_args', function($args, $url) {
        // Increase timeout for REST API requests
        if (strpos($url, '/wp-json/') !== false) {
            $args['timeout'] = 60;
        }
        
        // Increase timeout for local development
        if (strpos(home_url(), 'localhost') !== false || strpos(home_url(), '.local') !== false) {
            $args['timeout'] = 60;
        }
        
        return $args;
    }, 10, 2);
}
add_action('init', 'shop_theme_fix_curl_timeout');

// Disable REST API for non-authenticated users (optional security)
function shop_theme_rest_api_security() {
    // Only allow REST API access for authenticated users
    add_filter('rest_authentication_errors', function($result) {
        // If a previous authentication check was applied,
        // pass that result along without modification
        if (true === $result || is_wp_error($result)) {
            return $result;
        }
        
        // Allow REST API access for authenticated users
        if (is_user_logged_in()) {
            return $result;
        }
        
        // Allow public access to specific endpoints
        $allowed_endpoints = [
            '/wp/v2/posts',
            '/wp/v2/pages',
            '/wp/v2/categories',
            '/wp/v2/tags'
        ];
        
        $current_endpoint = $_SERVER['REQUEST_URI'] ?? '';
        foreach ($allowed_endpoints as $endpoint) {
            if (strpos($current_endpoint, $endpoint) !== false) {
                return $result;
            }
        }
        
        // Block access to other endpoints for non-authenticated users
        return new WP_Error(
            'rest_forbidden',
            __('Sorry, you are not allowed to access this endpoint.', 'shop-theme'),
            array('status' => 401)
        );
    });
}
// Uncomment the line below if you want to restrict REST API access
// add_action('rest_api_init', 'shop_theme_rest_api_security');

// Add REST API debugging (remove in production)
function shop_theme_rest_api_debug() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_action('rest_api_init', function() {
            error_log('REST API initialized with optimizations');
        });
        
        add_filter('rest_pre_dispatch', function($result, $server, $request) {
            error_log('REST API request: ' . $request->get_route());
            return $result;
        }, 1, 3);
    }
}
add_action('init', 'shop_theme_rest_api_debug'); 