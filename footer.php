<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package shop-theme
 */

?>

<footer id="colophon" class="site-footer bg-black text-white pt-12">
	<div class="container mx-auto px-4">
		<div class="flex flex-col sm:flex-row sm:justify-between sm:flex-wrap gap-y-8 pb-8 border-b border-white/10">
			<!-- Logo & Description -->
			<div class="md:w-1/4 flex flex-col gap-4">
				<div class="flex items-center gap-2 mb-2 [&_img]:h-12 [&_img]:w-auto">
					<?php if (has_custom_logo()) { the_custom_logo(); } else { ?>
						<span class="text-3xl font-bold">Shop</span>
					<?php } ?>
				</div>
				<p class="text-sm text-white/80 leading-relaxed">
					<?php echo esc_html__('Sed perspiciatis unde omnis natus error voluptatem accusantium doloremque laudantium totam aperiam eaque ipsa quae ab illo inventore', 'shop-theme'); ?>
				</p>
				<div class="mt-4">
					<span class="font-semibold mb-2 block"><?php echo esc_html__('Follow Us', 'shop-theme'); ?></span>
					<div class="flex gap-3 text-xl">
						<a href="#" class="hover:text-primary" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
						<a href="#" class="hover:text-primary" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
						<a href="#" class="hover:text-primary" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
						<a href="#" class="hover:text-primary" aria-label="Behance"><i class="fab fa-behance"></i></a>
						<a href="#" class="hover:text-primary" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
					</div>
				</div>
			</div>
			<!-- Quick Links -->
			<div class="md:w-1/4 sm:w-1/2">
				<span class="font-bold text-lg mb-4 block"><?php echo esc_html__('Quick Links', 'shop-theme'); ?></span>
				<ul class="space-y-2">
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Discount Returns', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Privacy Policy', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Customer Service', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Term & condition', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Specials Offers', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Return Policy', 'shop-theme'); ?></a></li>
				</ul>
			</div>
			<!-- Useful Links -->
			<div class="md:w-1/4 sm:w-1/2">
				<span class="font-bold text-lg mb-4 block"><?php echo esc_html__('Useful', 'shop-theme'); ?></span>
				<ul class="space-y-2">
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Site Map', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Search Terms', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Advanced Search', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('About Us', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Contact Us', 'shop-theme'); ?></a></li>
					<li><a href="#" class="hover:underline"><?php echo esc_html__('Suppliers', 'shop-theme'); ?></a></li>
				</ul>
			</div>
			<!-- Contact Us -->
			<div class="md:w-1/4">
				<span class="font-bold text-lg mb-4 block"><?php echo esc_html__('Contact Us', 'shop-theme'); ?></span>
				<ul class="space-y-3 text-white/80">
					<li class="flex items-start gap-2"><span class="mt-1"><i class="fas fa-map-marker-alt"></i></span> <span><?php echo esc_html__('250 Main Street. 2nd Floor D - Block, New York', 'shop-theme'); ?></span></li>
					<li class="flex items-center gap-2"><span><i class="fas fa-envelope"></i></span> <a href="mailto:support@example.com" class="hover:underline">support@example.com</a></li>
					<li class="flex items-center gap-2"><span><i class="fas fa-phone"></i></span> <a href="tel:+89812345698" class="hover:underline">+898 - 123 - 456 - 98</a></li>
				</ul>
			</div>
		</div>
		<div class="text-center text-white/60 text-sm py-6">
			<?php printf(esc_html__('Copyright %s. All rights reserved', 'shop-theme'), date('Y')); ?>
		</div>
	</div>
</footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
