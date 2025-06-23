<?php

// === CONFIG ===
$env = file_exists(__DIR__ . '/../env.php') ? include __DIR__ . '/../env.php' : [];
define('COURIER_API_KEY', $env['bdcourier_api_key'] ?? '');
define('COURIER_API_URL', $env['bdcourier_api_url'] ?? '');
define('STEADFAST_API_KEY', $env['steadfast_api_key'] ?? '');
define('STEADFAST_SECRET_KEY', $env['steadfast_secret_key'] ?? '');
define('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1');

// === HOOKS ===
add_filter('woocommerce_shop_order_list_table_columns', 'courier_check_add_column');
add_action('woocommerce_shop_order_list_table_custom_column', 'courier_check_render_column', 10, 2);
add_filter('manage_edit-shop_order_columns', 'courier_check_add_column');
add_action('manage_shop_order_posts_custom_column', 'courier_check_render_column_classic', 10, 2);
add_action('admin_footer', 'courier_check_modal_css');
add_action('add_meta_boxes', 'courier_check_add_meta_box');
add_filter('wc_order_is_editable', 'order_status_editable', 9999, 2);
add_action('wp_ajax_send_to_steadfast', 'send_order_to_steadfast');
add_action('rest_api_init', 'register_steadfast_webhook_endpoint'); // New hook for webhook

// === FUNCTIONS ===
// Add courier check column to order table
function courier_check_add_column($columns) {
    $new_columns = [];
    $inserted = false;
    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'status') {
            $new_columns['courier_check'] = __('Courier Check', 'shop-theme');
            $new_columns['steadfast'] = __('SteadFast', 'shop-theme');
            $inserted = true;
        }
    }
    if (!$inserted) {
        $new_columns['courier_check'] = __('Courier Check', 'shop-theme');
        $new_columns['steadfast'] = __('SteadFast', 'shop-theme');
    }
    return $new_columns;
}

// Render courier check in new order table
function courier_check_render_column($column, $order) {
    if ($column === 'courier_check') {
        courier_check_render_courier_check($order->get_id(), $order);
    } elseif ($column === 'steadfast') {
        render_steadfast_column($order->get_id(), $order);
    }
}

// Render courier check in classic order table
function courier_check_render_column_classic($column, $post_id) {
    if ($column === 'courier_check') {
        $order = wc_get_order($post_id);
        if ($order) {
            courier_check_render_courier_check($post_id, $order);
        }
    } elseif ($column === 'steadfast') {
        $order = wc_get_order($post_id);
        if ($order) {
            render_steadfast_column($post_id, $order);
        }
    }
}

// Get courier data
function courier_check_get_courier_data($order) {
    $meta_key = '_courier_check_data';
    $data = $order->get_meta($meta_key, true);
    if (!$data) {
        $phone = $order->get_billing_phone();
        if (!$phone) return [];
        $response = wp_remote_post(COURIER_API_URL . '?phone=' . urlencode($phone), [
            'headers' => [
                'Authorization' => 'Bearer ' . COURIER_API_KEY,
            ],
            'timeout' => 10,
        ]);
        if (is_wp_error($response)) return [];
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!$data || !is_array($data)) return [];
        $order->update_meta_data($meta_key, $data);
        $order->save();
    }
    return $data;
}

// Get risk level
function courier_check_get_risk($courierData) {
    if (empty($courierData) || !is_array($courierData)) {
        return ['New Customer', 'risk-new'];
    }

    $risk = 'Low Risk';
    $risk_class = 'risk-low';
    $has_history = false;
    $all_zero = true;

    foreach ($courierData as $key => $value) {
        if ($key === 'summary') continue;
        $success = isset($value['success_parcel']) ? (int)$value['success_parcel'] : 0;
        // $cancelled = isset($value['cancelled_parcel']) ? (int)$value['cancelled_parcel'] : 0;
        $cancelled = 13;
        $success_ratio = isset($value['success_ratio']) ? (float)$value['success_ratio'] : 0;
        if ($success > 0 || $cancelled > 0) {
            $all_zero = false;
            $has_history = true;
        }
        if ($cancelled > 0 && $success_ratio < 70) {
            return ['High Risk', 'risk-high'];
        } elseif ($cancelled > 0 || $success_ratio < 90) {
            $risk = 'Medium Risk';
            $risk_class = 'risk-medium';
        }
    }

    if ($all_zero) {
        return ['New Customer', 'risk-new'];
    }

    if (!$has_history) {
        return ['New Customer', 'risk-new'];
    }

    return [$risk, $risk_class];
}

