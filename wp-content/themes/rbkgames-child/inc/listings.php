<?php
/**
 * Section indexes and game hubs: /news/, /publications/, /publications/articles/, /publications/reviews/,
 * /games/ and /games/{game}/ - dynamic queries with the original pagination URLs (…/page/N/).
 */

if (!defined('ABSPATH')) {
	exit;
}

add_action('init', function () {
	add_rewrite_tag('%rbk_list%', '([a-z]+)');
	$rules = [
		'^news/page/([0-9]+)/?$'                            => 'index.php?rbk_list=news&paged=$matches[1]',
		'^news/?$'                                          => 'index.php?rbk_list=news',
		'^publications/page/([0-9]+)/?$'                    => 'index.php?rbk_list=articles&paged=$matches[1]',
		'^publications/?$'                                  => 'index.php?rbk_list=articles',
		'^publications/(articles|reviews)/page/([0-9]+)/?$' => 'index.php?rbk_list=$matches[1]&paged=$matches[2]',
		'^publications/(articles|reviews)/?$'               => 'index.php?rbk_list=$matches[1]',
		'^games/page/([0-9]+)/?$'                           => 'index.php?rbk_list=games&paged=$matches[1]',
		'^games/?$'                                         => 'index.php?rbk_list=games',
	];
	foreach (array_reverse($rules, true) as $re => $q) {
		add_rewrite_rule($re, $q, 'top');
	}
}, 20);

/* One-time rewrite flush when the rules change. */
add_action('init', function () {
	$v = '1';
	if (get_option('rbkgames_child_listing_rules') !== $v) {
		flush_rewrite_rules(false);
		update_option('rbkgames_child_listing_rules', $v);
	}
}, 99);

/* Listing views use the rbk-listing block template. */
add_filter('index_template_hierarchy', function ($t) {
	return get_query_var('rbk_list') ? ['rbk-listing'] : $t;
});
add_filter('home_template_hierarchy', function ($t) {
	return get_query_var('rbk_list') ? ['rbk-listing'] : $t;
});
add_filter('frontpage_template_hierarchy', function ($t) {
	return get_query_var('rbk_list') ? ['rbk-listing'] : $t;
});
add_filter('single_template_hierarchy', function ($t) {
	return is_singular('game') ? ['rbk-listing'] : $t;
});

/* The lists run their own queries; keep the main one cheap. */
add_action('pre_get_posts', function ($q) {
	if ($q->is_main_query() && !is_admin() && $q->get('rbk_list')) {
		$q->set('posts_per_page', 1);
		$q->set('no_found_rows', true);
	}
});
add_filter('redirect_canonical', function ($url) {
	return get_query_var('rbk_list') ? false : $url;
});
add_filter('pre_handle_404', function ($pre, $q) {
	return ($q->get('rbk_list') && $q->is_main_query()) ? true : $pre;
}, 10, 2);

add_action('wp_enqueue_scripts', function () {
	if (get_query_var('rbk_list') || is_singular('game')) {
		$f = get_stylesheet_directory() . '/assets/listing.css';
		wp_enqueue_style('rbkgames-child-listing', get_stylesheet_directory_uri() . '/assets/listing.css', ['rbkgames-child-layout'], (string) filemtime($f));
	}
}, 40);

add_filter('pre_get_document_title', function ($t) {
	$map = ['news' => 'новости', 'articles' => 'Статьи', 'reviews' => 'Обзоры', 'games' => 'База игр для ПК, PS5, Xbox на Robokot Games'];
	$l   = get_query_var('rbk_list');
	return $l && isset($map[$l]) ? $map[$l] . ' | ROBOKOT Games' : $t;
});

