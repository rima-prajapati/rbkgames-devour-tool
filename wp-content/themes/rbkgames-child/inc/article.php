<?php
/**
 * Single article template helpers: breadcrumbs, author line, comments note, related query, CSS.
 */

if (!defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', function () {
	if (is_singular('post')) {
		wp_enqueue_style(
			'rbkgames-child-article',
			get_stylesheet_directory_uri() . '/assets/article.css',
			['rbkgames-child-layout'],
			(string) filemtime(get_stylesheet_directory() . '/assets/article.css')
		);
	}
}, 40);

/* Breadcrumbs: RBK Games / Публикации / Статьи */
add_shortcode('rbk_breadcrumbs', function () {
	$sections = [
		'articles' => ['Статьи', '/publications/articles/'],
		'reviews'  => ['Обзоры', '/publications/reviews/'],
		'news'     => ['Новости', '/news/'],
	];
	$crumbs = [['RBK Games', home_url('/')]];
	$game   = (int) get_post_meta(get_the_ID(), '_rbk_game_id', true);
	if ($game && get_post_status($game)) {
		$crumbs[] = ['База игр', home_url('/games/')];
		$crumbs[] = [get_the_title($game), get_permalink($game)];
		$src      = (string) get_post_meta(get_the_ID(), '_rbk_source_path', true);
		$crumbs[] = [false !== strpos($src, '/article/') ? 'Статьи' : 'Новости', ''];
		$sections = [];
	}
	foreach (get_the_category() as $cat) {
		if (!$sections) {
			break;
		}
		if (isset($sections[$cat->slug])) {
			$crumbs[] = ['Публикации', ''];
			$crumbs[] = [$sections[$cat->slug][0], home_url($sections[$cat->slug][1])];
			break;
		}
	}
	$parts = [];
	foreach ($crumbs as $c) {
		$parts[] = $c[1] ? '<a href="' . esc_url($c[1]) . '">' . esc_html($c[0]) . '</a>' : '<span>' . esc_html($c[0]) . '</span>';
	}
	return '<nav class="rbk-crumbs" aria-label="Breadcrumb">' . implode(' <i>/</i> ', $parts) . '</nav>';
});

/* Lead paragraph shown under the title of game news. */
add_shortcode('rbk_lead', function () {
	$lead = get_post_meta(get_the_ID(), '_rbk_lead', true);
	return $lead ? '<p class="rbk-lead">' . esc_html($lead) . '</p>' : '';
});

/* Tags row. */
add_shortcode('rbk_tags', function () {
	$tags = get_the_tags();
	if (!$tags) {
		return '';
	}
	$out = '<div class="rbk-tags"><span>Теги:</span>';
	foreach ($tags as $t) {
		$out .= '<a href="' . esc_url(home_url('/tag/' . $t->slug . '/')) . '">' . esc_html($t->name) . '</a>';
	}
	return $out . '</div>';
});

/* Author line (avatar + name). Date comes from the post-date block. */
add_shortcode('rbk_author', function () {
	$img = get_stylesheet_directory_uri() . '/assets/author-rbkgames.jpg';
	return '<a class="rbk-author" href="' . esc_url(home_url('/editorial/rbkgames/')) . '"><img src="' . esc_url($img) . '" width="40" height="40" alt="" loading="lazy"><span>' . esc_html(get_post_meta(get_the_ID(), '_rbk_author', true) ?: 'Денис Морпорков') . '</span></a>';
});

/* Comments header + closed note (the core comments block renders the list/form when open). */
add_shortcode('rbk_comments_note', function () {
	$n   = (int) get_comments_number();
	$out = '<div class="rbk-comments-note"><h3>Комментарии: (' . $n . ') <span aria-hidden="true">&#8963;</span></h3>';
	if (!comments_open()) {
		$out .= '<p>Комментарии к этой записи закрыты.</p>';
	}
	return $out . '</div>';
});

/* Defer original-CDN images: src moves to data-rbk-src (attached by assets/remote-images.js after load). */
add_filter('the_content', function ($content) {
	if (!is_singular('post') || false === strpos($content, 'cdn.gamestatic.net')) {
		return $content;
	}
	return preg_replace_callback('#<img\b[^>]*>#i', function ($m) {
		$tag = $m[0];
		if (!preg_match('#\bsrc=(["\'])(https?:)?//cdn\.gamestatic\.net[^"\']*\1#i', $tag, $s)) {
			return $tag;
		}
		$orig = $s[0];
		$url  = preg_replace('#^src=["\']|["\']$#', '', $orig);
		$new  = preg_replace('#\bsrc=(["\'])[^"\']*\1#i', 'src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-rbk-src="' . esc_url($url) . '"', $tag, 1);
		return $new . '<noscript>' . $tag . '</noscript>';
	}, $content);
}, 20);

/* Related query (queryId 7): other posts, not the current one. */
add_filter('query_loop_block_query_vars', function ($query, $block) {
	$qid = isset($block->context['queryId']) ? (int) $block->context['queryId'] : 0;
	if ($qid === 7 && is_singular()) {
		$query['post__not_in']    = [get_the_ID()];
		$query['posts_per_page'] = 3;
		$query['rbk_limit']      = 3;
	}
	if ($qid === 8) {
		$query['posts_per_page'] = 4;
		$query['rbk_limit']      = 4;
	}
	return $query;
}, 10, 2);

/* Something on this site returns more rows than LIMIT; enforce the block's count. */
add_filter('the_posts', function ($posts, $q) {
	$limit = (int) $q->get('rbk_limit');
	if ($limit && count($posts) > $limit) {
		$posts = array_slice($posts, 0, $limit);
		$q->post_count = count($posts);
	}
	return $posts;
}, 999, 2);
