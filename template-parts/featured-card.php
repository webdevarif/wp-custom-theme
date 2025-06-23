<?php
// Accept arguments for reusability
    $banner = $args['banner'] ?? '';
    $caption = $args['caption'] ?? '';
    $heading = $args['heading'] ?? '';
    $info = $args['info'] ?? '';
    $button_label = $args['button_label'] ?? '';
    $card_link = $args['card_link'] ?? '';
    $wrapperClass = $args['wrapperClass'] ?? '';
    $contentClass = $args['contentClass'] ?? '';
?>

<?php if (!empty($card_link)) : ?>
<a href="<?php echo esc_url($card_link); ?>" class="group block relative rounded-2xl overflow-hidden bg-black focus:outline-none focus:ring-2 focus:ring-primary transition-all duration-300 <?php echo esc_attr($wrapperClass); ?>">
<?php else : ?>
<div class="relative rounded-2xl overflow-hidden bg-black w-full <?php echo esc_attr($wrapperClass); ?>">
<?php endif; ?>
    <div class="relative w-full h-full overflow-hidden">
        <img src="<?php echo wp_get_attachment_image_url($banner, 'full'); ?>" alt="<?php echo esc_attr($caption); ?>" class="!w-full !h-full object-cover object-center transition-transform duration-300 will-change-transform group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent z-10"></div>
    </div>
    <div class="absolute inset-0 flex flex-col justify-end items-start p-6 z-20 <?php echo esc_attr($contentClass); ?>">
        <?php if (!empty($caption)) : ?>
            <span class="featured-card-caption inline-block mb-2 px-3 py-1 bg-primary text-white text-xs font-semibold rounded-full"> <?php echo esc_html($caption); ?> </span>
        <?php endif; ?>
        <?php if (!empty($heading)) : ?>
            <h3 class="featured-card-heading text-white text-2xl font-bold leading-tight mb-4"><?php echo __($heading, 'shop-theme'); ?></h3>
        <?php endif; ?>
        <?php if (!empty($info)) : ?>
            <p class="featured-card-info text-white/50 text-base font-normal leading-tight mb-4"><?php echo esc_html($info); ?></p>
        <?php endif; ?>
        <?php if (!empty($button_label)) : ?>
            <span class="featured-card-button inline-flex items-center gap-x-2 text-white font-normal text-base">
                <?php echo esc_html($button_label); ?>
                <span><?php echo get_icon('icon-move-up-right'); ?></span>
            </span>
        <?php endif; ?>
    </div>
<?php if (!empty($card_link)) : ?>
</a>
<?php else : ?>
</div>
<?php endif; ?>