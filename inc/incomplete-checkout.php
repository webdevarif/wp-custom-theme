<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue JavaScript on checkout page (removed external js file, inline JS instead)
add_action('wp_enqueue_scripts', 'wac_enqueue_scripts');
function wac_enqueue_scripts() {
    if (is_checkout()) {
        wp_localize_script('jquery', 'wac_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wac_capture_nonce'),
            'session_token' => wp_generate_uuid4(),
            'client_ip' => wac_get_client_ip(),
            'device_info' => wac_get_device_info(),
        ]);
    }
}

// Register custom post type for abandoned checkouts
add_action('init', 'wac_register_post_type');
function wac_register_post_type() {
    register_post_type('abandoned_checkout', [
        'labels' => [
            'name' => 'Abandoned Checkouts',
            'singular_name' => 'Abandoned Checkout',
        ],
        'public' => false,
        'show_ui' => false,
        'show_in_menu' => false,
        'supports' => ['title'],
    ]);
}

// Helper to get client IP
function wac_get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

// Helper to get device info
function wac_get_device_info() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

// Save form data and cart contents via AJAX
add_action('wp_ajax_wac_save_checkout_data', 'wac_save_checkout_data');
add_action('wp_ajax_nopriv_wac_save_checkout_data', 'wac_save_checkout_data');
function wac_save_checkout_data() {
    // Debug log all incoming POST data
    file_put_contents(get_template_directory() . '/wac_debug.log', "\n--- Incoming POST ---\n" . print_r($_POST, true), FILE_APPEND);
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wac_capture_nonce')) {
        file_put_contents(get_template_directory() . '/wac_debug.log', "\n[ERROR] Invalid nonce\n", FILE_APPEND);
        wp_send_json_error('Invalid nonce');
        return;
    }
    // Get and sanitize form data
    if (!isset($_POST['form_data'])) {
        file_put_contents(get_template_directory() . '/wac_debug.log', "\n[ERROR] No form data received\n", FILE_APPEND);
        wp_send_json_error('No form data received');
        return;
    }
    $form_data = json_decode(stripslashes($_POST['form_data']), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents(get_template_directory() . '/wac_debug.log', "\n[ERROR] Invalid form data format\n", FILE_APPEND);
        wp_send_json_error('Invalid form data format');
        return;
    }
    // Get session token
    if (!isset($_POST['session_token'])) {
        file_put_contents(get_template_directory() . '/wac_debug.log', "\n[ERROR] No session token\n", FILE_APPEND);
        wp_send_json_error('No session token');
        return;
    }
    $session_token = sanitize_text_field($_POST['session_token']);

    // Get client IP and device info, fallback to server values if missing
    $client_ip = isset($_POST['client_ip']) && $_POST['client_ip'] ? sanitize_text_field($_POST['client_ip']) : wac_get_client_ip();
    $device_info = isset($_POST['device_info']) && $_POST['device_info'] ? sanitize_text_field($_POST['device_info']) : wac_get_device_info();

    $post_id = null;
    $existing_by_ip = [];
    if ($client_ip) {
        $existing_by_ip = get_posts([
            'post_type' => 'abandoned_checkout',
            'meta_query' => [
                ['key' => 'client_ip', 'value' => $client_ip],
                ['key' => 'status', 'value' => 'ordered', 'compare' => '!='],
                ['key' => 'not_interested', 'compare' => 'NOT EXISTS'],
            ],
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'ASC',
        ]);
    }
    if (!empty($existing_by_ip)) {
        $post_id = $existing_by_ip[0]->ID;
    } else {
        // Fallback to session_token logic
        $existing = get_posts([
            'post_type' => 'abandoned_checkout',
            'meta_query' => [
                [
                    'key' => 'session_token',
                    'value' => $session_token,
                ],
            ],
        ]);
        if (!empty($existing)) {
            $post_id = $existing[0]->ID;
        } else {
            // Only create a new post if at least name or email is present
            $has_key_field = false;
            foreach ($form_data as $field) {
                if (isset($field['name'], $field['value']) && in_array($field['name'], ['billing_first_name', 'billing_last_name', 'billing_email']) && trim($field['value']) !== '') {
                    $has_key_field = true;
                    break;
                }
            }
            if ($has_key_field) {
                $post_id = wp_insert_post([
                    'post_type' => 'abandoned_checkout',
                    'post_title' => 'Checkout ' . date('Y-m-d H:i:s'),
                    'post_status' => 'publish',
                ]);
            } else {
                // Do not create a new post if no key field is present
                wp_send_json_success('No key field present, not saving');
                return;
            }
        }
    }
    if (is_wp_error($post_id)) {
        wp_send_json_error('Failed to create/update post');
        return;
    }
    // Save form data as meta
    foreach ($form_data as $field) {
        if (isset($field['name']) && isset($field['value'])) {
            update_post_meta($post_id, sanitize_text_field($field['name']), sanitize_text_field($field['value']));
        }
    }
    // Save cart contents
    if (function_exists('WC') && WC()->cart) {
        $cart_contents = WC()->cart->get_cart();
        $cart_data = [];
        foreach ($cart_contents as $item) {
            $product = $item['data'];
            $cart_data[] = [
                'product_id' => $product->get_id(),
                'name' => $product->get_name(),
                'quantity' => $item['quantity'],
                'subtotal' => $item['line_subtotal'],
            ];
        }
        update_post_meta($post_id, 'cart_contents', $cart_data);
    }
    update_post_meta($post_id, 'session_token', $session_token);
    update_post_meta($post_id, 'last_updated', current_time('mysql'));
    update_post_meta($post_id, 'status', 'in_progress');
    update_post_meta($post_id, 'client_ip', $client_ip);
    update_post_meta($post_id, 'device_info', $device_info);
    wp_send_json_success('Data saved successfully');
}

