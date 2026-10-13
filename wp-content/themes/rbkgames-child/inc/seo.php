<?php
/**
 * SEO title / meta description for migrated posts (stored in _rbk_seo_title / _rbk_seo_desc).
 */
if (!defined('ABSPATH')) {
	exit;
}
add_filter('pre_get_document_title', function ($t) {
	if (is_singular('post')) {
		$s = get_post_meta(get_queried_object_id(), '_rbk_seo_title', true);
		if ($s) {
			return $s;
		}
	}
	return $t;
}, 20);
add_action('wp_enqueue_scripts', function () {
	if (is_singular('post')) {
		$f = get_stylesheet_directory() . '/assets/remote-images.js';
		wp_enqueue_script('rbkgames_child_remote_images', get_stylesheet_directory_uri() . '/assets/remote-images.js', [], (string) filemtime($f), true);
	}
});
add_action('wp_head', function () {
	if (is_singular('post')) {
		$d = get_post_meta(get_queried_object_id(), '_rbk_seo_desc', true);
		if ($d) {
			echo '<meta name="description" content="' . esc_attr($d) . '">' . "\n";
			echo '<meta property="og:description" content="' . esc_attr($d) . '">' . "\n";
		}
	}
}, 5);
