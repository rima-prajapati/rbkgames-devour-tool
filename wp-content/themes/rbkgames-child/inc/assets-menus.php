<?php
/**
 * SVG uploads, menu locations and menu-driven header/footer data.
 */

if (!defined('ABSPATH')) {
	exit;
}

add_filter('upload_mimes', function ($m) {
	if (current_user_can('manage_options') || (defined('WP_CLI') && WP_CLI)) {
		$m['svg'] = 'image/svg+xml';
	}
	return $m;
});
add_filter('wp_check_filetype_and_ext', function ($d, $file, $name) {
	if (substr($name, -4) === '.svg') {
		return ['ext' => 'svg', 'type' => 'image/svg+xml', 'proper_filename' => $name];
	}
	return $d;
}, 10, 3);

add_action('after_setup_theme', function () {
	register_nav_menus([
		'rbk_header' => 'Header',
		'rbk_footer' => 'Footer',
	]);
});

/**
 * Convert a registered menu to the nested array used by the header renderer.
 */
function rbkgames_child_menu_tree($menu_name) {
	$menu = wp_get_nav_menu_object($menu_name);
	if (!$menu) {
		return [];
	}
	$items = wp_get_nav_menu_items($menu->term_id);
	if (!$items) {
		return [];
	}
	$home = untrailingslashit(home_url());
	$by   = [];
	foreach ($items as $i) {
		$u = $i->url;
		if (strpos($u, $home) === 0) {
			$u = substr($u, strlen($home)) ?: '/';
		}
		$by[(int) $i->ID] = ['t' => $i->title, 'u' => $u, 'c' => [], 'p' => (int) $i->menu_item_parent];
	}
	$tree = [];
	foreach ($by as $id => &$n) {
		if ($n['p'] && isset($by[$n['p']])) {
			$by[$n['p']]['c'][] = &$n;
		} else {
			$tree[] = &$n;
		}
	}
	unset($n);
	return $tree;
}

// The header menu (with its grouped dropdown panels) and the footer links come from
// data/menu.json and data/footer.json, copied from rbkgames.com. The WP menus are no
// longer used as a source because they cannot express the grouped submenus.