// Render courier check
function courier_check_render_courier_check($order_id, $order) {
    $data = courier_check_get_courier_data($order);
    $courierData = isset($data['courierData']) && is_array($data['courierData']) ? $data['courierData'] : [];
    $summary = isset($courierData['summary']) ? $courierData['summary'] : null;
    list($risk, $risk_class) = courier_check_get_risk($courierData);
    ?>
    <span class="courier-risk-label <?php echo esc_attr($risk_class); ?>"
          style="cursor:pointer;padding:2px 8px;border-radius:4px;background:#eee;"
          onclick="event.stopPropagation();document.getElementById('courier-modal-<?php echo $order_id; ?>').style.display='block'">
        <?php echo esc_html($risk); ?>
    </span>
    <div id="courier-modal-<?php echo $order_id; ?>" class="courier-modal"
         style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);"
         onclick="event.stopPropagation();this.style.display='none';" tabindex="-1" role="dialog">
        <div class="courier-modal-content" style="background:#fff;max-width:500px;margin:5vh auto;padding:20px;position:relative;border-radius:8px;"
             onclick="event.stopPropagation();">
            <button onclick="event.preventDefault();event.stopPropagation();document.getElementById('courier-modal-<?php echo $order_id; ?>').style.display='none'"
                    style="position:absolute;top:10px;right:10px;font-size:20px;background:none;border:none;cursor:pointer;">×</button>
            <h3 style="margin-top:0;">Courier Check Details</h3>
            <?php
            echo '<table class="courier-table"><tr><th>Courier</th><th>Success</th><th>Cancelled</th><th>Success Ratio</th></tr>';
            foreach ($courierData as $key => $value) {
                if ($key === 'summary') continue;
                echo '<tr>';
                echo '<td>';
                if (!empty($value['logo'])) {
                    echo '<img src="' . esc_url($value['logo']) . '" alt="" style="height:18px;vertical-align:middle;margin-right:4px;"> ';
                }
                echo '</td>';
                echo '<td>' . esc_html($value['success_parcel'] ?? '-') . '</td>';
                echo '<td>' . esc_html($value['cancelled_parcel'] ?? '-') . '</td>';
                echo '<td>' . esc_html(isset($value['success_ratio']) ? $value['success_ratio'] . '%' : '-') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            if ($summary) {
                printf(
                    '<p style="margin-top:10px;"><b>%s</b> %s | <b>%s</b> %s | <b>%s</b> %s | <b>%s</b> %s%%</p>',
                    __('Total Parcels:', 'shop-theme'), esc_html($summary['total_parcel']),
                    __('Success:', 'shop-theme'), esc_html($summary['success_parcel']),
                    __('Cancelled:', 'shop-theme'), esc_html($summary['cancelled_parcel']),
                    __('Success Ratio:', 'shop-theme'), esc_html($summary['success_ratio'])
                );
            }
            ?>
        </div>
    </div>
    <?php
}

