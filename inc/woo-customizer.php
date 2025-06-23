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

// Track post views when single post is viewed
function trackPostViews() {
    if (is_single() && !is_user_logged_in()) {
        global $post;
        if ($post) {
            setThemeimPostViews($post->ID);
        }
    }
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

// Hook to track views
add_action( 'wp_head', 'trackPostViews');
// Uncomment the line below if you want to track views for logged-in users too
// add_action( 'wp_head', 'trackPostViewsLoggedIn');

?>