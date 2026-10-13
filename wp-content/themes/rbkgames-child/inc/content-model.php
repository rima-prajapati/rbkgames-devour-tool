<?php
/**
 * Content model: original URL patterns for posts (news / articles / reviews) and the "game" post type.
 */

if (!defined('ABSPATH')) {
	exit;
}

function rbkgames_child_post_bases() {
	return [
		'news'     => 'news',
		'articles' => 'publications/articles',
		'reviews'  => 'publications/reviews',
	];
}

add_action('init', function () {
	register_post_type('game', [
		'label'        => 'Игры',
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'menu_icon'    => 'dashicons-games',
		'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
		'rewrite'      => ['slug' => 'games', 'with_front' => false],
	]);
	add_rewrite_rule('^games/[^/]+/(?:news|article|articles|reviews)/([^/]+)/?$', 'index.php?name=$matches[1]', 'top');
	foreach (rbkgames_child_post_bases() as $base) {
		add_rewrite_rule('^' . $base . '/([^/]+)/?$', 'index.php?name=$matches[1]', 'top');
	}
});

add_filter('post_link', function ($url, $post) {
	if ($post->post_type !== 'post') {
		return $url;
	}
	$src = get_post_meta($post->ID, '_rbk_source_path', true);
	if ($src) {
		return home_url($src);
	}
	$bases = rbkgames_child_post_bases();
	foreach (get_the_category($post->ID) as $cat) {
		if (isset($bases[$cat->slug])) {
			return home_url('/' . $bases[$cat->slug] . '/' . $post->post_name . '/');
		}
	}
	return $url;
}, 10, 2);

/* Russian month abbreviations for post dates ("30 Сен, 2026"). */
add_filter('render_block_core/post-date', function ($content) {
	$map = [
		'Jan' => 'Янв', 'Feb' => 'Фев', 'Mar' => 'Мар', 'Apr' => 'Апр', 'May' => 'Май', 'Jun' => 'Июн',
		'Jul' => 'Июл', 'Aug' => 'Авг', 'Sep' => 'Сен', 'Oct' => 'Окт', 'Nov' => 'Ноя', 'Dec' => 'Дек',
	];
	return strtr($content, $map);
});