// Save session token and update status when order is placed
add_action('woocommerce_checkout_order_processed', 'wac_save_order_token', 10, 3);
function wac_save_order_token($order_id, $posted_data, $order) {
    if (isset($_POST['session_token'])) {
        $session_token = sanitize_text_field($_POST['session_token']);
        update_post_meta($order_id, 'session_token', $session_token);
        
        // Update corresponding checkout post
        $checkouts = get_posts([
            'post_type' => 'abandoned_checkout',
            'meta_query' => [
                [
                    'key' => 'session_token',
                    'value' => $session_token,
                ],
            ],
        ]);
        if (!empty($checkouts)) {
            $post_id = $checkouts[0]->ID;
            update_post_meta($post_id, 'status', 'ordered');
            update_post_meta($post_id, 'order_id', $order_id);
        }
    }
}

// Mark abandoned checkouts via cron
add_action('wac_check_abandoned', 'wac_mark_abandoned');
function wac_mark_abandoned() {
    $checkouts = get_posts([
        'post_type' => 'abandoned_checkout',
        'meta_query' => [
            [
                'key' => 'status',
                'value' => 'in_progress',
            ],
        ],
    ]);
    
    foreach ($checkouts as $checkout) {
        $last_updated = get_post_meta($checkout->ID, 'last_updated', true);
        if (strtotime($last_updated) < strtotime('-15 minutes')) {
            $session_token = get_post_meta($checkout->ID, 'session_token', true);
            $order_exists = get_posts([
                'post_type' => 'shop_order',
                'meta_query' => [
                    [
                        'key' => 'session_token',
                        'value' => $session_token,
                    ],
                ],
            ]);
            if (empty($order_exists)) {
                update_post_meta($checkout->ID, 'status', 'abandoned');
            }
        }
    }
}

// Schedule cron job
add_action('wp', 'wac_schedule_cron');
function wac_schedule_cron() {
    if (!wp_next_scheduled('wac_check_abandoned')) {
        wp_schedule_event(time(), 'hourly', 'wac_check_abandoned');
    }
}

// Admin menu page with "Incomplete Checkout" submenu
add_action('admin_menu', 'wac_admin_menu');
function wac_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'Incomplete Checkout',
        'Incomplete Checkout',
        'manage_woocommerce',
        'wac_checkout_data',
        'wac_render_admin_page'
    );
}

