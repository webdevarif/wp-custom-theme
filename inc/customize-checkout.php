<?php
// Remove country, address_2, postcode, city, order notes
add_filter('woocommerce_checkout_fields', function($fields) {
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_city']);
    unset($fields['order']['order_comments']);
    // Make email optional
    if (isset($fields['billing']['billing_email'])) {
        $fields['billing']['billing_email']['required'] = false;
    }
    // Make phone required
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['required'] = true;
    }
    return $fields;
});

// Remove order notes field from checkout form
add_filter('woocommerce_enable_order_notes_field', '__return_false');

// Move 'Your Order' to the right and payment gateway to the left using hooks and CSS
add_action('wp_enqueue_scripts', function() {
    // if (is_checkout()) {
    //     wp_add_inline_style('woocommerce-inline', ' \
    //     @media (min-width: 900px) {
    //         .woocommerce-checkout .col2-set { display: flex; flex-direction: row; gap: 40px; }
    //         .woocommerce-checkout .col2-set .col-1 { width: 50%; order: 1; }
    //         .woocommerce-checkout .col2-set .col-2 { width: 50%; order: 2; }
    //         .woocommerce-checkout-review-order { float: right; width: 48%; margin-left: 2%; }
    //         .woocommerce-checkout-payment { float: left; width: 48%; margin-right: 2%; }
    //     }
    //     .woocommerce-additional-fields, .woocommerce-additional-fields__field-wrapper { display: none !important; }
    //     ');
    // }
});

// AJAX endpoint for phone risk check
add_action('wp_ajax_nopriv_checkout_phone_risk_check', 'custom_checkout_phone_risk_check');
add_action('wp_ajax_checkout_phone_risk_check', 'custom_checkout_phone_risk_check');
function custom_checkout_phone_risk_check() {
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    if (!$phone) {
        wp_send_json_error(['message' => 'ফোন নম্বর প্রদান করা হয়নি।']);
    }
    // Use the same logic as order-auditional.php
    if (!function_exists('courier_check_get_risk')) {
        require_once get_template_directory() . '/inc/order-auditional.php';
    }
    // Use user-blocking.php for blocking logic
    if (!function_exists('is_recent_order_blocked')) {
        require_once get_template_directory() . '/inc/user-blocking.php';
    }
    $data = [];
    // Call courier API (reuse logic from order-auditional.php)
    $response = wp_remote_post(COURIER_API_URL . '?phone=' . urlencode($phone), [
        'headers' => [
            'Authorization' => 'Bearer ' . COURIER_API_KEY,
        ],
        'timeout' => 10,
    ]);
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'ফোন নম্বর যাচাই করা যায়নি।']);
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    $courierData = isset($data['courierData']) && is_array($data['courierData']) ? $data['courierData'] : [];
    list($risk, $risk_class) = courier_check_get_risk($courierData);

    // Use reusable blocking functions
    list($recent_blocked, $recent_msg, $recent_type) = is_recent_order_blocked($phone);
    list($return_blocked, $return_msg, $return_type) = is_high_return_blocked($courierData);

    $result = [
        'risk' => $risk,
        'risk_class' => $risk_class,
    ];
    if ($recent_blocked) {
        $result['message'] = $recent_msg;
        $result['type'] = 'error';
        $result['block_type'] = $recent_type;
    } elseif ($return_blocked) {
        $result['message'] = $return_msg;
        $result['type'] = 'error';
        $result['block_type'] = $return_type;
    } elseif ($risk === 'High Risk') {
        $result['message'] = 'দুঃখিত, আপনার অর্ডার রেট ভালো নয়, তাই আপনি এখানে অর্ডার করতে পারবেন না। অনুগ্রহ করে WhatsApp-এ আমাদের সাথে যোগাযোগ করুন।';
        $result['type'] = 'error';
        $result['block_type'] = 'high_risk';
    } else {
        $result['message'] = 'দারুন! এই নম্বর দিয়ে আপনি অর্ডার করতে পারবেন।';
        $result['type'] = 'success';
        $result['block_type'] = '';
    }
    wp_send_json_success($result);
}

