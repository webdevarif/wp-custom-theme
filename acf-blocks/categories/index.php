<?php
    $heading = get_field('heading');
    $categories = get_field('repeater');
?>
<section class="w-full bg-white py-6 lg:py-12">
    <div class="container">
        <?php if (is_array($heading) && !empty($heading)) : 
            get_template_part('template-parts/section-heading', null, [
                'title' => $heading['title'],
                'info' => $heading['info'],
                'button_link' => $heading['button_link'],
                'button_label' => $heading['button_label']
            ]);
        endif; ?>
        <div class="section-content">
            <div class="categories-list w-full grid grid-cols-1 <?php 
                $count = count($categories);
                if ($count === 1) {
                    echo 'md:grid-cols-1';
                } elseif ($count === 2) {
                    echo 'md:grid-cols-2';
                } else {
                    echo 'md:grid-cols-2 lg:grid-cols-3';
                }
            ?> gap-6">
                <?php foreach ($categories as $category) : 
                    $banner = $category['image']; // OBJECT ID
                    $caption = $category['caption'];
                    $heading = $category['heading'];
                    $button_label = $category['button_label'];
                    $card_link = $category['card_link'];
                ?>
                <?php get_template_part('template-parts/featured-card', null, [
                    'banner' => $banner,
                    'caption' => $caption,
                    'heading' => $heading,
                    'button_label' => $button_label,
                    'card_link' => $card_link
                ]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
