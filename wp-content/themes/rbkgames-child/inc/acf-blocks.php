<?php
/**
 * ACF blocks for the Home page (needs ACF Pro for block registration).
 * Blocks: acf/rbk-featured (editor's choice), acf/rbk-post-grid (games / articles / news / reviews).
 * Markup reuses the classes of assets/home.css so the design is unchanged.
 */

if (!defined('ABSPATH')) {
	exit;
}

/** True when ACF Pro (block support) is active. */
function rbkgames_child_acf_blocks_ready() {
	return function_exists('acf_register_block_type') || (defined('ACF_PRO') && ACF_PRO) || class_exists('ACF_Blocks') || function_exists('acf_get_block_types');
}

/** Read an ACF field with a default; works also when ACF is missing. */
function rbkgames_child_f($key, $default = '') {
	if (!function_exists('get_field')) {
		return $default;
	}
	$v = get_field($key);
	return ($v === null || $v === '' || $v === false) ? $default : $v;
}

add_action('init', function () {
	if (!rbkgames_child_acf_blocks_ready()) {
		// Fallback until ACF Pro is active: same block names, rendered natively from the saved ACF data.
		$cb = [
			'rbk-featured' => function ($attrs) {
				return rbkgames_child_render_featured((array) ($attrs['data'] ?? []));
			},
			'rbk-post-grid' => function ($attrs) {
				ob_start();
				rbkgames_child_render_grid((array) ($attrs['data'] ?? []));
				return ob_get_clean();
			},
		];
		foreach ($cb as $slug => $fn) {
			register_block_type(get_stylesheet_directory() . '/blocks/' . $slug, [
				'attributes' => ['name' => ['type' => 'string'], 'data' => ['type' => 'object'], 'mode' => ['type' => 'string']],
				'render_callback' => $fn,
			]);
		}
		return;
	}
	foreach (['rbk-featured', 'rbk-post-grid'] as $slug) {
		register_block_type(get_stylesheet_directory() . '/blocks/' . $slug);
	}
}, 20);

/** Tell the editor scripts to register the native editor UI when ACF Pro is not active. */
add_action('enqueue_block_editor_assets', function () {
	if (!rbkgames_child_acf_blocks_ready()) {
		wp_register_script('rbkgames-child-native-flag', '', [], '1.0', false);
		wp_enqueue_script('rbkgames-child-native-flag');
		wp_add_inline_script('rbkgames-child-native-flag', 'window.rbkChildNativeBlocks=true;');
	}
}, 5);

add_action('acf/init', function () {
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group([
		'key' => 'group_rbk_featured',
		'title' => 'Block: Editor\'s choice',
		'fields' => [
			['key' => 'field_rbk_f_h1', 'label' => 'Main heading (H1)', 'name' => 'heading', 'type' => 'text', 'default_value' => 'Онлайн игры и новости из мира игр'],
			['key' => 'field_rbk_f_title', 'label' => 'Section title', 'name' => 'section_title', 'type' => 'text', 'default_value' => 'Выбор редакции'],
			['key' => 'field_rbk_f_count', 'label' => 'Number of posts', 'name' => 'count', 'type' => 'number', 'default_value' => 5, 'min' => 1, 'max' => 9],
			['key' => 'field_rbk_f_excerpt', 'label' => 'Excerpt words (first card)', 'name' => 'excerpt_length', 'type' => 'number', 'default_value' => 25],
		],
		'location' => [[['param' => 'block', 'operator' => '==', 'value' => 'acf/rbk-featured']]],
	]);
	acf_add_local_field_group([
		'key' => 'group_rbk_post_grid',
		'title' => 'Block: Post grid',
		'fields' => [
			['key' => 'field_rbk_g_title', 'label' => 'Section title', 'name' => 'section_title', 'type' => 'text', 'default_value' => 'Статьи'],
			['key' => 'field_rbk_g_link', 'label' => 'Title link', 'name' => 'title_link', 'type' => 'url'],
			['key' => 'field_rbk_g_type', 'label' => 'Layout / post type', 'name' => 'layout', 'type' => 'select', 'choices' => ['cards' => 'Article cards (posts)', 'games' => 'Game posters (games)'], 'default_value' => 'cards', 'return_format' => 'value'],
			['key' => 'field_rbk_g_cat', 'label' => 'Category (posts only)', 'name' => 'category', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'select', 'allow_null' => 1, 'return_format' => 'id', 'add_term' => 0, 'save_terms' => 0, 'load_terms' => 0],
			['key' => 'field_rbk_g_count', 'label' => 'Number of posts', 'name' => 'count', 'type' => 'number', 'default_value' => 9, 'min' => 1, 'max' => 24],
			['key' => 'field_rbk_g_excerpt', 'label' => 'Excerpt words', 'name' => 'excerpt_length', 'type' => 'number', 'default_value' => 45],
			['key' => 'field_rbk_g_date', 'label' => 'Show date', 'name' => 'show_date', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1],
			['key' => 'field_rbk_g_btn_text', 'label' => 'Button text', 'name' => 'button_text', 'type' => 'text'],
			['key' => 'field_rbk_g_btn_url', 'label' => 'Button URL', 'name' => 'button_url', 'type' => 'url'],
			['key' => 'field_rbk_g_class', 'label' => 'Section CSS class', 'name' => 'section_class', 'type' => 'text', 'default_value' => 'rbk-articles', 'instructions' => 'rbk-articles, rbk-news, rbk-reviews or rbk-games'],
		],
		'location' => [[['param' => 'block', 'operator' => '==', 'value' => 'acf/rbk-post-grid']]],
	]);
});

