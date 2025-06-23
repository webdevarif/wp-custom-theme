<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package shop-theme
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<!-- Google Fonts: Anek Bangla -->
	<link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;600;700&display=swap" rel="stylesheet">
	<style>
		body, .anek-bangla { font-family: 'Anek Bangla', sans-serif; }
	</style>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'shop-theme' ); ?></a>
<site-header class="block w-full anek-bangla">
	<?php get_template_part('template-parts/header-topbar'); ?>
	<!-- Main Header -->
	<?php get_template_part('template-parts/header-main'); ?>
	<!-- Navigation & Categories -->
	<?php get_template_part('template-parts/header-navigation'); ?>
</site-header>