// CSS for modal and status display
function courier_check_modal_css() {
    ?>
    <style>
    .courier-modal .courier-modal-content {
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        animation: courierModalFadeIn 0.2s;
    }
    @keyframes courierModalFadeIn {
        from { transform: translateY(40px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .courier-risk-label { font-weight: 600; }
    .risk-high { background: #ffdddd !important; color: #a00; }
    .risk-medium { background: #fff5cc !important; color: #a67c00; }
    .risk-low { background: #ddffdd !important; color: #008a00; }
    .risk-new { background: #e6f3ff !important; color: #0066cc; }
    .courier-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .courier-table th, .courier-table td { border: 1px solid #eee; padding: 6px 8px; text-align: left; }
    .courier-table th { background: #f8f8f8; }
    .steadfast-status { display: block; font-size: 12px; color: #555; margin-top: 4px; }
    </style>
    <?php
}

// Add meta box
function courier_check_add_meta_box() {
    add_meta_box(
        'courier_check_meta_box',
        __('Courier Check', 'shop-theme'),
        'courier_check_render_courier_check_meta_box',
        'shop_order',
        'advanced',
        'high'
    );
    add_meta_box(
        'courier_check_order_details',
        __('Courier Check', 'shop-theme'),
        'courier_check_render_courier_check_table_only',
        null,
        'side',
        'core'
    );
}

// Render meta box
function courier_check_render_courier_check_meta_box($post) {
    $order = wc_get_order($post->ID);
    if ($order) {
        courier_check_render_courier_check($order->get_id(), $order);
    }
}

// Render sidebar meta box table
function courier_check_render_courier_check_table_only($post) {
    $order = wc_get_order($post->ID);
    if ($order) {
        $data = courier_check_get_courier_data($order);
        $courierData = isset($data['courierData']) && is_array($data['courierData']) ? $data['courierData'] : [];
        $summary = isset($courierData['summary']) ? $courierData['summary'] : null;
        echo '<table class="courier-table" style="font-size:12px;width:100%;border-collapse:collapse;">';
        echo '<tr><th>Courier</th><th>S.</th><th>C.</th><th>S.R.</th></tr>';
        foreach ($courierData as $key => $value) {
            if ($key === 'summary') continue;
            echo '<tr>';
            echo '<td>';
            if (!empty($value['logo'])) {
                echo '<img src="' . esc_url($value['logo']) . '" alt="" style="height:14px;vertical-align:middle;margin-right:3px;"> ';
            }
            echo '</td>';
            echo '<td>' . esc_html($value['success_parcel'] ?? '-') . '</td>';
            echo '<td>' . esc_html($value['cancelled_parcel'] ?? '-') . '</td>';
            echo '<td>' . esc_html(isset($value['success_ratio']) ? $value['success_ratio'] . '%' : '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        if ($summary) {
            printf(
                '<p style="margin-top:8px;font-size:11px;"><b>%s</b> %s | <b>%s</b> %s | <b>%s</b> %s | <b>%s</b> %s%%</p>',
                __('Total Parcels:', 'shop-theme'), esc_html($summary['total_parcel']),
                __('Success:', 'shop-theme'), esc_html($summary['success_parcel']),
                __('Cancelled:', 'shop-theme'), esc_html($summary['cancelled_parcel']),
                __('Success Ratio:', 'shop-theme'), esc_html($summary['success_ratio'])
            );
        }
    }
}

// Render SteadFast column with delivery status
function render_steadfast_column($order_id, $order) {
    $consignment_id = $order->get_meta('_steadfast_consignment_id');
    $delivery_status = $order->get_meta('_steadfast_delivery_status');
    if ($consignment_id) {
        echo '<span class="steadfast-consignment" style="color: #008a00;">' . esc_html($consignment_id) . '</span>';
        if ($delivery_status) {
            echo '<span class="steadfast-status">' . esc_html($delivery_status) . '</span>';
        }
    } else {
        echo '<button class="button send-to-steadfast" data-order-id="' . esc_attr($order_id) . '" style="padding: 2px 8px;">Send</button>';
    }
}

// Send order to SteadFast
function send_order_to_steadfast() {
    if (!current_user_can('edit_shop_orders')) {
        wp_send_json_error(['message' => 'Permission denied']);
        return;
    }

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    if (!$order_id) {
        wp_send_json_error(['message' => 'Invalid order ID']);
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => 'Order not found']);
        return;
    }

    // Prepare SteadFast API data according to documentation
    $steadfast_data = [
        'invoice' => $order->get_order_number(),
        'recipient_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
        'recipient_phone' => $order->get_billing_phone(),
        'recipient_address' => $order->get_shipping_address_1(),
        'cod_amount' => $order->get_total(),
        'note' => $order->get_customer_note(),
        'item_description' => get_order_items_description($order)
    ];

    // Log the request data for debugging
    error_log('SteadFast API Request for Order #' . $order_id . ': ' . json_encode($steadfast_data));

    $url = STEADFAST_BASE_URL . '/create_order';
    $max_attempts = 3;
    $delay = 2; // seconds

    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($steadfast_data),
            CURLOPT_HTTPHEADER => [
                'Api-Key: ' . STEADFAST_API_KEY,
                'Secret-Key: ' . STEADFAST_SECRET_KEY,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT => 'WordPress/' . get_bloginfo('version'),
            CURLOPT_VERBOSE => true
        ]);

        // Create a temporary file handle for CURL debug output
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);

        // Get debug information
        rewind($verbose);
        $verbose_log = stream_get_contents($verbose);

        // Log the complete request and response
        error_log('SteadFast API Debug for Order #' . $order_id . ' (Attempt #' . $attempt . '): ' . $verbose_log);
        error_log('SteadFast API Response Code for Order #' . $order_id . ' (Attempt #' . $attempt . '): ' . $http_code);
        error_log('SteadFast API Response Body for Order #' . $order_id . ' (Attempt #' . $attempt . '): ' . $response);

        curl_close($ch);

        if ($response && $http_code == 200) {
            $data = json_decode($response, true);
            if (isset($data['consignment']['consignment_id'])) {
                $order->update_meta_data('_steadfast_consignment_id', $data['consignment']['consignment_id']);
                $order->save();
                wp_send_json_success([
                    'consignment_id' => $data['consignment']['consignment_id'],
                    'message' => 'Order sent successfully'
                ]);
                return;
            }
        }

        if ($attempt < $max_attempts) {
            sleep($delay); // Wait before retrying
        } else {
            $error_message = $curl_error ? "cURL Error: $curl_error" : "HTTP Error: $http_code";
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['message'])) {
                    $error_message = $data['message'];
                }
            }
            wp_send_json_error(['message' => "Failed after $max_attempts attempts. $error_message"]);
        }
    }
}

// Get order items description
function get_order_items_description($order) {
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = $item->get_name() . ' x ' . $item->get_quantity();
    }
    return implode(', ', $items);
}

// Add JavaScript for SteadFast integration
add_action('admin_footer', 'add_steadfast_scripts');
function add_steadfast_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.send-to-steadfast').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = $(this);
            const orderId = button.data('order-id');
            
            button.prop('disabled', true).text('Sending...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'send_to_steadfast',
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success && response.data && response.data.consignment_id) {
                        const consignmentId = response.data.consignment_id;
                        const statusHtml = '<span class="steadfast-consignment" style="color: #008a00;">' + consignmentId + '</span>';
                        button.replaceWith(statusHtml);
                    } else {
                        const errorMessage = response.data && response.data.message ? response.data.message : 'Unknown error occurred';
                        console.error('SteadFast API Error:', errorMessage);
                        alert('Error: ' + errorMessage);
                        button.prop('disabled', false).text('Retry');
                    }
                },
                error: function(xhr, status, error) {
                    const errorMessage = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message 
                        ? xhr.responseJSON.data.message 
                        : 'Failed to connect to SteadFast: ' + error;
                    console.error('SteadFast AJAX Error:', errorMessage);
                    alert('Error: ' + errorMessage);
                    button.prop('disabled', false).text('Retry');
                }
            });
        });
    });
    </script>
    <?php
}

