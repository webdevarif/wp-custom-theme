<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if we should show register form
$show_register = isset($_GET['action']) && $_GET['action'] === 'register';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
	<div class="max-w-md mx-auto">
		
		<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

		<!-- Header Section -->
		<div class="text-center mb-8">
			<h2 class="text-3xl font-bold text-gray-900 mb-2">
				<?php echo $show_register ? esc_html__('Create Account', 'woocommerce') : esc_html__('Welcome Back', 'woocommerce'); ?>
			</h2>
			<p class="text-gray-600">
				<?php echo $show_register ? esc_html__('Sign up to get started', 'woocommerce') : esc_html__('Sign in to your account', 'woocommerce'); ?>
			</p>
		</div>

		<!-- Form Container -->
		<div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
			
			<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) && !$show_register ) : ?>
			
			<!-- Login Form -->
			<div class="space-y-6">
				<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<div class="space-y-4">
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
						</p>
					</div>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<div class="flex items-center justify-between mt-6">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200">
							<?php esc_html_e( 'Forgot password?', 'woocommerce' ); ?>
						</a>
					</div>

					<div class="mt-6">
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02] woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Sign In', 'woocommerce' ); ?></button>
					</div>

					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>

				<!-- Divider -->
				<div class="relative my-6 flex flex-col w-full">
					<div class="absolute inset-0 flex items-center">
						<div class="w-full border-t border-gray-300"></div>
					</div>
					<div class="relative flex justify-center text-sm">
						<span class="px-2 bg-white text-gray-500">Don't have an account?</span>
					</div>
				</div>

				<!-- Register Link -->
				<div class="text-center">
					<a 
						href="<?php echo esc_url( add_query_arg( 'action', 'register', wc_get_page_permalink( 'myaccount' ) ) ); ?>" 
						class="inline-flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
					>
						<?php esc_html_e( 'Create Account', 'woocommerce' ); ?>
					</a>
				</div>
			</div>

			<?php elseif ( $show_register ) : ?>

			<!-- Register Form -->
			<div class="space-y-6">
				<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<div class="space-y-4">
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
						</p>
						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
						</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
						</p>
						<?php else : ?>
						<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
							<p class="text-sm text-blue-800">
								<?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?>
							</p>
						</div>
						<?php endif; ?>
					</div>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<div class="mt-6">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02] woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Create Account', 'woocommerce' ); ?></button>
					</div>

					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>

				<!-- Divider -->
				<div class="relative my-6">
					<div class="absolute inset-0 flex items-center">
						<div class="w-full border-t border-gray-300"></div>
					</div>
					<div class="relative flex justify-center text-sm">
						<span class="px-2 bg-white text-gray-500">Already have an account?</span>
					</div>
				</div>

				<!-- Login Link -->
				<div class="text-center">
					<a 
						href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" 
						class="inline-flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
					>
						<?php esc_html_e( 'Sign In', 'woocommerce' ); ?>
					</a>
				</div>
			</div>

			<?php else : ?>

			<!-- Registration Disabled Message -->
			<div class="text-center py-8">
				<div class="mx-auto h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-4">
					<svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
					</svg>
				</div>
				<h3 class="text-lg font-medium text-gray-900 mb-2">Registration Disabled</h3>
				<p class="text-gray-600">New user registration is currently disabled.</p>
			</div>

			<?php endif; ?>

		</div>

		<!-- Footer -->
		<div class="mt-8 text-center">
			<p class="text-sm text-gray-500">
				By continuing, you agree to our 
				<a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> 
				and 
				<a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
			</p>
		</div>

		<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

	</div>
</div>
