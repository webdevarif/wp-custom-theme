<?php
function getThemeimPostViews($postID){ 
    $count_key = 'post_views_count'; 
    $count = get_post_meta($postID, $count_key, true); 
    if($count == ''){ 
        $count = 0; 
        add_post_meta($postID, $count_key, '0'); 
    } 
    return $count . ' View' . ($count != 1 ? 's' : ''); 
} 

function setThemeimPostViews($postID) { 
    $count_key = 'post_views_count'; 
    $count = get_post_meta($postID, $count_key, true); 
    if($count == ''){ 
        $count = 0; 
    }
    $count++; 
    update_post_meta($postID, $count_key, $count); 
}

// Track post views when single post is viewed - OPTIMIZED VERSION
function trackPostViews() {
    // Only track on single posts/pages, not logged in users, and not bots
    if (!is_single() || is_user_logged_in() || is_bot()) {
        return;
    }
    
    global $post;
    if (!$post) {
        return;
    }
    
    // Use transient to prevent multiple updates in same session
    $transient_key = 'post_view_' . $post->ID . '_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (get_transient($transient_key)) {
        return;
    }
    
    // Set transient for 1 hour to prevent spam
    set_transient($transient_key, true, HOUR_IN_SECONDS);
    
    // Update view count
    setThemeimPostViews($post->ID);
}

// Simple bot detection
function is_bot() {
    $bot_patterns = [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'java'
    ];
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $user_agent = strtolower($user_agent);
    
    foreach ($bot_patterns as $pattern) {
        if (strpos($user_agent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

// Track post views for logged-in users (optional - remove if you don't want to track logged-in users)
function trackPostViewsLoggedIn() {
    if (is_single()) {
        global $post;
        if ($post) {
            setThemeimPostViews($post->ID);
        }
    }
}

remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

// Hook to track views - OPTIMIZED
add_action( 'wp_head', 'trackPostViews');
// Uncomment the line below if you want to track views for logged-in users too
// add_action( 'wp_head', 'trackPostViewsLoggedIn');

?>