// Render admin page
function wac_render_admin_page() {
    // Handle email save
    if (isset($_POST['wac_save_emails']) && isset($_POST['manager_emails'])) {
        $emails = array_filter(array_map('sanitize_email', preg_split('/[\s,]+/', $_POST['manager_emails'])));
        update_option('wac_manager_emails', $emails);
        echo '<div class="updated notice"><p>Manager emails updated.</p></div>';
    }
    $manager_emails = get_option('wac_manager_emails', []);
    $manager_emails_str = esc_attr(implode(", ", $manager_emails));
    // Handle filter/search
    $search = isset($_GET['wac_search']) ? sanitize_text_field($_GET['wac_search']) : '';
    $filter_status = isset($_GET['wac_status']) ? sanitize_text_field($_GET['wac_status']) : '';
    $bulk_action = isset($_POST['wac_bulk_action']) ? sanitize_text_field($_POST['wac_bulk_action']) : '';
    $bulk_ids = isset($_POST['wac_bulk_ids']) ? array_map('intval', (array)$_POST['wac_bulk_ids']) : [];
    // Handle bulk actions
    if ($bulk_action && $bulk_ids) {
        if ($bulk_action === 'send_manager') {
            $rows = '';
            foreach ($bulk_ids as $id) {
                $first_name = get_post_meta($id, 'billing_first_name', true);
                $last_name = get_post_meta($id, 'billing_last_name', true);
                $email = get_post_meta($id, 'billing_email', true);
                $phone = get_post_meta($id, 'billing_phone', true);
                $address = get_post_meta($id, 'billing_address_1', true);
                $size = get_post_meta($id, 'billing_size', true);
                $cart_contents = get_post_meta($id, 'cart_contents', true);
                $cart_html = '';
                if (is_array($cart_contents) && count($cart_contents)) {
                    $cart_html .= '<table style="width:100%;border-collapse:collapse;background:#fff;margin-top:4px;">';
                    $cart_html .= '<tr style="background:#f6f7f7;"><th style="padding:4px 2px;text-align:left;">Product</th><th style="padding:4px 2px;text-align:left;">Qty</th><th style="padding:4px 2px;text-align:left;">Subtotal</th></tr>';
                    foreach ($cart_contents as $item) {
                        $subtotal = is_numeric($item['subtotal']) ? wc_price($item['subtotal']) : $item['subtotal'];
                        $cart_html .= '<tr>';
                        $cart_html .= '<td style="padding:4px 2px;">' . esc_html($item['name']) . '</td>';
                        $cart_html .= '<td style="padding:4px 2px;">' . esc_html($item['quantity']) . '</td>';
                        $cart_html .= '<td style="padding:4px 2px;">' . wp_strip_all_tags($subtotal) . '</td>';
                        $cart_html .= '</tr>';
                    }
                    $cart_html .= '</table>';
                }
                $rows .= '<tr>';
                $rows .= '<td style="padding:8px 4px;">' . esc_html($first_name . ' ' . $last_name) . '</td>';
                $rows .= '<td style="padding:8px 4px;"><a href="mailto:' . esc_attr($email) . '" style="color:#2271b1;text-decoration:underline;">' . esc_html($email) . '</a></td>';
                $rows .= '<td style="padding:8px 4px;">' . esc_html($phone) . '</td>';
                $rows .= '<td style="padding:8px 4px;">' . esc_html($address) . '</td>';
                $rows .= '<td style="padding:8px 4px;">' . ($size ? esc_html($size) : '-') . '</td>';
                $rows .= '<td style="padding:8px 4px;">' . $cart_html . '</td>';
                $rows .= '</tr>';
                // Increment send_manager_count for each
                $count = intval(get_post_meta($id, 'send_manager_count', true));
                update_post_meta($id, 'send_manager_count', $count + 1);
            }
            $content = '<div style="font-family:Arial,sans-serif;max-width:900px;margin:0 auto;background:#f9f9f9;padding:24px;border-radius:8px;">';
            $content .= '<h2 style="color:#2271b1;margin-top:0;">Abandoned Checkouts (Bulk Notification)</h2>';
            $content .= '<table style="width:100%;border-collapse:collapse;margin-bottom:20px;background:#fff;">';
            $content .= '<tr style="background:#f6f7f7;">';
            $content .= '<th style="padding:8px 4px;">Name</th>';
            $content .= '<th style="padding:8px 4px;">Email</th>';
            $content .= '<th style="padding:8px 4px;">Phone</th>';
            $content .= '<th style="padding:8px 4px;">Address</th>';
            $content .= '<th style="padding:8px 4px;">Size</th>';
            $content .= '<th style="padding:8px 4px;">Cart</th>';
            $content .= '</tr>';
            $content .= $rows;
            $content .= '</table>';
            $content .= '<div style="margin-top:24px;font-size:16px;color:#333;"><strong>Please call these customers and confirm their orders.</strong></div>';
            $content .= '</div>';
            $emails = get_option('wac_manager_emails', []);
            if (!empty($emails)) {
                add_filter('wp_mail_content_type', function() { return 'text/html'; });
                $sent = wp_mail($emails, 'Abandoned Checkouts: Bulk Notification', $content);
                remove_filter('wp_mail_content_type', 'set_html_content_type');
                if ($sent) {
                    echo '<div class="updated notice"><p>Bulk manager email sent for ' . count($bulk_ids) . ' checkouts.</p></div>';
                } else {
                    echo '<div class="error notice"><p>Failed to send bulk manager email.</p></div>';
                }
            }
        } else {
            foreach ($bulk_ids as $id) {
                if ($bulk_action === 'delete') {
                    wp_delete_post($id, true);
                } elseif ($bulk_action === 'not_interested') {
                    update_post_meta($id, 'not_interested', '1');
                }
            }
            echo '<div class="updated notice"><p>Bulk action applied.</p></div>';
        }
    }
    // Pagination settings
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Build query args
    $query_args = [
        'post_type' => 'abandoned_checkout',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'orderby' => 'date',
        'order' => 'DESC',
    ];
    if ($search) {
        $query_args['meta_query'] = [
            'relation' => 'OR',
            [
                'key' => 'billing_first_name',
                'value' => $search,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'billing_last_name',
                'value' => $search,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'billing_email',
                'value' => $search,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'billing_phone',
                'value' => $search,
                'compare' => 'LIKE',
            ],
        ];
        add_filter('posts_where', function($where) use ($search) {
            global $wpdb;
            $like = '%' . esc_sql($wpdb->esc_like($search)) . '%';
            $where .= $wpdb->prepare(" OR {$wpdb->posts}.post_title LIKE %s", $like);
            return $where;
        });
    }
    if ($filter_status === 'abandoned' || $filter_status === 'ordered' || $filter_status === 'not_interested') {
        $query_args['meta_query'] = [
            [
                'key' => $filter_status === 'not_interested' ? 'not_interested' : 'status',
                'value' => $filter_status === 'not_interested' ? '1' : $filter_status,
            ],
        ];
    }
    // Get total items for pagination
    $total_items = count(get_posts(array_merge($query_args, ['posts_per_page' => -1, 'offset' => 0, 'fields' => 'ids'])));
    // Get checkouts for current page
    $checkouts = get_posts($query_args);
    $ajax_nonce = wp_create_nonce('wac_create_order_nonce');
    ?>
    <div class="wrap">
        <h1>Incomplete Checkout</h1>
        <form method="get" class="wac-filters-bar" style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <input type="hidden" name="page" value="wac_checkout_data" />
            <input type="text" name="wac_search" value="<?php echo esc_attr($search); ?>" placeholder="Search name, email, phone..." style="min-width:200px;" />
            <select name="wac_status">
                <option value="">All Statuses</option>
                <option value="abandoned"<?php selected($filter_status, 'abandoned'); ?>>Abandoned</option>
                <option value="ordered"<?php selected($filter_status, 'ordered'); ?>>Ordered</option>
                <option value="not_interested"<?php selected($filter_status, 'not_interested'); ?>>Not Interested</option>
            </select>
            <select name="wac_bulk_action" style="min-width:120px;">
                <option value="">Bulk actions</option>
                <option value="send_manager">Send Manager</option>
                <option value="not_interested">Mark as Not Interested</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="button" name="wac_apply_bulk">Apply</button>
            <button type="submit" class="button">Filter</button>
            <span style="margin-left:auto;"><span class="displaying-num"><?php echo $total_items; ?> items</span></span>
        </form>
        <form method="post" id="wac-bulk-form">
        <div class="checkout-table-wrap">
            <table class="checkout-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="wac_bulk_select_all" /></th>
                        <th>Name</th>
                        <th>Content</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($checkouts as $checkout): 
                    $first_name = get_post_meta($checkout->ID, 'billing_first_name', true);
                    $last_name = get_post_meta($checkout->ID, 'billing_last_name', true);
                    $email = get_post_meta($checkout->ID, 'billing_email', true);
                    $phone = get_post_meta($checkout->ID, 'billing_phone', true);
                    $address = get_post_meta($checkout->ID, 'billing_address_1', true);
                    $size = get_post_meta($checkout->ID, 'billing_size', true);
                    $cart_contents = get_post_meta($checkout->ID, 'cart_contents', true);
                    $status = get_post_meta($checkout->ID, 'status', true);
                    $order_id = get_post_meta($checkout->ID, 'order_id', true);
                    $date = get_post_field('post_date', $checkout->ID);
                    $timestamp = strtotime($date);
                    $time_ago = human_time_diff($timestamp, current_time('timestamp')) . ' ago';
                    $order_status = '';
                    $order = false;
                    if ($order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            $order_status = $order->get_status();
                        }
                    }
                    $send_manager_count = intval(get_post_meta($checkout->ID, 'send_manager_count', true));
                    $not_interested = get_post_meta($checkout->ID, 'not_interested', true) === '1';
                    $client_ip = get_post_meta($checkout->ID, 'client_ip', true);
                    $device_info = get_post_meta($checkout->ID, 'device_info', true);
                ?>
                    <tr class="checkout-item<?php if ($not_interested) echo ' wac-disabled'; ?>" data-checkout-id="<?php echo $checkout->ID; ?>">
                        <td><input type="checkbox" name="wac_bulk_ids[]" value="<?php echo $checkout->ID; ?>" /></td>
                        <td><strong><?php echo esc_html(trim($first_name . ' ' . $last_name)); ?></strong></td>
                        <td style="font-size:12px; line-height:1.6;">
                            <?php if ($email) echo '<div><strong>Email:</strong> ' . esc_html($email) . '</div>'; ?>
                            <?php if ($address) echo '<div><strong>Address:</strong> ' . esc_html($address) . '</div>'; ?>
                            <?php if ($size) echo '<div><strong>Size:</strong> ' . esc_html($size) . '</div>'; ?>
                            <?php if (is_array($cart_contents) && count($cart_contents)) {
                                echo '<div><strong>Cart:</strong>';
                                foreach ($cart_contents as $item) {
                                    echo '<div class="cart-item">' . esc_html($item['name']) . ' x ' . esc_html($item['quantity']) . ' (' . wc_price($item['subtotal']) . ')</div>';
                                }
                                echo '</div>';
                            } ?>
                            <?php if ($client_ip) echo '<div><strong>IP:</strong> ' . esc_html($client_ip) . '</div>'; ?>
                            <?php if ($device_info) echo '<div><strong>Device:</strong> ' . esc_html($device_info) . '</div>'; ?>
                            <?php if ($phone) echo '<div><strong>Phone:</strong> ' . esc_html($phone) . '</div>'; ?>
                        </td>
                        <td><?php echo esc_html($time_ago); ?></td>
                        <td>
                            <?php 
                            if ($order_id && $order) {
                                echo '<span class="order-status">' . esc_html(ucfirst($order_status)) . '</span>';
                            } else {
                                echo '<span class="status-abandoned">Abandoned</span>';
                            }
                            ?>
                        </td>
                        <td style="position:relative;">
                            <?php 
                            if ($order_id && $order) {
                                echo '<div><a href="' . admin_url('post.php?post=' . $order_id . '&action=edit') . '" class="button button-small">View Order</a></div>';
                            } else {
                                if (!$not_interested) {
                                    echo '<div style="margin-bottom:4px"><button class="button button-primary create-order-btn" data-checkout-id="' . $checkout->ID . '" data-nonce="' . esc_attr($ajax_nonce) . '">Confirm Order</button></div>';
                                    echo '<div style="margin-bottom:4px"><button class="button button-secondary send-manager-btn" data-checkout-id="' . $checkout->ID . '" data-nonce="' . esc_attr($ajax_nonce) . '">Send Manager (' . $send_manager_count . ')</button></div>';
                                    echo '<div style="margin-bottom:4px"><button class="button button-danger not-interested-btn" data-checkout-id="' . $checkout->ID . '" data-nonce="' . esc_attr($ajax_nonce) . '">Not Interested</button></div>';
                                    echo '<div><button class="button button-danger delete-row-btn" data-checkout-id="' . $checkout->ID . '" data-nonce="' . esc_attr($ajax_nonce) . '">Delete</button></div>';
                                } else {
                                    echo '<div><button class="button button-small enable-row-btn" style="display:none;position:absolute;right:0;top:0;" data-checkout-id="' . $checkout->ID . '" data-nonce="' . esc_attr($ajax_nonce) . '">Enable</button></div>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </form>
        <form method="post" style="margin-top:20px;display:flex;align-items:flex-end;gap:10px;">
            <label for="manager_emails" style="margin-bottom:0;"><strong>Manager Emails:</strong></label>
            <input type="text" id="manager_emails" name="manager_emails" value="<?php echo $manager_emails_str; ?>" style="width:320px;" placeholder="Enter emails..." />
            <input type="submit" name="wac_save_emails" class="button" value="Save Emails" />
        </form>
        <div class="tablenav top" style="margin:0;">
            <div class="tablenav-pages">
                <?php if ($total_items > $per_page): ?>
                    <span class="pagination-links">
                        <?php
                        $total_pages = ceil($total_items / $per_page);
                        if ($current_page > 1) {
                            echo '<a class="first-page button" href="' . add_query_arg('paged', 1) . '"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></a>';
                            echo '<a class="prev-page button" href="' . add_query_arg('paged', $current_page - 1) . '"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>';
                        } else {
                            echo '<span class="first-page button disabled"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></span>';
                            echo '<span class="prev-page button disabled"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></span>';
                        }
                        echo '<span class="paging-input">' . $current_page . ' of <span class="total-pages">' . $total_pages . '</span></span>';
                        if ($current_page < $total_pages) {
                            echo '<a class="next-page button" href="' . add_query_arg('paged', $current_page + 1) . '"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>';
                            echo '<a class="last-page button" href="' . add_query_arg('paged', $total_pages) . '"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></a>';
                        } else {
                            echo '<span class="next-page button disabled"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></span>';
                            echo '<span class="last-page button disabled"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></span>';
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <style>
    .checkout-table-wrap { margin-top: 20px; }
    .checkout-table { width: 100%; border-collapse: collapse; background: #fff; }
    .checkout-table th, .checkout-table td { border: 1px solid #ccd0d4; padding: 8px 10px; text-align: left; vertical-align: top; }
    .checkout-table th { background: #f6f7f7; font-weight: 600; }
    .cart-item { margin-left: 10px; }
    .order-status { font-weight: 600; color: #2271b1; }
    .status-abandoned { color: #d63638; font-weight: 600; }
    .button-disabled { background: #ccc !important; border-color: #ccc !important; color: #888 !important; cursor: not-allowed !important; }
    .wac-topbar { margin-bottom: 20px; }
    .wac-topbar form label { margin-bottom: 0; }
    .wac-topbar input[type=text] { min-width: 220px; }
    .wac-topbar input[type=select] { min-width: 120px; }
    .wac-topbar button { margin-left: 5px; }
    .wac-disabled { opacity: 0.5; background: #f9f9f9 !important; position: relative; }
    .wac-disabled td:not(:last-child),
    .wac-disabled td:not(:last-child) *:not(.enable-row-btn) {
        pointer-events: none !important;
    }
    .enable-row-btn { display: none; position: absolute; right: 0; top: 0; z-index: 2; pointer-events: auto !important; opacity: 1 !important; }
    .wac-disabled .enable-row-btn { display: inline-block !important; }
    .button-danger {
        background: #d63638 !important;
        border-color: #d63638 !important;
        color: #fff !important;
    }
    .button-danger:disabled,
    .button-danger[disabled] {
        background: #f2bcbc !important;
        border-color: #f2bcbc !important;
        color: #fff !important;
    }
    .wac-filters-bar { margin-bottom: 10px; }
    .wac-filters-bar input[type=text] { min-width: 200px; }
    .wac-filters-bar select { min-width: 120px; }
    .wac-filters-bar button { margin-left: 5px; }
    .wac-filters-bar .displaying-num { margin-left: 10px; color: #666; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirm Order
        document.querySelectorAll('.create-order-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to create an order from this checkout?')) return;
                const checkoutId = btn.getAttribute('data-checkout-id');
                const nonce = btn.getAttribute('data-nonce');
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'wac_create_order_from_checkout',
                        checkout_id: checkoutId,
                        nonce: nonce
                    }),
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.data.order_edit_url;
                    } else {
                        alert('Error: ' + (data.data || 'Failed to create order'));
                    }
                });
            });
        });
        // Send Manager
        document.querySelectorAll('.send-manager-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const checkoutId = btn.getAttribute('data-checkout-id');
                const nonce = btn.getAttribute('data-nonce');
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'wac_send_manager_email',
                        checkout_id: checkoutId,
                        nonce: nonce
                    }),
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.success ? 'Manager notified!' : ('Error: ' + (data.data || 'Failed to send')));
                    if (data.success) window.location.reload();
                });
            });
        });
        // Not Interested
        document.querySelectorAll('.not-interested-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Mark as not interested?')) return;
                const checkoutId = btn.getAttribute('data-checkout-id');
                const nonce = btn.getAttribute('data-nonce');
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'wac_set_not_interested',
                        checkout_id: checkoutId,
                        value: 1,
                        nonce: nonce
                    }),
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.success ? 'Marked as not interested.' : ('Error: ' + (data.data || 'Failed')));
                    if (data.success) window.location.reload();
                });
            });
        });
        // Enable (undo Not Interested)
        document.querySelectorAll('.enable-row-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const checkoutId = btn.getAttribute('data-checkout-id');
                const nonce = btn.getAttribute('data-nonce');
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'wac_set_not_interested',
                        checkout_id: checkoutId,
                        value: 0,
                        nonce: nonce
                    }),
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.success ? 'Enabled.' : ('Error: ' + (data.data || 'Failed')));
                    if (data.success) window.location.reload();
                });
            });
        });
        // Delete
        document.querySelectorAll('.delete-row-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Delete this checkout?')) return;
                const checkoutId = btn.getAttribute('data-checkout-id');
                const nonce = btn.getAttribute('data-nonce');
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'wac_delete_checkout',
                        checkout_id: checkoutId,
                        nonce: nonce
                    }),
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.success ? 'Deleted.' : ('Error: ' + (data.data || 'Failed')));
                    if (data.success) window.location.reload();
                });
            });
        });
        // Select all checkbox logic
        const selectAll = document.getElementById('wac_bulk_select_all');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="wac_bulk_ids[]"]');
                checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
            });
        }
    });
    </script>
    <?php
}

