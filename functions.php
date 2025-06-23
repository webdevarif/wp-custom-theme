<?php
/**
 * Shop Theme - Main Theme Class
 *
 * @package shop-theme
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

class ShopTheme {
	/**
	 * Instance of this class
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize theme
	 */
	private function init() {
		add_action('after_setup_theme', [$this, 'setup']);
		add_action('after_setup_theme', [$this, 'contentWidth'], 0);
		add_action('widgets_init', [$this, 'widgetsInit']);
		add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
	}

	/**
	 * Theme setup
	 */
	public function setup() {
		/*
			* Make theme available for translation.
			* Translations can be filed in the /languages/ directory.
			* If you're building a theme based on shop-theme, use a find and replace
			* to change 'shop-theme' to the name of your theme in all the template files.
			*/
		load_theme_textdomain('shop-theme', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
			* Let WordPress manage the document title.
			* By adding theme support, we declare that this theme does not use a
			* hard-coded <title> tag in the document head, and expect WordPress to
			* provide it for us.
			*/
		add_theme_support('title-tag');

		/*
			* Enable support for Post Thumbnails on posts and pages.
			*
			* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
			*/
		add_theme_support('post-thumbnails');

		// Add WooCommerce support
		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus([
			'menu-1' => esc_html__('Primary', 'shop-theme'),
		]);

		/*
			* Switch default core markup for search form, comment form, and comments
			* to output valid HTML5.
			*/
		add_theme_support('html5', [
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		]);

		// Set up the WordPress core custom background feature.
		add_theme_support('custom-background', apply_filters('shop_theme_custom_background_args', [
			'default-color' => 'ffffff',
			'default-image' => '',
		]));

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support('custom-logo', [
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		]);
	}

	/**
	 * Set content width
	 */
	public function contentWidth() {
		$GLOBALS['content_width'] = apply_filters('shop_theme_content_width', 640);
	}

	/**
	 * Register widget areas
	 */
	public function widgetsInit() {
		register_sidebar([
			'name'          => esc_html__('Sidebar', 'shop-theme'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'shop-theme'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		]);
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueueAssets() {
		// Enqueue Swiper CSS from CDN
		wp_enqueue_style(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css',
			[],
			null
		);

		// Enqueue Tailwind CSS
		wp_enqueue_style(
			'tailwindcss',
			get_template_directory_uri() . '/dist/css/tailwind.min.css',
			['swiper'],
			_S_VERSION
		);

		// Enqueue main stylesheet
		wp_enqueue_style(
			'shop-theme-style',
			get_stylesheet_uri(),
			['tailwindcss'],
			_S_VERSION
		);
		wp_style_add_data('shop-theme-style', 'rtl', 'replace');

		// Enqueue Swiper JS from CDN
		wp_enqueue_script(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js',
			array('jquery'),
			null,
			true
		);

		// Enqueue app bundle (libraries and dependencies)
		wp_enqueue_script(
			'shop-theme-app',
			get_template_directory_uri() . '/dist/js/app.bundle.js',
			array('jquery', 'swiper'),
			_S_VERSION,
			true
		);

		// Enqueue main bundle (custom JS)
		wp_enqueue_script(
			'shop-theme-main',
			get_template_directory_uri() . '/dist/js/main.bundle.js',
			array('shop-theme-app'),
			_S_VERSION,
			true
		);

		if (is_singular() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}
	}
}

// Initialize the theme
ShopTheme::getInstance();

// Include additional files
require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/customizer.php';
// require get_template_directory() . '/inc/order-auditional.php';
// require get_template_directory() . '/inc/incomplete-checkout.php';
// require get_template_directory() . '/inc/customize-checkout.php';
require get_template_directory() . '/inc/woo-customizer.php';

if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Helper: Get SVG icon markup by slug
 * Usage: echo get_icon('basket-outline');
 */
function get_icon($slug) {
	$icon_path = get_template_directory() . '/icons/' . $slug . '.svg';
	if (file_exists($icon_path)) {
		return file_get_contents($icon_path);
	}
	return '<!-- Icon not found: ' . esc_html($slug) . ' -->';
}

// === WooCommerce Product Category: Show Browse Category Checkbox ===
// Add field to Add form
add_action('product_cat_add_form_fields', function() {
	?>
	<div class="form-field" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
		<label style="margin:0;font-weight:600;">Browse Category</label>
		<span style="display:flex;align-items:center;gap:0.5em;">
			<input type="checkbox" name="show_browse_category" id="show_browse_category" value="1" />
			<label for="show_browse_category" style="margin:0;">Show</label>
		</span>
	</div>
	<?php
});
// Add field to Edit form
add_action('product_cat_edit_form_fields', function($term) {
	$value = get_term_meta($term->term_id, 'show_browse_category', true);
	?>
	<tr class="form-field">
		<th scope="row" style="font-weight:600;">Browse Category</th>
		<td style="display:flex;align-items:center;gap:1rem;">
			<span style="display:flex;align-items:center;gap:0.5em;">
				<input type="checkbox" name="show_browse_category" id="show_browse_category" value="1" <?php checked($value, '1'); ?> />
				<label for="show_browse_category" style="margin:0;">Show</label>
			</span>
			<p class="description" style="margin:0;">
				<?php _e('If checked, this category will appear in the header All Categories list.', 'shop-theme'); ?>
			</p>
		</td>
	</tr>
	<?php
});
// Save field value
add_action('created_product_cat', function($term_id) {
	$value = isset($_POST['show_browse_category']) ? '1' : '';
	update_term_meta($term_id, 'show_browse_category', $value);
});
add_action('edited_product_cat', function($term_id) {
	$value = isset($_POST['show_browse_category']) ? '1' : '';
	update_term_meta($term_id, 'show_browse_category', $value);
});
