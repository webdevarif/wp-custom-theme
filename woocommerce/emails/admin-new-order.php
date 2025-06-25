<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails\HTML
 * @version 9.8.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

$order = $args['order'] ?? $order ?? false;
if ( ! $order ) return;

// Helper: Bangla date
function get_bangla_date($date) {
    $bn = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
    $en = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    return str_replace($bn, $en, date_i18n('j F, Y', strtotime($date)));
}

// Get order data for custom sections
$order_number = $order->get_order_number();
$order_date = get_bangla_date($order->get_date_created());
$customer_name = $order->get_formatted_billing_full_name();
$customer_address = $order->get_billing_address_1();
$customer_phone = $order->get_billing_phone();
$payment_method = $order->get_payment_method_title();
$subtotal = $order->get_subtotal();
$total = $order->get_total();
$shipping = $order->get_shipping_total();

// --- DYNAMIC FRAUD CHECK ---
$fraud_api_url = 'https://portal.packzy.com/api/v1/fraud_check/' . urlencode($customer_phone);
$fraud_data = null;
try {
    $args = [
        'timeout' => 10,
    ];
    $response = wp_remote_get($fraud_api_url, $args);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
        $body = wp_remote_retrieve_body($response);
        error_log('Fraud API raw response: ' . $body); // Debug
        $fraud_data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
        }
    } else {
        error_log('Fraud API error: ' . print_r($response, true));
    }
} catch (Exception $e) {
    $fraud_data = null;
    error_log('Fraud API exception: ' . $e->getMessage());
}
$total_delivered = $fraud_data['total_delivered'] ?? 0;
$total_cancelled = $fraud_data['total_cancelled'] ?? 0;
$total_orders = $fraud_data['total_parcels'] ?? 0;
$success_rate = $total_orders > 0 ? round(($total_delivered / $total_orders) * 100, 1) : 0;
$frauds = $fraud_data['frauds'] ?? [];

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
echo $email_improvements_enabled ? '<div class="email-introduction">' : '';
/* translators: %s: Customer billing full name */
$text = __( 'You\'ve received the following order from %s:', 'woocommerce' );
if ( $email_improvements_enabled ) {
        /* translators: %s: Customer billing full name */
        $text = __( 'You\'ve received a new order from %s:', 'woocommerce' );
}
?>
<p><?php printf( esc_html( $text ), esc_html( $order->get_formatted_billing_full_name() ) ); ?></p>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

// Custom Bangla sections - only show if not using email improvements
if ( ! $email_improvements_enabled ) : ?>
    
    <!-- Custom Order Info Section -->
    <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #128C7E;">
        <h3 style="color: #128C7E; margin: 0 0 10px 0;">অর্ডারের বিবরণ</h3>
        <p><strong>অর্ডার নম্বর:</strong> #<?php echo esc_html($order_number); ?></p>
        <p><strong>অর্ডারের তারিখ:</strong> <?php echo esc_html($order_date); ?></p>
        <p><strong>পেমেন্ট পদ্ধতি:</strong> <?php echo esc_html($payment_method); ?></p>
        <?php
        $billing_size = $order->get_meta('billing_size');
        if (!$billing_size) {
            // Fallback: try to find any meta key containing 'billing_size'
            foreach ($order->get_meta_data() as $meta) {
                if (strpos($meta->key, 'billing_size') !== false && !empty($meta->value)) {
                    $billing_size = $meta->value;
                    break;
                }
            }
        }
        if ($billing_size): ?>
        <p><strong>কোমরের সাইজ সিলেক্ট করুন:</strong> <?php echo esc_html($billing_size); ?></p>
        <?php endif; ?>
    </div>

    <!-- Custom Customer History Section -->
    <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #128C7E;">
        <h3 style="color: #128C7E; margin: 0 0 10px 0;">গ্রাহকের ইতিহাস</h3>
        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 1; text-align: center; padding: 10px; background-color: #f9f9f9; border-radius: 5px;">
                <div style="font-size: 12px; color: #666;">মোট অর্ডার</div>
                <div style="font-size: 20px; font-weight: bold;"><?php echo $total_orders; ?></div>
            </div>
            <div style="flex: 1; text-align: center; padding: 10px; background-color: #e8f5e9; border-radius: 5px; border-bottom: 3px solid #4caf50;">
                <div style="font-size: 12px; color: #666;">ডেলিভারি সম্পন্ন</div>
                <div style="font-size: 20px; font-weight: bold;"><?php echo $total_delivered; ?></div>
            </div>
            <div style="flex: 1; text-align: center; padding: 10px; background-color: #ffebee; border-radius: 5px; border-bottom: 3px solid #f44336;">
                <div style="font-size: 12px; color: #666;">বাতিল করা</div>
                <div style="font-size: 20px; font-weight: bold;"><?php echo $total_cancelled; ?></div>
            </div>
        </div>
        <p><strong>সাফল্যের হার:</strong> <?php echo $success_rate; ?>%</p>
        <div style="background-color: #e0e0e0; border-radius: 5px; height: 10px; overflow: hidden;">
            <div style="background: linear-gradient(90deg, #4caf50, #8bc34a); height: 100%; width: <?php echo esc_attr($success_rate); ?>%;"></div>
        </div>
    </div>

    <!-- Custom Fraud Alert Section -->
    <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #128C7E;">
        <h3 style="color: #128C7E; margin: 0 0 10px 0;">সম্ভাব্য প্রতারণা চেক</h3>
        <?php if (!empty($frauds)) : ?>
            <?php foreach (array_slice($frauds, 0, 5) as $fraud) : ?>
                <div style="background-color: #ffebee; border-left: 4px solid #f44336; padding: 10px; margin-bottom: 10px;">
                    <p style="margin: 0 0 5px 0;"><strong>নাম:</strong> <?php echo esc_html($fraud['name']); ?></p>
                    <p style="margin: 0;"><strong>বিবরণ:</strong> <?php echo nl2br(esc_html($fraud['details'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 10px;">
                <p style="margin: 0; color: #388e3c; font-weight: 600;">কোনো প্রতারণার রিপোর্ট পাওয়া যায়নি!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Congratulations Note -->
    <div style="margin: 20px 0; padding: 15px; background-color: #fff8e1; border-left: 4px solid #ffc107;">
        <p style="margin: 0;">বিক্রয়ের জন্য অভিনন্দন! এই অর্ডারটি সফলভাবে প্রক্রিয়া করার জন্য অনুগ্রহ করে প্রয়োজনীয় পদক্ষেপ নিন।</p>
    </div>

<?php endif; ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
        echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td class="email-additional-content">' : '';
        echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
        echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );