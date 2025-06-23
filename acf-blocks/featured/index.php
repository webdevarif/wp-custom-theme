<?php
    $banner = get_field('image'); // OBJECT ID
    $caption = get_field('caption');
    $heading = get_field('heading');
    $button_label = get_field('button_label');
    $card_link = get_field('card_link');
    $info = get_field('info');

    get_template_part('template-parts/featured-card', null, [
        'banner' => $banner,
        'caption' => $caption,
        'heading' => $heading,
        'info' => $info,
        'button_label' => $button_label,
        'card_link' => $card_link,
        'wrapperClass' => 'featured-card',
        'contentClass' => 'featured-card-content xl:p-10 xl:h-[275px] xl:justify-center xl:[&_.featured-card-heading]:text-4xl xl:[&_.featured-card-heading]:max-w-[600px]'
    ]);
?>