// Make order status editable
function order_status_editable($allow_edit, $order) {
    if ($order->get_status() === 'processing') {
        $allow_edit = true;
    }
    return $allow_edit;
}

// Register the SteadFast webhook endpoint
function register_steadfast_webhook_endpoint() {
    register_rest_route('steadfast/v1', '/delivery-status', [
        'methods' => 'POST',
        'callback' => 'handle_steadfast_webhook',
        'permission_callback' => function () {
            $auth_header = sanitize_text_field($_SERVER['HTTP_AUTHORIZATION'] ?? '');
            $expected_auth = 'Bearer ' . STEADFAST_API_KEY;
            return $auth_header === $expected_auth; // Validate API key
        },
    ]);
}

// Handle the SteadFast webhook
function handle_steadfast_webhook($request) {
    $body = $request->get_body();
    $data = json_decode($body, true);

    // Validate payload
    if (!$data || !isset($data['notification_type']) || $data['notification_type'] !== 'delivery_status') {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Invalid webhook payload'
        ], 400);
    }

    $consignment_id = isset($data['consignment_id']) ? intval($data['consignment_id']) : 0;
    $status = isset($data['status']) ? sanitize_text_field($data['status']) : '';

    if (!$consignment_id || !$status) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Missing consignment_id or status'
        ], 400);
    }

    // Find the order
    $args = [
        'post_type' => 'shop_order',
        'post_status' => 'any',
        'meta_query' => [
            [
                'key' => '_steadfast_consignment_id',
                'value' => $consignment_id,
                'compare' => '=',
            ],
        ],
        'limit' => 1,
    ];

    $orders = wc_get_orders($args);
    if (empty($orders)) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Order not found for consignment ID: ' . $consignment_id
        ], 404);
    }

    $order = $orders[0];
    $order->update_meta_data('_steadfast_delivery_status', $status);
    $order->save();

    // Log for debugging
    error_log('SteadFast Webhook: Order #' . $order->get_id() . ' updated to ' . $status);

    return new WP_REST_Response([
        'status' => 'success',
        'message' => 'Webhook received successfully'
    ], 200);
}