<?php
    $heading = get_field('heading');
    $coupon_code = get_field('coupon_code');
    $description = get_field('description');
?>

<section class="section-discount py-6 lg:py-12">
    <div class="container">
        <div class="discount-content bg-primary/5 rounded-lg p-4 lg:p-6 flex flex-col md:flex-row border border-dashed border-primary items-center justify-center text-center md:text-left gap-4">
            <?php if (!empty($heading)) : ?>
            <div class="coupon-title">
                <h2 class="text-lg md:text-2xl leading-[1.2] font-medium text-primary mb-0"><?php echo $heading; ?></h2>
            </div>
            <?php endif; ?>
            <?php if (!empty($coupon_code)) : ?>
            <div class="coupon-code uppercase text-base tracking-widest font-semibold btn btn btn-primary text-white px-6 py-2 rounded-full"><?php echo $coupon_code; ?></div>
            <?php endif; ?>
            <?php if (!empty($description)) : ?>
                <div class="coupon-description text-base text-black/50"><?php echo $description; ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>