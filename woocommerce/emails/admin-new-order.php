<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * @author WooThemes
 * @package WooCommerce/Templates/Emails
 * @version 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order = $args['order'] ?? $order ?? false;
if ( ! $order ) return;

// Helper: Bangla date
function get_bangla_date($date) {
    $bn = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
    $en = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    return str_replace($bn, $en, date_i18n('j F, Y', strtotime($date)));
}
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
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন অর্ডার বিজ্ঞপ্তি</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333333; background-color: #f5f5f5; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="650" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #128C7E, #075E54); color: #ffffff; padding: 20px; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0; text-align: center; color: #fff; font-size: 24px; font-weight: 700;">নতুন অর্ডার বিজ্ঞপ্তি</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <p style="margin: 5px 0 0; font-size: 16px;">
                                            অর্ডার #<?php echo esc_html($order_number); ?> | <?php echo esc_html($order_date); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <!-- Intro -->
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p style="margin: 0;">আপনি <strong><?php echo esc_html($customer_name); ?></strong> থেকে একটি অর্ডার পেয়েছেন। অর্ডারের বিবরণ নিম্নরূপ:</p>
                                    </td>
                                </tr>
                                
                                <!-- Order Details Section -->
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 18px; font-weight: 600; color: #128C7E; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;">
                                                    <span style="font-weight: bold;">অর্ডারের বিবরণ</span>
                                                </td>
                                            </tr>
                                            
                                            <!-- Order Info Box -->
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9; border-left: 4px solid #128C7E; padding: 15px; margin-bottom: 20px;">
                                                        <tr>
                                                            <td style="padding: 5px 0;">
                                                                <span style="color: #128C7E; font-weight: 600;">অর্ডার নম্বর:</span> #<?php echo esc_html($order_number); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 5px 0;">
                                                                <span style="color: #128C7E; font-weight: 600;">অর্ডারের তারিখ:</span> <?php echo esc_html($order_date); ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            
                                            <!-- Product Table -->
                                            <tr>
                                                <td style="padding-top: 10px;">
                                                    <table width="100%" cellpadding="10" cellspacing="0" border="0" style="border-collapse: collapse;">
                                                        <tr>
                                                            <th style="background-color: #f2f2f2; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; width: 50%;">পণ্য</th>
                                                            <th style="background-color: #f2f2f2; text-align: center; font-weight: 600; border-bottom: 2px solid #e0e0e0; width: 25%;">পরিমাণ</th>
                                                            <th style="background-color: #f2f2f2; text-align: right; font-weight: 600; border-bottom: 2px solid #e0e0e0; width: 25%;">মূল্য</th>
                                                        </tr>
                                                        <?php foreach ( $order->get_items() as $item ) : ?>
                                                        <tr>
                                                            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;"><?php echo esc_html( $item->get_name() ); ?></td>
                                                            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;"><?php echo esc_html( $item->get_quantity() ); ?></td>
                                                            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;"><?php echo wc_price( $item->get_total() ); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            
                                            <!-- Order Total -->
                                            <tr>
                                                <td style="padding-top: 10px;">
                                                    <table width="100%" cellpadding="5" cellspacing="0" border="0" style="background-color: #f9f9f9; padding: 15px;">
                                                        <tr>
                                                            <td style="text-align: right;">সাবটোটাল:</td>
                                                            <td width="120" style="text-align: right;"><?php echo wc_price($subtotal); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: right;">শিপিং:</td>
                                                            <td style="text-align: right;"><?php echo $shipping == 0 ? 'ফ্রী ডেলিভারি' : wc_price($shipping); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: right; font-weight: 700; font-size: 18px; color: #128C7E; padding-top: 10px; border-top: 2px solid #e0e0e0;">মোট:</td>
                                                            <td style="text-align: right; font-weight: 700; font-size: 18px; color: #128C7E; padding-top: 10px; border-top: 2px solid #e0e0e0;"><?php echo wc_price($total); ?></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            
                                            <!-- Additional Order Info -->
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="5" cellspacing="0" border="0">
                                                        <tr>
                                                            <td>
                                                                <span style="color: #128C7E; font-weight: 600;">পেমেন্ট পদ্ধতি:</span> <?php echo esc_html($payment_method); ?>
                                                            </td>
                                                        </tr>
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
                                                        <tr>
                                                            <td>
                                                                <span style="color: #128C7E; font-weight: 600;">কোমরের সাইজ সিলেক্ট করুন:</span> <?php echo esc_html($billing_size); ?>
                                                            </td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Customer Info Section -->
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 18px; font-weight: 600; color: #128C7E; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;">
                                                    <span style="font-weight: bold;">গ্রাহকের তথ্য</span>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="5" cellspacing="0" border="0">
                                                        <tr>
                                                            <td>
                                                                <span style="color: #128C7E; font-weight: 600;">নাম:</span> <?php echo esc_html($customer_name); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span style="color: #128C7E; font-weight: 600;">ঠিকানা:</span> <?php echo esc_html($customer_address); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span style="color: #128C7E; font-weight: 600;">ফোন:</span> <?php echo esc_html($customer_phone); ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Customer History Section -->
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 18px; font-weight: 600; color: #128C7E; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;">
                                                    <span style="font-weight: bold;">গ্রাহকের ইতিহাস</span>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td>
                                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                    <tr>
                                                                        <td width="33%" style="padding: 15px; text-align: center; background-color: #f9f9f9; border-radius: 8px;">
                                                                            <div style="font-size: 14px; color: #666;">মোট অর্ডার</div>
                                                                            <div style="font-size: 24px; font-weight: 700; margin: 5px 0;"><?php echo $total_orders; ?></div>
                                                                        </td>
                                                                        <td width="2%"></td>
                                                                        <td width="33%" style="padding: 15px; text-align: center; background-color: #e8f5e9; border-radius: 8px; border-bottom: 3px solid #4caf50;">
                                                                            <div style="font-size: 14px; color: #666;">ডেলিভারি সম্পন্ন</div>
                                                                            <div style="font-size: 24px; font-weight: 700; margin: 5px 0;"><?php echo $total_delivered; ?></div>
                                                                        </td>
                                                                        <td width="2%"></td>
                                                                        <td width="33%" style="padding: 15px; text-align: center; background-color: #ffebee; border-radius: 8px; border-bottom: 3px solid #f44336;">
                                                                            <div style="font-size: 14px; color: #666;">বাতিল করা</div>
                                                                            <div style="font-size: 24px; font-weight: 700; margin: 5px 0;"><?php echo $total_cancelled; ?></div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <td style="padding-top: 15px;">
                                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                    <tr>
                                                                        <td>
                                                                            <strong>সাফল্যের হার:</strong> <?php echo $success_rate; ?>%
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding-top: 5px;">
                                                                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #e0e0e0; border-radius: 5px; height: 10px;">
                                                                                <tr>
                                                                                    <td width="<?php echo esc_attr($success_rate); ?>%" style="background: linear-gradient(90deg, #4caf50, #8bc34a); border-radius: 5px; height: 10px;"></td>
                                                                                    <td width="<?php echo esc_attr(100 - $success_rate); ?>%"></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Fraud Alert Section -->
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 18px; font-weight: 600; color: #128C7E; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;">
                                                    <span style="font-weight: bold;">সম্ভাব্য প্রতারণা চেক</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <?php if (!empty($frauds)) : ?>
                                                        <?php foreach (array_slice($frauds, 0, 5) as $fraud) : ?>
                                                            <table width="100%" cellpadding="15" cellspacing="0" border="0" style="background-color: #ffebee; border-left: 4px solid #f44336; margin-bottom: 10px;">
                                                                <tr>
                                                                    <td>
                                                                        <p style="margin: 0 0 5px 0;"><strong>নাম:</strong> <?php echo esc_html($fraud['name']); ?></p>
                                                                        <p style="margin: 0;"><strong>বিবরণ:</strong> <?php echo nl2br(esc_html($fraud['details'])); ?></p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <table width="100%" cellpadding="15" cellspacing="0" border="0" style="background-color: #e8f5e9; border-left: 4px solid #4caf50;">
                                                            <tr>
                                                                <td>
                                                                    <p style="margin: 0; color: #388e3c; font-weight: 600;">কোনো প্রতারণার রিপোর্ট পাওয়া যায়নি!</p>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Congratulations Note -->
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="15" cellspacing="0" border="0" style="background-color: #fff8e1; border-left: 4px solid #ffc107;">
                                            <tr>
                                                <td>
                                                    <p style="margin: 0;">বিক্রয়ের জন্য অভিনন্দন! এই অর্ডারটি সফলভাবে প্রক্রিয়া করার জন্য অনুগ্রহ করে প্রয়োজনীয় পদক্ষেপ নিন।</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 14px; color: #666666;">
                            <p style="margin: 0 0 5px 0;">এই ইমেইলটি স্বয়ংক্রিয়ভাবে তৈরি করা হয়েছে, দয়া করে উত্তর দিবেন না।</p>
                            <p style="margin: 0;">&copy; <?php echo date('Y'); ?> আপনার কোম্পানি। সর্বস্বত্ব সংরক্ষিত।</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>