function rbkgames_child_pagination($base, $paged, $total) {
	if ($total < 2) {
		return '';
	}
	$url  = static function ($n) use ($base) {
		return esc_url(home_url($n > 1 ? $base . 'page/' . $n . '/' : $base));
	};
	$show = array_unique([1, 2, max(1, $paged - 1), $paged, min($total, $paged + 1), $total]);
	$show = array_filter($show, static function ($n) use ($total) {
		return $n >= 1 && $n <= $total;
	});
	sort($show);
	$o    = '<nav class="rbk-pager" aria-label="Pagination"><ul>';
	$prev = 0;
	foreach ($show as $n) {
		if ($prev && $n - $prev > 1) {
			$o .= '<li><span>…</span></li>';
		}
		$o   .= $n === $paged ? '<li class="is-current"><span>' . (int) $n . '</span></li>' : '<li><a href="' . $url($n) . '">' . (int) $n . '</a></li>';
		$prev = $n;
	}
	if ($paged < $total) {
		$o .= '<li title="Вперёд"><a href="' . $url($paged + 1) . '">&gt;</a></li><li title="На последнюю страницу"><a href="' . $url($total) . '">»</a></li>';
	}
	return $o . '</ul></nav>';
}

function rbkgames_child_ru_date($id) {
	$m = ['Jan' => 'Янв', 'Feb' => 'Фев', 'Mar' => 'Мар', 'Apr' => 'Апр', 'May' => 'Май', 'Jun' => 'Июн', 'Jul' => 'Июл', 'Aug' => 'Авг', 'Sep' => 'Сен', 'Oct' => 'Окт', 'Nov' => 'Ноя', 'Dec' => 'Дек'];
	return strtr(get_the_date('d M, Y', $id), $m);
}

function rbkgames_child_card($post, $with_excerpt = true) {
	$id   = $post->ID;
	$img  = get_the_post_thumbnail($id, 'medium_large', ['loading' => 'lazy', 'alt' => '']);
	$lead = get_post_meta($id, '_rbk_lead', true);
	$text = $lead ?: wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_excerpt ?: $post->post_content)), 24, '…');
	$link = esc_url(get_permalink($id));
	$out  = '<article class="rbk-lcard"><a class="rbk-lcard__img" href="' . $link . '" tabindex="-1" aria-hidden="true">' . $img . '</a>';
	$out .= '<div class="rbk-lcard__body"><h3><a href="' . $link . '">' . esc_html(get_the_title($id)) . '</a></h3>';
	if ($with_excerpt && $text) {
		$out .= '<p>' . esc_html($text) . '</p>';
	}
	return $out . '<time datetime="' . esc_attr(get_the_date('c', $id)) . '">' . esc_html(rbkgames_child_ru_date($id)) . '</time></div></article>';
}

function rbkgames_child_crumbs(array $trail) {
	$parts = [];
	foreach ($trail as $c) {
		$parts[] = !empty($c[1]) ? '<a href="' . esc_url($c[1]) . '">' . esc_html($c[0]) . '</a>' : '<span>' . esc_html($c[0]) . '</span>';
	}
	return '<nav class="rbk-crumbs" aria-label="Breadcrumb">' . implode(' <i>/</i> ', $parts) . '</nav>';
}

/* Posts of a game hub (by original path /games/{slug}/news|article/…). */
function rbkgames_child_game_posts($slug, $cats, $limit) {
	return new WP_Query([
		'post_type'           => 'post',
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => true,
		'category_name'       => implode(',', $cats),
		'meta_query'          => [[
			'key'     => '_rbk_source_path',
			'value'   => '/games/' . $slug . '/',
			'compare' => 'LIKE',
		]],
	]);
}

