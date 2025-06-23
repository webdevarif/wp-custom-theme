<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Normalize phone number function
function normalize_phone($phone) {
    // Remove spaces, dashes, parentheses, and leading +
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Optionally, handle country code normalization here
    return $phone;
}

// Hook to normalize phone number when order is created or updated
add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    $order = wc_get_order($order_id);
    $phone = $order->get_billing_phone();
    $normalized_phone = normalize_phone($phone);
    update_post_meta($order_id, '_billing_phone', $normalized_phone);
});

// Function to check if a phone has a 'Processing' order in the last 12 hours
function is_recent_order_blocked($phone) {
    $normalized_phone = normalize_phone($phone);
    $twelve_hours_ago = current_time('timestamp') - (12 * 3600); // 12 hours in seconds

    $args = [
        'post_type' => 'shop_order',
        'post_status' => 'wc-processing', // Only check "Processing" orders
        'meta_query' => [
            [
                'key' => '_billing_phone',
                'value' => $normalized_phone,
                'compare' => '=' // Use '=' for exact match since phone is normalized
            ],
        ],
        'date_query' => [
            [
                'column' => 'post_date',
                'after' => date('Y-m-d H:i:s', $twelve_hours_ago),
                'inclusive' => true,
            ],
        ],
        'posts_per_page' => 1, // Only need one match to block
        'fields' => 'ids', // Optimize by fetching only IDs
    ];
    $orders = get_posts($args);
    if (!empty($orders)) {
        return [true, 'আপনার পূর্ববর্তী অর্ডার এখনও প্রসেসিং এ আছে, অনুগ্রহ করে অর্ডার সম্পন্ন হওয়ার পর আবার চেষ্টা করুন।', 'processing_order'];
    }
    return [false, '', ''];
}

// Server-side check to block order during checkout process
add_action('woocommerce_checkout_process', function() {
    $phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';
    if ($phone) {
        list($blocked, $message, $type) = is_recent_order_blocked($phone);
        if ($blocked) {
            wc_add_notice($message, 'error'); // This stops the checkout
        }
    }
});

// AJAX handler for client-side phone check
add_action('wp_ajax_nopriv_check_phone_block', 'check_phone_block_ajax');
add_action('wp_ajax_check_phone_block', 'check_phone_block_ajax');
function check_phone_block_ajax() {
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    if ($phone) {
        list($blocked, $message, $type) = is_recent_order_blocked($phone);
        wp_send_json(['blocked' => $blocked, 'message' => $message]);
    } else {
        wp_send_json(['blocked' => false, 'message' => '']);
    }
}

// Add JavaScript to checkout page for real-time phone check
add_action('wp_footer', function() {
    if (is_checkout()) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            var phoneField = $('input[name="billing_phone"]');
            var placeOrderBtn = $('#place_order');

            phoneField.on('blur', function() {
                var phone = $(this).val().trim();
                if (phone.length < 6) return; // Skip short/empty numbers

                // Show loading spinner or message (optional)
                placeOrderBtn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'check_phone_block',
                        phone: phone
                    },
                    success: function(response) {
                        if (response.blocked) {
                            placeOrderBtn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
                            alert(response.message); // Or display in a custom div
                        } else {
                            placeOrderBtn.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                        }
                    },
                    error: function() {
                        placeOrderBtn.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                    }
                });
            });
        });
        </script>
        <?php
    }
});