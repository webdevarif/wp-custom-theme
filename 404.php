<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package shop-theme
 */

get_header();
?>

<main id="primary" class="site-main min-h-screen bg-gray-50">
	
	<!-- 404 Header -->
	<div class="bg-white border-b border-gray-200">
		<div class="container mx-auto px-4 py-8 lg:py-12">
			<div class="max-w-4xl mx-auto text-center">
				
				<!-- 404 Icon -->
				<div class="mx-auto [&_svg]:w-24 [&_svg]:h-24 lg:[&_svg]:w-32 lg:[&_svg]:h-32 [&_svg]:text-dark/30 flex items-center justify-center mb-6">
					<?php echo get_icon('icon-emoji-sad'); ?>
				</div>

				<!-- 404 Header -->
				<header class="page-header mb-8">
					<h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-4">
						<?php esc_html_e( '404', 'shop-theme' ); ?>
					</h1>
					<h2 class="text-2xl lg:text-3xl font-semibold text-gray-700 mb-4">
						<?php esc_html_e( 'Page Not Found', 'shop-theme' ); ?>
					</h2>
					<p class="text-base lg:text-lg text-gray-600 max-w-2xl mx-auto">
						<?php esc_html_e( 'Sorry, the page you are looking for doesn\'t exist or has been moved. Let\'s get you back on track!', 'shop-theme' ); ?>
					</p>
				</header>
			</div>
		</div>
	</div>

	<!-- 404 Content -->
	<div class="container mx-auto px-4 py-8 lg:py-12">
		<div class="max-w-6xl mx-auto">
			
			<!-- Search Section -->
			<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
				<div class="text-center mb-6">
					<h3 class="text-xl font-semibold text-gray-900 mb-2">
						<?php esc_html_e( 'Search for what you need', 'shop-theme' ); ?>
					</h3>
					<p class="text-gray-600">
						<?php esc_html_e( 'Try searching for the content you\'re looking for', 'shop-theme' ); ?>
					</p>
				</div>
				
				<div class="max-w-md mx-auto">
					<?php get_search_form(); ?>
				</div>
			</div>

			<!-- Quick Links Grid -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
				
				<!-- Recent Posts -->
				<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
					<h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
						<svg class="h-5 w-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
						</svg>
						<?php esc_html_e( 'Recent Posts', 'shop-theme' ); ?>
					</h3>
					<div class="space-y-3">
						<?php
						$recent_posts = wp_get_recent_posts( array(
							'numberposts' => 5,
							'post_status' => 'publish'
						) );
						
						if ( $recent_posts ) :
							foreach ( $recent_posts as $post ) :
								?>
								<div class="flex items-start space-x-3">
									<div class="flex-shrink-0 w-2 h-2 bg-primary rounded-full mt-2"></div>
									<div class="flex-1 min-w-0">
										<a href="<?php echo esc_url( get_permalink( $post['ID'] ) ); ?>" class="text-sm font-medium text-gray-900 hover:text-primary truncate block">
											<?php echo esc_html( $post['post_title'] ); ?>
										</a>
										<p class="text-xs text-gray-500">
											<?php echo get_the_date( '', $post['ID'] ); ?>
										</p>
									</div>
								</div>
								<?php
							endforeach;
						else :
							?>
							<p class="text-sm text-gray-500"><?php esc_html_e( 'No recent posts found.', 'shop-theme' ); ?></p>
							<?php
						endif;
						?>
					</div>
				</div>

				<!-- Popular Categories -->
				<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
					<h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
						<svg class="h-5 w-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
						</svg>
						<?php esc_html_e( 'Popular Categories', 'shop-theme' ); ?>
					</h3>
					<div class="space-y-3">
						<?php
						$categories = get_categories( array(
							'orderby'    => 'count',
							'order'      => 'DESC',
							'show_count' => 1,
							'number'     => 5,
						) );
						
						if ( $categories ) :
							foreach ( $categories as $category ) :
								?>
								<div class="flex items-center justify-between">
									<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="text-sm font-medium text-gray-900 hover:text-primary">
										<?php echo esc_html( $category->name ); ?>
									</a>
									<span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
										<?php echo esc_html( $category->count ); ?>
									</span>
								</div>
								<?php
							endforeach;
						else :
							?>
							<p class="text-sm text-gray-500"><?php esc_html_e( 'No categories found.', 'shop-theme' ); ?></p>
							<?php
						endif;
						?>
					</div>
				</div>

				<!-- Archives -->
				<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
					<h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
						<svg class="h-5 w-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
						</svg>
						<?php esc_html_e( 'Archives', 'shop-theme' ); ?>
					</h3>
					<div class="space-y-3">
						<?php
						$archives = wp_get_archives( array(
							'type'            => 'monthly',
							'limit'           => 5,
							'echo'            => 0,
							'format'          => 'custom',
							'before'          => '',
							'after'           => '',
						) );
						
						if ( $archives ) :
							$archive_links = explode( '</li>', $archives );
							foreach ( $archive_links as $link ) :
								if ( trim( $link ) ) :
									$link = str_replace( '<li>', '', $link );
									$link = str_replace( '</a>', '</a>', $link );
									?>
									<div class="flex items-center justify-between">
										<?php echo $link; ?>
									</div>
									<?php
								endif;
							endforeach;
						else :
							?>
							<p class="text-sm text-gray-500"><?php esc_html_e( 'No archives found.', 'shop-theme' ); ?></p>
							<?php
						endif;
						?>
					</div>
				</div>

			</div>

			<!-- Action Buttons -->
			<div class="text-center mt-12">
				<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary py-3 px-6">
						<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
						</svg>
						<?php esc_html_e( 'Back to Home', 'shop-theme' ); ?>
					</a>
					
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
						<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
						</svg>
						<?php esc_html_e( 'Browse Blog', 'shop-theme' ); ?>
					</a>
				</div>
			</div>

		</div>
	</div>

</main><!-- #main -->

<?php
get_footer();
