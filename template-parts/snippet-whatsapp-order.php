<?php

$whatsapp_icon      = get_icon('icon-whatsapp');
$specialist_title   = esc_html__('কোন প্রশ্ন আছে? আমাদের সাথে কথা বলুন', 'shop-theme');
$specialist_phone   = esc_html__('(+880) 1841-695790', 'shop-theme');
$whatsapp_text      = esc_html__('হোয়াটসঅ্যাপে অর্ডার করুন', 'shop-theme');
$whatsapp_url       = 'https://wa.me/8801841695790'; // Replace with your WhatsApp number

printf(
    '<div class="flex items-center gap-4 p-6 rounded-lg bg-gray-100">
        <span class="flex-shrink-0 inline-block text-green-600 [&_svg]:h-8 [&_svg]:w-8 lg:[&_svg]:h-16 lg:[&_svg]:w-16">%s</span>
        <div>
            <p class="font-semibold text-gray-800 text-sm lg:text-base">%s</p>
            <p class="text-sm lg:text-xl font-bold text-gray-900 mt-1">%s</p>
            <a href="%s" target="_blank" class="text-green-600 font-semibold hover:text-green-700 transition-colors mt-2 inline-block text-sm lg:text-base">%s</a>
        </div>
    </div>',
    $whatsapp_icon,
    $specialist_title,
    $specialist_phone,
    esc_url($whatsapp_url),
    $whatsapp_text
);