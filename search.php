<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package shop-theme
 */

get_header();
?>

<main id="primary" class="site-main min-h-screen bg-gray-50">
	
	<!-- Search Header -->
	<div class="bg-white border-b border-gray-200">
		<div class="container mx-auto px-4 py-8 lg:py-12">
			<div class="max-w-4xl mx-auto text-center">
				
				<!-- Search Icon -->
				<div class="mx-auto h-16 w-16 bg-primary rounded-2xl flex items-center justify-center mb-6">
					<svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
					</svg>
				</div>

				<!-- Search Results Header -->
				<header class="page-header mb-8">
					<h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
						<?php esc_html_e( 'Search Results', 'shop-theme' ); ?>
					</h1>
					<div class="text-lg text-gray-600 mb-6">
						<?php
						/* translators: %s: search query. */
						printf( esc_html__( 'Showing results for: %s', 'shop-theme' ), '<span class="font-semibold text-primary">' . get_search_query() . '</span>' );
						?>
					</div>
					
					<!-- Search Form -->
					<div class="max-w-md mx-auto">
						<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div class="relative">
								<input 
									type="search" 
									class="w-full px-4 py-3 pl-12 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
									placeholder="<?php esc_attr_e( 'Search again...', 'shop-theme' ); ?>" 
									value="<?php echo get_search_query(); ?>" 
									name="s" 
								/>
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
									</svg>
								</div>
								<button type="submit" class="absolute inset-y-0 right-0 px-4 text-blue-600 hover:text-blue-700">
									<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
									</svg>
								</button>
							</div>
						</form>
					</div>
				</header>
			</div>
		</div>
	</div>

	<!-- Search Results Content -->
	<div class="container mx-auto px-4 py-8 lg:py-12">
		<div class="max-w-6xl mx-auto">
			
			<?php if ( have_posts() ) : ?>
				
				<!-- Results Count -->
				<div class="mb-8 text-center">
					<p class="text-lg text-gray-600">
						<?php
						global $wp_query;
						$total_results = $wp_query->found_posts;
						printf(
							esc_html( _n( 'Found %d result', 'Found %d results', $total_results, 'shop-theme' ) ),
							$total_results
						);
						?>
					</p>
				</div>

				<!-- Results Grid -->
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
					<?php
					/* Start the Loop */
					while ( have_posts() ) :
						the_post();
						?>
						
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md' ); ?>>
							
							<!-- Featured Image -->
							<?php if ( has_post_thumbnail() ) : ?>
							<div class="aspect-w-16 aspect-h-9">
								<a href="<?php the_permalink(); ?>" class="block">
									<?php the_post_thumbnail( 'medium', array( 'class' => 'w-full h-48 object-cover' ) ); ?>
								</a>
							</div>
							<?php endif; ?>
							
							<!-- Content -->
							<div class="p-6">
								<!-- Post Type Badge -->
								<div class="mb-3">
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
										<?php echo get_post_type_object( get_post_type() )->labels->singular_name; ?>
									</span>
								</div>
								
								<!-- Title -->
								<header class="entry-header mb-3">
									<h2 class="entry-title text-xl font-bold text-gray-900 mb-2">
										<a href="<?php the_permalink(); ?>" class="hover:text-blue-600">
											<?php the_title(); ?>
										</a>
									</h2>
								</header>
								
								<!-- Excerpt -->
								<div class="entry-summary mb-4">
									<p class="text-gray-600 text-sm leading-relaxed">
										<?php 
										$excerpt = get_the_excerpt();
										if ( $excerpt ) {
											echo wp_trim_words( $excerpt, 20, '...' );
										} else {
											echo wp_trim_words( get_the_content(), 20, '...' );
										}
										?>
									</p>
								</div>
								
								<!-- Meta -->
								<div class="entry-meta flex items-center justify-between text-sm text-gray-500">
									<div class="flex items-center space-x-4">
										<span class="flex items-center">
											<svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
											</svg>
											<?php echo get_the_date(); ?>
										</span>
										<?php if ( has_category() ) : ?>
										<span class="flex items-center">
											<svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
											</svg>
											<?php the_category( ', ' ); ?>
										</span>
										<?php endif; ?>
									</div>
								</div>
								
								<!-- Read More Link -->
								<div class="mt-4">
									<a href="<?php the_permalink(); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm">
										<?php esc_html_e( 'Read More', 'shop-theme' ); ?>
										<svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
										</svg>
									</a>
								</div>
							</div>
						</article>
						
						<?php
					endwhile;
					?>
				</div>

				<!-- Pagination -->
				<div class="mt-12">
					<?php
					// Custom pagination with Tailwind styling
					$pagination = get_the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>',
						'next_text' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
					) );
					
					// Add Tailwind classes to pagination
					$pagination = str_replace( 'nav-links', 'flex items-center justify-center space-x-2', $pagination );
					$pagination = str_replace( 'page-numbers', 'px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600', $pagination );
					$pagination = str_replace( 'current', 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700', $pagination );
					$pagination = str_replace( 'prev', 'flex items-center', $pagination );
					$pagination = str_replace( 'next', 'flex items-center', $pagination );
					
					echo $pagination;
					?>
				</div>

			<?php else : ?>

				<!-- No Results -->
				<div class="text-center py-16">
					<div class="mx-auto h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
						<svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.47-.881-6.08-2.33"></path>
						</svg>
					</div>
					
					<h2 class="text-3xl font-bold text-gray-900 mb-4">
						<?php esc_html_e( 'No results found', 'shop-theme' ); ?>
					</h2>
					
					<p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
						<?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'shop-theme' ); ?>
					</p>
					
					<!-- Search Again Form -->
					<div class="max-w-md mx-auto mb-8">
						<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div class="relative">
								<input 
									type="search" 
									class="w-full px-4 py-3 pl-12 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
									placeholder="<?php esc_attr_e( 'Try a different search...', 'shop-theme' ); ?>" 
									name="s" 
								/>
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
									</svg>
								</div>
								<button type="submit" class="absolute inset-y-0 right-0 px-4 text-blue-600 hover:text-blue-700">
									<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
									</svg>
								</button>
							</div>
						</form>
					</div>
					
					<!-- Back to Home -->
					<div>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
							<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
							</svg>
							<?php esc_html_e( 'Back to Home', 'shop-theme' ); ?>
						</a>
					</div>
				</div>

			<?php endif; ?>

		</div>
	</div>

</main><!-- #main -->

<?php
// get_sidebar();
get_footer();