// AJAX handler to create order from abandoned checkout
add_action('wp_ajax_wac_create_order_from_checkout', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permission denied');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wac_create_order_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    $checkout_id = intval($_POST['checkout_id'] ?? 0);
    if (!$checkout_id) {
        wp_send_json_error('Invalid checkout ID');
    }
    $order_id = get_post_meta($checkout_id, 'order_id', true);
    if ($order_id && wc_get_order($order_id)) {
        wp_send_json_error('Order already exists');
    }
    // Gather meta
    $first_name = get_post_meta($checkout_id, 'billing_first_name', true);
    $last_name = get_post_meta($checkout_id, 'billing_last_name', true);
    $email = get_post_meta($checkout_id, 'billing_email', true);
    $phone = get_post_meta($checkout_id, 'billing_phone', true);
    $address_1 = get_post_meta($checkout_id, 'billing_address_1', true);
    $cart_contents = get_post_meta($checkout_id, 'cart_contents', true);
    if (!$cart_contents || !is_array($cart_contents) || empty($cart_contents)) {
        wp_send_json_error('No cart contents');
    }
    // Create order
    $order = wc_create_order();
    foreach ($cart_contents as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $order->add_product(wc_get_product($product_id), $quantity);
    }
    $order->set_address([
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'email'      => $email,
        'phone'      => $phone,
        'address_1'  => $address_1,
    ], 'billing');
    $order->calculate_totals();
    $order->update_status('processing');
    update_post_meta($checkout_id, 'order_id', $order->get_id());
    update_post_meta($checkout_id, 'status', 'ordered');
    wp_send_json_success([
        'order_id' => $order->get_id(),
        'order_edit_url' => admin_url('post.php?post=' . $order->get_id() . '&action=edit'),
    ]);
});

