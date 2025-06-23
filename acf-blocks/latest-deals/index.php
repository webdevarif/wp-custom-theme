<?php
    $heading = get_field('heading');
    $left_products = get_field('left_products');
    $middle_item = get_field('middle_item');
    $right_products = get_field('right_products');
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
        <div class="section-content grid sm:grid-cols-2 lg:grid-cols-[5fr_4fr_5fr] gap-4">
            <!-- Left Products Grid (2x2) -->
            <div class="grid grid-cols-2 gap-4 flex-1 hoverable-group">
                <?php if (is_array($left_products) &&!empty($left_products)) :?>
                    <?php foreach ($left_products as $product) :
                    get_template_part('template-parts/product-card', null, [
                        'product' => $product['product'],
                        'style' => 'style-1'
                    ]);
                    endforeach; ?>
                <?php endif;?>
            </div>
            <!-- Middle Special Offer -->
            <div class="flex flex-col items-center sm:col-span-2 lg:col-span-1 sm:order-3 lg:order-2">
                <div class="group relative border-[3px] border-primary rounded-xl p-4 lg:p-6 bg-white shadow z-0 transition-all duration-300 w-full h-full hover:shadow-[0_25px_50px_-12px_rgba(33,37,41,0.25)]">
                    <div class="mb-6">
                        <?php 
                        if (!empty($middle_item['title'])) {
                            echo sprintf('<div class="text-xl font-bold text-primary mb-2">%s</div>', esc_html($middle_item['title']));
                        }
                        if (!empty($middle_item['info'])) {
                            echo sprintf('<div class="text-base text-gray-500 mb-3">%s</div>', esc_html($middle_item['info']));
                        }
                        if (!empty($middle_item['end_time'])) {
                            ?>
                            <div class="countdown-timer flex gap-1 items-center mb-2" data-end-time="<?php echo esc_attr($middle_item['end_time']); ?>">
                                <span class="days bg-[linear-gradient(45deg,rgb(239,35,60)_0%,rgb(244,113,3)_100%)] text-white w-[2.375rem] h-[2.375rem] flex items-center justify-center px-2 py-1 rounded-lg font-semibold text-base">00</span> :
                                <span class="hours bg-[linear-gradient(45deg,rgb(239,35,60)_0%,rgb(244,113,3)_100%)] text-white w-[2.375rem] h-[2.375rem] flex items-center justify-center px-2 py-1 rounded-lg font-semibold text-base">00</span> :
                                <span class="minutes bg-[linear-gradient(45deg,rgb(239,35,60)_0%,rgb(244,113,3)_100%)] text-white w-[2.375rem] h-[2.375rem] flex items-center justify-center px-2 py-1 rounded-lg font-semibold text-base">00</span> :
                                <span class="seconds bg-[linear-gradient(45deg,rgb(239,35,60)_0%,rgb(244,113,3)_100%)] text-white w-[2.375rem] h-[2.375rem] flex items-center justify-center px-2 py-1 rounded-lg font-semibold text-base">00</span>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php if (is_array($middle_item) && !empty($middle_item)) {
                        if (isset($middle_item['product'])) {
                            get_template_part('template-parts/product-card', null, [
                                'product' => $middle_item['product'],
                                'style' => 'style-2'
                            ]);
                        }
                    } ?>
                </div>
            </div>
            <!-- Right Products Grid (2x2) -->
            <div class="grid grid-cols-2 gap-4 flex-1 hoverable-group sm:order-2 lg:order-3">
                <?php if (is_array($right_products) &&!empty($right_products)) :?>
                    <?php foreach ($right_products as $product) :
                    get_template_part('template-parts/product-card', null, [
                        'product' => $product['product'],
                        'style' => 'style-1'
                    ]);
                    endforeach;?>
                <?php endif;?>
            </div>
        </div>
    </div>
</section>