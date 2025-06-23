<?php
if (isset($args) && is_array($args)) {
    extract($args);
}
?>
<div class="section-header border-b border-gray-200 pb-3 mb-8 flex flex-col sm:flex-row items-start sm:items-center gap-x-4">
    <?php if (!empty($title)) : ?>
        <h2 class="text-xl font-bold mb-0 uppercase"><?php echo $title; ?></h2>
    <?php endif; ?>
    <?php if (!empty($info)) : ?>
        <div class="text-sm text-gray-500 mb-0"><?php echo $info; ?></div>
    <?php endif; ?>
    <?php if (!empty($button_link) && !empty($button_label)) : ?>
        <a href="<?php echo $button_link; ?>" class="text-base font-semibold inline-flex items-center gap-x-1 text-dark hover:text-primary transition-all duration-300 sm:ms-auto">
            <span><?php echo $button_label; ?></span> 
            <?php echo get_icon('icon-move-up-right'); ?>
        </a>
    <?php endif; ?>
</div>