<?php

// === CONFIG ===
$env = file_exists(__DIR__ . '/../env.php') ? include __DIR__ . '/../env.php' : [];
define('COURIER_API_KEY', $env['bdcourier_api_key'] ?? '');
define('COURIER_API_URL', $env['bdcourier_api_url'] ?? '');

// === HOOKS ===
add_filter('woocommerce_shop_order_list_table_columns', 'courier_check_add_column');
add_action('woocommerce_shop_order_list_table_custom_column', 'courier_check_render_column', 10, 2);
add_filter('manage_edit-shop_order_columns', 'courier_check_add_column');
add_action('manage_shop_order_posts_custom_column', 'courier_check_render_column_classic', 10, 2);
add_action('admin_footer', 'courier_check_modal_css');
add_action('add_meta_boxes', 'courier_check_add_meta_box');
add_filter( 'wc_order_is_editable', 'order_status_editable', 9999, 2 );

// === FUNCTIONS ===
// This is the function that will be used to add the courier check column to the order table
function courier_check_add_column($columns) {
    $new_columns = [];
    $inserted = false;
    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'status') {
            $new_columns['courier_check'] = __('Courier Check', 'shop-theme');
            $inserted = true;
        }
    }
    if (!$inserted) {
        $new_columns['courier_check'] = __('Courier Check', 'shop-theme');
    }
    return $new_columns;
}

// This is the function that will be used to render the courier check in the new order table
function courier_check_render_column($column, $order) {
    if ($column === 'courier_check') {
        courier_check_render_courier_check($order->get_id(), $order);
    }
}

// This is the function that will be used to render the courier check in the classic order table
function courier_check_render_column_classic($column, $post_id) {
    if ($column === 'courier_check') {
        $order = wc_get_order($post_id);
        if ($order) {
            courier_check_render_courier_check($post_id, $order);
        }
    }
}

// This is the function that will be used to get the courier data
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

// This is the function that will be used to get the risk
function courier_check_get_risk($courierData) {
    $risk = 'Low Risk';
    $risk_class = 'risk-low';
    foreach ($courierData as $key => $value) {
        if ($key === 'summary') continue;
        $cancelled = isset($value['cancelled_parcel']) ? (int)$value['cancelled_parcel'] : 0;
        $success_ratio = isset($value['success_ratio']) ? (float)$value['success_ratio'] : 0;
        if ($cancelled > 0 && $success_ratio < 70) {
            return ['High Risk', 'risk-high'];
        } elseif ($cancelled > 0 || $success_ratio < 90) {
            $risk = 'Medium Risk';
            $risk_class = 'risk-medium';
        }
    }
    return [$risk, $risk_class];
}

// This is the function that will be used to render the courier check
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
                    style="position:absolute;top:10px;right:10px;font-size:20px;background:none;border:none;cursor:pointer;">&times;</button>
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
                // echo esc_html($value['name'] ?? $key);
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

// This is the CSS for the modal
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
    .courier-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .courier-table th, .courier-table td { border: 1px solid #eee; padding: 6px 8px; text-align: left; }
    .courier-table th { background: #f8f8f8; }
    </style>
    <?php
}

// This is the meta box that will be displayed in the order page
function courier_check_add_meta_box() {
    add_meta_box(
        'courier_check_meta_box',
        __('Courier Check', 'shop-theme'),
        'courier_check_render_courier_check_meta_box',
        'shop_order',
        'advanced',
        'high'
    );
    
    // Add a sample Hello World meta box for testing
    add_meta_box(
        'courier_check_order_details',
        __('Courier Check', 'shop-theme'),
        // function() { echo '<div style="padding:10px;font-weight:bold;color:red;">Order Details Meta Box!</div>'; },
        'courier_check_render_courier_check_table_only',
        null, // null = all post types
        'side',
        'core'
    );
}

// This is the meta box that will be displayed in the order page
function courier_check_render_courier_check_meta_box($post) {
    $order = wc_get_order($post->ID);
    if ($order) {
        courier_check_render_courier_check($order->get_id(), $order);
    }
}

// This is the table that will be displayed in the sidebar meta box
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
            // echo esc_html($value['name'] ?? $key);
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


// This is the function that will be used to make the order status editable
function order_status_editable( $allow_edit, $order ) {
    if ( $order->get_status() === 'processing' ) {
        $allow_edit = true;
    }
    return $allow_edit;
}