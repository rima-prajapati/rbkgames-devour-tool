<?php
/**
 * Home page: assets and SEO meta.
 */

if (!defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', function () {
	if (!is_front_page()) {
		return;
	}
	$f = get_stylesheet_directory() . '/assets/home.css';
	wp_enqueue_style('rbkgames-child-home', get_stylesheet_directory_uri() . '/assets/home.css', ['rbkgames-child-layout'], (string) filemtime($f));
}, 40);

add_filter('pre_get_document_title', function ($t) {
	return is_front_page() ? 'Онлайн игры | ROBOKOT Games' : $t;
});

add_action('wp_head', function () {
	if (is_front_page()) {
		echo '<meta name="description" content="' . esc_attr('Новости видеоигр и кино. Браузерные онлайн игры, обзоры техники, топы фильмов, подборки видеоигр на ПК, PS и Xbox. Статьи про игры, секреты, гайды, читы и коды.') . '">' . "\n";
	}
});
