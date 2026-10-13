<?php
/**
 * Child theme of rbkgames. Custom code added by DEVOUR tasks.
 * Each feature lives in its own file in inc/ and is loaded automatically.
 */

if (!defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', function () {
	// The parent enqueues get_stylesheet_uri() (now this child's style.css) with its own version;
	// load the parent stylesheet explicitly and this one with a cache-busting version.
	wp_dequeue_style('rbkgames-style');
	wp_enqueue_style('rbkgames-parent-style', get_template_directory_uri() . '/style.css', [], wp_get_theme(get_template())->get('Version'));
	wp_enqueue_style('rbkgames-child-style', get_stylesheet_uri(), ['rbkgames-parent-style'], (string) filemtime(__DIR__ . '/style.css'));
}, 20);

foreach (glob(__DIR__ . '/inc/*.php') ?: [] as $wpm_child_file) {
	require_once $wpm_child_file;
}