/** Thumbnail wrapped as the core featured-image block does. */
function rbkgames_child_thumb($id, $size = 'large') {
	$img = get_the_post_thumbnail($id, $size);
	if (!$img) {
		return '<figure class="wp-block-post-featured-image"></figure>';
	}
	return '<figure class="wp-block-post-featured-image"><a href="' . esc_url(get_permalink($id)) . '">' . $img . '</a></figure>';
}

function rbkgames_child_title($id, $level = 3) {
	return '<h' . $level . ' class="wp-block-post-title"><a href="' . esc_url(get_permalink($id)) . '">' . esc_html(get_the_title($id)) . '</a></h' . $level . '>';
}

function rbkgames_child_excerpt($id, $words) {
	$t = wp_trim_words(wp_strip_all_tags(get_the_excerpt($id)), max(1, (int) $words), '…');
	return '<div class="wp-block-post-excerpt"><p class="wp-block-post-excerpt__excerpt">' . esc_html($t) . '</p></div>';
}

function rbkgames_child_render_featured($f = []) {
	$h1 = $f['heading'] ?? 'Онлайн игры и новости из мира игр';
	$title = $f['section_title'] ?? 'Выбор редакции';
	$count = (int) ($f['count'] ?? 5);
	$ex = (int) ($f['excerpt_length'] ?? 25);
	$sticky = get_option('sticky_posts');
	$posts = $sticky ? get_posts(['post_type' => 'post', 'post__in' => $sticky, 'posts_per_page' => $count, 'ignore_sticky_posts' => 1, 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false]) : [];
	ob_start();
	echo '<div class="rbk-home-top rbk-sec rbk-acf"><div class="wp-block-group rbk-headrow"><h1 class="wp-block-heading rbk-h1">' . esc_html($h1) . '</h1><h2 class="wp-block-heading rbk-sec-title">' . esc_html($title) . '</h2></div>';
	echo '<div class="wp-block-query rbk-q rbk-ec"><ul class="wp-block-post-template">';
	foreach ($posts as $p) {
		echo '<li class="wp-block-post"><div class="wp-block-group rbk-card">' . rbkgames_child_thumb($p->ID) . '<div class="wp-block-group rbk-card__body">' . rbkgames_child_title($p->ID) . rbkgames_child_excerpt($p->ID, $ex) . '</div></div></li>';
	}
	echo '</ul></div></div>';
	return ob_get_clean();
}

function rbkgames_child_render_grid($f = []) {
	$layout = ($f['layout'] ?? 'cards') === 'games' ? 'games' : 'cards';
	$cls = preg_replace('/[^a-z0-9_ -]/i', '', $f['section_class'] ?? ($layout === 'games' ? 'rbk-games' : 'rbk-articles'));
	$args = ['post_type' => $layout === 'games' ? 'game' : 'post', 'posts_per_page' => max(1, (int) ($f['count'] ?? 9)), 'ignore_sticky_posts' => 1, 'orderby' => 'date', 'order' => 'DESC'];
	if ($layout === 'cards' && !empty($f['category'])) {
		$args['cat'] = (int) $f['category'];
	}
	$q = new WP_Query($args);
	$show_date = !isset($f['show_date']) || $f['show_date'];
	echo '<div class="rbk-sec ' . esc_attr($cls) . ' rbk-acf">';
	if (!empty($f['section_title'])) {
		$t = esc_html($f['section_title']);
		if (!empty($f['title_link'])) {
			$t = '<a href="' . esc_url($f['title_link']) . '">' . $t . '</a>';
		}
		echo '<h2 class="wp-block-heading has-text-align-right rbk-sec-title">' . $t . '</h2>';
	}
	echo '<div class="wp-block-query rbk-q"><ul class="wp-block-post-template">';
	foreach ($q->posts as $p) {
		echo '<li class="wp-block-post"><div class="wp-block-group rbk-card">' . rbkgames_child_thumb($p->ID);
		if ($layout === 'games') {
			echo rbkgames_child_title($p->ID);
		} else {
			echo '<div class="wp-block-group rbk-card__body">' . rbkgames_child_title($p->ID) . rbkgames_child_excerpt($p->ID, $f['excerpt_length'] ?? 45);
			if ($show_date) {
				echo '<div class="wp-block-post-date"><time datetime="' . esc_attr(get_the_date('c', $p)) . '">' . esc_html(get_the_date('d M, Y', $p)) . '</time></div>';
			}
			echo '</div>';
		}
		echo '</div></li>';
	}
	echo '</ul></div>';
	if (!empty($f['button_text']) && !empty($f['button_url'])) {
		echo '<div class="wp-block-buttons is-content-justification-center is-layout-flex"><div class="wp-block-button is-style-outline rbk-more"><a class="wp-block-button__link wp-element-button" href="' . esc_url($f['button_url']) . '">' . esc_html($f['button_text']) . '</a></div></div>';
	}
	echo '</div>';
	wp_reset_postdata();
}

/**
 * Once ACF Pro is active, swap the Home page content to the ACF blocks (old content kept in meta).
 */
add_action('init', function () {
	if (get_option('rbkgames_child_home_acf_done')) {
		return;
	}
	$id = (int) get_option('page_on_front');
	if (!$id || !get_post($id)) {
		return;
	}
	$file = get_stylesheet_directory() . '/data/home-acf.html';
	if (!is_readable($file)) {
		return;
	}
	update_post_meta($id, '_rbk_legacy_content', get_post($id)->post_content);
	wp_update_post(['ID' => $id, 'post_content' => file_get_contents($file)]);
	update_option('rbkgames_child_home_acf_done', 1);
}, 30);