// Add JS to checkout page for phone risk check
add_action('wp_footer', function() {
    if (!is_checkout()) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function getPhoneField() {
            return document.querySelector('input[name="billing_phone"]');
        }
        function ensureRiskInfo() {
            let phoneField = getPhoneField();
            if (!phoneField) return null;
            let riskInfo = document.getElementById('phone-risk-info');
            if (!riskInfo) {
                riskInfo = document.createElement('div');
                riskInfo.id = 'phone-risk-info';
                riskInfo.className = '';
                phoneField.parentNode.appendChild(riskInfo);
            }
            return riskInfo;
        }
        function getPlaceOrderBtn() {
            return document.querySelector('#place_order, button[name="woocommerce_checkout_place_order"]');
        }
        function showSpinner() {
            let riskInfo = ensureRiskInfo();
            if (riskInfo) {
                riskInfo.innerHTML = `<div class="flex items-center gap-2 mt-2 p-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 text-base font-medium"><svg class='animate-spin h-5 w-5 text-blue-400' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'><circle class='opacity-25' cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4'></circle><path class='opacity-75' fill='currentColor' d='M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z'></path></svg> পরীক্ষা করা হচ্ছে...</div>`;
            }
        }
        function showRiskResult(type, message, risk, blockType) {
            let riskInfo = ensureRiskInfo();
            let btn = getPlaceOrderBtn();
            let icon = type === 'success'
                ? `<span class='inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-100 text-green-600 mr-2'><svg class='h-4 w-4' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7'/></svg></span>`
                : `<span class='inline-flex items-center justify-center h-6 w-6 rounded-full bg-red-100 text-red-600 mr-2'><svg class='h-4 w-4' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12'/></svg></span>`;
            let boxClass = type === 'success'
                ? 'flex items-center gap-2 mt-2 p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 text-base font-medium'
                : 'flex flex-col sm:flex-row items-start sm:items-start gap-2 mt-2 p-3 rounded-lg border border-red-200 bg-red-50 text-red-700 text-base font-medium';
            let whatsapp = '';
            if (blockType === 'high_risk') {
                whatsapp = `<a href='https://wa.me/8801886666039' target='_blank' class='inline-flex items-center gap-2 mt-2 sm:mt-0 text-green-600 hover:text-green-700 font-semibold underline'><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M260.062 32C138.605 32 40.134 129.701 40.134 250.232c0 41.23 11.532 79.79 31.559 112.687L32 480l121.764-38.682c31.508 17.285 67.745 27.146 106.298 27.146C381.535 468.464 480 370.749 480 250.232 480 129.701 381.535 32 260.062 32zm109.362 301.11c-5.174 12.827-28.574 24.533-38.899 25.072-10.314.547-10.608 7.994-66.84-16.434-56.225-24.434-90.052-83.844-92.719-87.67-2.669-3.812-21.78-31.047-20.749-58.455 1.038-27.413 16.047-40.346 21.404-45.725 5.351-5.387 11.486-6.352 15.232-6.413 4.428-.072 7.296-.132 10.573-.011 3.274.124 8.192-.685 12.45 10.639 4.256 11.323 14.443 39.153 15.746 41.989 1.302 2.839 2.108 6.126.102 9.771-2.012 3.653-3.042 5.935-5.961 9.083-2.935 3.148-6.174 7.042-8.792 9.449-2.92 2.665-5.97 5.572-2.9 11.269 3.068 5.693 13.653 24.356 29.779 39.736 20.725 19.771 38.598 26.329 44.098 29.317 5.515 3.004 8.806 2.67 12.226-.929 3.404-3.599 14.639-15.746 18.596-21.169 3.955-5.438 7.661-4.373 12.742-2.329 5.078 2.052 32.157 16.556 37.673 19.551 5.51 2.989 9.193 4.529 10.51 6.9 1.317 2.38.901 13.531-4.271 26.359z"></path></svg><span>WhatsApp-এ যোগাযোগ করুন</span></a>`;
            }
            if (riskInfo) {
                riskInfo.innerHTML = `<div class='${boxClass}'>${icon}<span class='flex-1'>${message} <br />${whatsapp}</span></div>`;
            }
            if (btn) {
                if (type === 'error') {
                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            }
        }
        // document.body.addEventListener('blur', function(e) {
        //     if (e.target && e.target.name === 'billing_phone') {
        //         let phone = e.target.value.trim();
        //         if (phone.length < 6) return; // skip short/empty
        //         showSpinner();
        //         fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        //             method: 'POST',
        //             headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        //             body: new URLSearchParams({
        //                 action: 'checkout_phone_risk_check',
        //                 phone: phone
        //             })
        //         })
        //         .then(r => r.json())
        //         .then(data => {
        //             if (data.success && data.data) {
        //                 showRiskResult(data.data.type, data.data.message, data.data.risk, data.data.block_type);
        //             } else {
        //                 showRiskResult('error', 'ফোন নম্বর যাচাই করা যায়নি।', '', '');
        //             }
        //         })
        //         .catch(() => {
        //             showRiskResult('error', 'ফোন নম্বর যাচাই করা যায়নি।', '', '');
        //         });
        //     }
        // }, true);
    });
    </script>
    <?php
});