// JavaScript in wp_footer for checkout page
add_action('wp_footer', 'wac_add_checkout_js');
function wac_add_checkout_js() {
    if (is_checkout()) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add serializeForm function for use in event handlers
            function serializeForm(form) {
                const formData = new FormData(form);
                const data = [];
                for (let [name, value] of formData.entries()) {
                    if (value && value.trim() !== '') {
                        data.push({
                            name: name,
                            value: value.trim()
                        });
                    }
                }
                return data;
            }

            // Add debounce and debouncedSave
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function saveCheckoutData(formData) {
                // Try both classic and block checkout forms
                let form = document.querySelector('form.woocommerce-checkout');
                if (!form) {
                    form = document.querySelector('form.wc-block-checkout__form');
                }
                if (!form) {
                    console.log('Form not found');
                    return;
                }
                const sessionToken = wac_params.session_token;
                fetch(wac_params.ajax_url, {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'wac_save_checkout_data',
                        nonce: wac_params.nonce,
                        form_data: JSON.stringify(formData),
                        session_token: sessionToken
                    }),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Data saved');
                    } else {
                        console.error('Error saving data:', data.data);
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                });
            }

            const debouncedSave = debounce(saveCheckoutData, 500);

            // Try both classic and block checkout forms
            let form = document.querySelector('form.woocommerce-checkout');
            if (!form) {
                form = document.querySelector('form.wc-block-checkout__form');
            }
            if (!form) {
                console.log('Form not found');
                return;
            }
            const sessionToken = wac_params.session_token;
            // Add session token to form
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'session_token';
            tokenInput.value = sessionToken;
            form.appendChild(tokenInput);

            // Trigger save only when billing_phone or billing_email is filled
            const phoneInput = form.querySelector('input[name="billing_phone"]');
            const emailInput = form.querySelector('input[name="billing_email"]');

            if (phoneInput) {
                phoneInput.addEventListener('blur', function() {
                    if (this.value.trim() !== '') {
                        const formData = serializeForm(form);
                        debouncedSave(formData);
                    }
                });
            }

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    if (this.value.trim() !== '') {
                        const formData = serializeForm(form);
                        debouncedSave(formData);
                    }
                });
            }
        });
        </script>
        <?php
    }
}