add_shortcode('rbk_listing', function () {
	if (is_singular('game')) {
		return rbkgames_child_game_hub(get_queried_object());
	}
	$type  = get_query_var('rbk_list');
	$paged = max(1, (int) get_query_var('paged'));
	if ($type === 'games') {
		$q  = new WP_Query(['post_type' => 'game', 'posts_per_page' => 24, 'paged' => $paged, 'orderby' => 'title', 'order' => 'ASC']);
		$o  = rbkgames_child_crumbs([['RBK Games', home_url('/')], ['Games', '']]);
		$o .= '<h1 class="rbk-ltitle">Подборка видеоигр для ПК, PS5, Xbox на Robokot Games</h1><p class="rbk-lintro">Все видеоигры для PC, PS5, Xbox, Nintendo Switch по жанрам, дате выхода, издателям и разработчикам. Ежедневное обновление и пополнение каталога компьютерных игр со скриншотами, трейлерами и отзывами.</p>';
		$o .= '<div class="rbk-posters">';
		foreach ($q->posts as $g) {
			$o .= '<a class="rbk-poster" href="' . esc_url(get_permalink($g)) . '"><span class="rbk-poster__img">' . get_the_post_thumbnail($g, 'medium', ['loading' => 'lazy', 'alt' => '']) . '</span><span class="rbk-poster__t">' . esc_html(get_the_title($g)) . '</span></a>';
		}
		return $o . '</div>' . rbkgames_child_pagination('/games/', $paged, (int) $q->max_num_pages);
	}
	$bases = ['news' => ['/news/', 'новости'], 'articles' => ['/publications/', 'Статьи'], 'reviews' => ['/publications/reviews/', 'Обзоры']];
	if (!isset($bases[$type])) {
		return '';
	}
	$q  = new WP_Query(['post_type' => 'post', 'posts_per_page' => 24, 'paged' => $paged, 'category_name' => $type, 'ignore_sticky_posts' => true]);
	$o  = rbkgames_child_crumbs([['RBK Games', home_url('/')], [$bases[$type][1], '']]);
	$o .= $type === 'news' ? '<h1 class="rbk-ltitle">Игровые новости.</h1>' : '<h1 class="screen-reader-text">' . esc_html($bases[$type][1]) . '</h1>';
	$o .= '<div class="rbk-lgrid">';
	foreach ($q->posts as $p) {
		$o .= rbkgames_child_card($p);
	}
	$base = $bases[$type][0];
	if ($type === 'articles' && 0 === strpos(trailingslashit(wp_parse_url(add_query_arg([]), PHP_URL_PATH)), '/publications/articles/')) {
		$base = '/publications/articles/';
	}
	return $o . '</div>' . rbkgames_child_pagination($base, $paged, (int) $q->max_num_pages);
});

function rbkgames_child_game_hub($game) {
	$o  = rbkgames_child_crumbs([['RBK Games', home_url('/')], ['Games', home_url('/games/')], [get_the_title($game), '']]);
	$o .= '<h1 class="rbk-ltitle">' . esc_html(get_the_title($game)) . '</h1>';
	$content = trim((string) $game->post_content);
	if (has_post_thumbnail($game) || $content !== '') {
		$o .= '<div class="rbk-hub-top">';
		if (has_post_thumbnail($game)) {
			$o .= '<div class="rbk-hub-cover">' . get_the_post_thumbnail($game, 'medium') . '</div>';
		}
		if ($content !== '') {
			$o .= '<div class="rbk-hub-text">' . apply_filters('the_content', $content) . '</div>';
		}
		$o .= '</div>';
	}
	$pub = rbkgames_child_game_posts($game->post_name, ['articles', 'reviews'], 8);
	if ($pub->posts) {
		$o .= '<h2 class="rbk-hub-h">Публикации</h2><div class="rbk-lgrid rbk-lgrid--2">';
		foreach ($pub->posts as $p) {
			$o .= rbkgames_child_card($p, false);
		}
		$o .= '</div><p class="rbk-hub-more"><a href="' . esc_url(home_url('/publications/')) . '">Все публикации</a></p>';
	}
	$news = rbkgames_child_game_posts($game->post_name, ['news'], 8);
	if ($news->posts) {
		$o .= '<h2 class="rbk-hub-h">Новости</h2><div class="rbk-nlist">';
		foreach ($news->posts as $p) {
			$o .= rbkgames_child_card($p);
		}
		$o .= '</div><p class="rbk-hub-more"><a href="' . esc_url(home_url('/news/')) . '">Все новости</a></p>';
	}
	return $o;
}
