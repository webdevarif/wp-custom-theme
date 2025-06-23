<?php
    $repeater = get_field('repeater');
    if (!$repeater) return;

    // Define Swiper configuration
    $swiperConfig = [
        'slidesPerView' => 1,
        'spaceBetween' => 30,
        'loop' => true,
        'effect' => 'fade',
        'lazy' => true,
        'speed' => 600,
        // 'autoplay' => [
        //     'delay' => 5000,
        //     'disableOnInteraction' => false
        // ],
        'navigation' => [
            'nextEl' => '.swiper-button-next',
            'prevEl' => '.swiper-button-prev'
        ],
        'pagination' => [
            'el' => '.swiper-pagination',
            'clickable' => true,
            'type' => 'bullets'
        ],
        'breakpoints' => [
            640 => [
                'slidesPerView' => 1,
                'spaceBetween' => 30
            ],
            1024 => [
                'slidesPerView' => 1,
                'spaceBetween' => 30
            ]
        ]
    ];

    // Convert config to JSON and escape for HTML attribute
    $swiperConfigJson = htmlspecialchars(json_encode($swiperConfig), ENT_QUOTES, 'UTF-8');
?>

<section-slideshow class="relative w-full block py-4">
    <div class="container">
        <div class="slideshow__container lg:pl-[18rem]"> 
            <div class="swiper relative z-10 overflow-hidden group"
                data-swiper-options='<?php echo $swiperConfigJson; ?>'>
                <div class="swiper-wrapper">
                    <?php foreach ($repeater as $slide) : 
                        if (!isset($slide['background']) || empty($slide['background'])) continue;
                    ?>
                        <div class="swiper-slide relative w-full h-full">
                            <?php echo wp_get_attachment_image(
                                $slide['background'],
                                'full',
                                false,
                                array(
                                    'class' => 'absolute top-0 left-0 w-full h-full object-cover rounded-2xl swiper-lazy z-[-1]',
                                    'data-src' => wp_get_attachment_image_url($slide['background'], 'full'),
                                    'loading' => 'lazy'
                                )
                            ); ?>
                            <div class="relative z-10 text-white py-8 px-6 lg:px-12 md:h-[480px] flex flex-col items-start justify-center transitl duration-500 slideshow__item-content md:max-w-[400px] gap-y-0">
                                <?php if (!empty($slide['caption'])) : ?>
                                    <h6 class="text-sm mb-2 opacity-90 fade-anim" data-fade-from="left" data-delay="0.2"><?php echo esc_html($slide['caption']); ?></h6>
                                <?php endif; ?>

                                <?php if (!empty($slide['heading'])) : ?>
                                    <h2 class="text-3xl font-bold font-sans mb-4 leading-tight text-anim"><?php echo esc_html($slide['heading']); ?></h2>
                                <?php endif; ?>

                                <?php if (!empty($slide['content'])) : ?>
                                    <div class="text-base mb-4 text-white/75 fade-anim" data-fade-from="left" data-delay="0.4"><?php echo wp_kses_post($slide['content']); ?></div>
                                <?php endif; ?>

                                <?php if (!empty($slide['regular_price']) || !empty($slide['sale_price'])) : ?>
                                    <div class="flex items-center gap-x-3 mb-6 fade-anim" data-fade-from="left" data-delay="0.4">
                                        <?php if (!empty($slide['regular_price'])) : ?>
                                            <span class="text-base text-gray-300 line-through"><?php echo wp_kses_post($slide['regular_price']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($slide['sale_price'])) : ?>
                                            <span class="text-2xl font-bold text-primary"><?php echo wp_kses_post($slide['sale_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($slide['action_button']['button_link']) && !empty($slide['action_button']['button_label'])) : ?>
                                    <?php printf(
                                        '<a href="%s" class="inline-block px-8 py-3 bg-white text-black no-underline rounded transition-all duration-300 hover:bg-gray-100 hover:-translate-y-0.5 fade-anim" data-fade-from="left" data-delay="0.6">%s</a>',
                                        esc_url($slide['action_button']['button_link']),
                                        esc_html($slide['action_button']['button_label'])
                                    ); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Navigation -->
                <div class="swiper-button-prev w-8 h-12 absolute top-1/2 left-0 -translate-y-1/2 z-20 opacity-0 pointer-events-none -translate-x-[100%] group-hover:opacity-100 group-hover:pointer-events-auto group-hover:translate-x-0 transition-all duration-300 bg-white text-black rounded-r" style="--swiper-navigation-size: 1rem;"></div>
                <div class="swiper-button-next w-8 h-12 absolute top-1/2 right-0 -translate-y-1/2 z-20 opacity-0 pointer-events-none translate-x-[100%] group-hover:opacity-100 group-hover:pointer-events-auto group-hover:translate-x-0 transition-all duration-300 bg-white text-black rounded-l" style="--swiper-navigation-size: 1rem;"></div>
                <!-- Pagination -->
                <div class="swiper-pagination swiper-pagination--default"></div>
            </div>
        </div>
    </div>
</section-slideshow>