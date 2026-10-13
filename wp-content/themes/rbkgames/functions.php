<?php
/**
 * Theme functions for rbkgames.
 *
 * Appearance → Theme Options: inc/class-wpm-options.php
 *
 * @package rbkgames
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/inc/class-wpm-options.php';
require_once __DIR__ . '/inc/class-wpm-styles.php';
require_once __DIR__ . '/inc/class-wpm-frontend.php';

WPM_Options::boot([
	'option'  => 'rbkgames_options',
	'accent'  => '#0f766e',
	'version' => wp_get_theme()->get('Version'),
	'url'     => get_template_directory_uri(),
]);
WPM_Styles::boot();
WPM_Frontend::boot();

add_action('after_setup_theme', function () {
	add_theme_support('wp-block-styles');
	add_theme_support('responsive-embeds');
	// Enables Appearance → Menus; menus are picked in Theme Options → Header / Footer.
	register_nav_menus([
		'header' => 'Header menu',
		'footer' => 'Footer menu',
	]);
	add_editor_style('style.css');
});

add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style('rbkgames-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
});