// AJAX handler to send manager email
add_action('wp_ajax_wac_send_manager_email', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permission denied');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wac_create_order_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    $checkout_id = intval($_POST['checkout_id'] ?? 0);
    if (!$checkout_id) {
        wp_send_json_error('Invalid checkout ID');
    }
    $first_name = get_post_meta($checkout_id, 'billing_first_name', true);
    $last_name = get_post_meta($checkout_id, 'billing_last_name', true);
    $email = get_post_meta($checkout_id, 'billing_email', true);
    $phone = get_post_meta($checkout_id, 'billing_phone', true);
    $address = get_post_meta($checkout_id, 'billing_address_1', true);
    $size = get_post_meta($checkout_id, 'billing_size', true);
    $cart_contents = get_post_meta($checkout_id, 'cart_contents', true);
    
    // Build creative HTML email
    $content = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#f9f9f9;padding:24px;border-radius:8px;">';
    $content .= '<h2 style="color:#2271b1;margin-top:0;">New Abandoned Checkout</h2>';
    $content .= '<table style="width:100%;border-collapse:collapse;margin-bottom:20px;">';
    $content .= '<tr><td style="padding:8px 4px;font-weight:bold;width:120px;">Name:</td><td style="padding:8px 4px;">' . esc_html($first_name . ' ' . $last_name) . '</td></tr>';
    $content .= '<tr><td style="padding:8px 4px;font-weight:bold;">Email:</td><td style="padding:8px 4px;"><a href="mailto:' . esc_attr($email) . '" style="color:#2271b1;text-decoration:underline;">' . esc_html($email) . '</a></td></tr>';
    $content .= '<tr><td style="padding:8px 4px;font-weight:bold;">Phone:</td><td style="padding:8px 4px;">' . esc_html($phone) . '</td></tr>';
    $content .= '<tr><td style="padding:8px 4px;font-weight:bold;">Address:</td><td style="padding:8px 4px;">' . esc_html($address) . '</td></tr>';
    if ($size) {
        $content .= '<tr><td style="padding:8px 4px;font-weight:bold;">Size:</td><td style="padding:8px 4px;">' . esc_html($size) . '</td></tr>';
    }
    $content .= '</table>';
    if (is_array($cart_contents) && count($cart_contents)) {
        $content .= '<h3 style="color:#444;margin-bottom:8px;">Cart Details</h3>';
        $content .= '<table style="width:100%;border-collapse:collapse;background:#fff;">';
        $content .= '<tr style="background:#f6f7f7;"><th style="padding:8px 4px;text-align:left;">Product</th><th style="padding:8px 4px;text-align:left;">Qty</th><th style="padding:8px 4px;text-align:left;">Subtotal</th></tr>';
        foreach ($cart_contents as $item) {
            $subtotal = is_numeric($item['subtotal']) ? wc_price($item['subtotal']) : $item['subtotal'];
            $content .= '<tr>';
            $content .= '<td style="padding:8px 4px;">' . esc_html($item['name']) . '</td>';
            $content .= '<td style="padding:8px 4px;">' . esc_html($item['quantity']) . '</td>';
            $content .= '<td style="padding:8px 4px;">' . wp_strip_all_tags($subtotal) . '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';
    }
    $content .= '<div style="margin-top:24px;font-size:16px;color:#333;"><strong>Please call this person and confirm the order.</strong></div>';
    $content .= '</div>';
    $emails = get_option('wac_manager_emails', []);
    if (empty($emails)) {
        wp_send_json_error('No manager emails set');
    }
    add_filter('wp_mail_content_type', function() { return 'text/html'; });
    $sent = wp_mail($emails, 'Abandoned Checkout: Please Call Customer', $content);
    remove_filter('wp_mail_content_type', 'set_html_content_type');
    if ($sent) {
        // Increment send_manager_count
        $count = intval(get_post_meta($checkout_id, 'send_manager_count', true));
        update_post_meta($checkout_id, 'send_manager_count', $count + 1);
        wp_send_json_success('Email sent');
    } else {
        wp_send_json_error('Failed to send email');
    }
});

// AJAX handler for not interested
add_action('wp_ajax_wac_set_not_interested', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permission denied');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wac_create_order_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    $checkout_id = intval($_POST['checkout_id'] ?? 0);
    $value = isset($_POST['value']) && $_POST['value'] == '1' ? '1' : '0';
    if (!$checkout_id) {
        wp_send_json_error('Invalid checkout ID');
    }
    update_post_meta($checkout_id, 'not_interested', $value);
    wp_send_json_success('Updated');
});

// AJAX handler for deleting checkout
add_action('wp_ajax_wac_delete_checkout', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permission denied');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wac_create_order_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    $checkout_id = intval($_POST['checkout_id'] ?? 0);
    if (!$checkout_id) {
        wp_send_json_error('Invalid checkout ID');
    }
    $deleted = wp_delete_post($checkout_id, true);
    if ($deleted) {
        wp_send_json_success('Deleted');
    } else {
        wp_send_json_error('Failed to delete');
    }
});