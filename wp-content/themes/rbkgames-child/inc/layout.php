<?php
/**
 * Shared layout: header (top bar, quick bar, mobile menu, search modal) and footer,
 * modelled on https://rbkgames.com/. Menu and footer data live in data/*.json.
 */

if (!defined('ABSPATH')) {
	exit;
}

function rbkgames_child_data($name) {
	$file = get_stylesheet_directory() . '/data/' . $name . '.json';
	$data = is_readable($file) ? json_decode((string) file_get_contents($file), true) : [];
	return is_array($data) ? $data : [];
}

function rbkgames_child_url($u) {
	if ($u === '#' || $u === '') {
		return '#';
	}
	if (preg_match('#^https?://#', $u)) {
		return esc_url($u);
	}
	return esc_url(home_url($u));
}

function rbkgames_child_menu() {
	return apply_filters('rbkgames_child_menu_items', rbkgames_child_data('menu'));
}

function rbkgames_child_arrow() {
	return '<svg class="dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" aria-hidden="true"><path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function rbkgames_child_groups_html(array $groups) {
	$out = '';
	foreach ($groups as $g) {
		$out .= '<ul class="submenu-group">';
		if (!empty($g['h'])) {
			$out .= '<li class="dropdown-menu-heading"><a href="' . rbkgames_child_url($g['h']['u']) . '" class="main-link">' . esc_html($g['h']['t']) . '</a></li>';
		}
		foreach ($g['i'] as $i) {
			$out .= '<li><a href="' . rbkgames_child_url($i['u']) . '" class="main-link">' . esc_html($i['t']) . '</a></li>';
		}
		$out .= '</ul>';
	}
	return $out;
}

function rbkgames_child_header() {
	$menu = rbkgames_child_menu();
	$logo = get_stylesheet_directory_uri() . '/assets/rbkgames-header-logo.svg';
	$bar  = [
		['Roblox', '/games/roblox/', 'red'], ['inZOI', '/games/inzoi/', 'yellow'], ['Marvel Rivals', '/games/marvel-rivals/', 'white'],
		['MINECRAFT', '/games/minecraft/articles/download-minecraft-pe/', 'magenta'],
		['The Last of Us Part 2', '/games/the-last-of-us-part-2/', 'skyblue'], ['GTA 6', '/games/gta-6/', 'red'],
		['Наши игры', '/brauzernye-igry/', 'pink'],
	];
	$search = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><g fill="none" stroke="#71b829" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></g></svg>';

	$desktop = '';
	$mobile  = '';
	foreach ($menu as $i) {
		$has   = !empty($i['g']);
		$href  = rbkgames_child_url($i['u']);
		$label = esc_html($i['t']);
		$desktop .= '<li class="' . ($has ? 'dropdown' : '') . '"><a href="' . $href . '" class="main-link">' . $label . '</a>';
		if ($has) {
			$desktop .= '<div class="dropdown-menu">' . rbkgames_child_groups_html($i['g']) . '</div>';
		}
		$desktop .= '</li>';

		if ($has) {
			$mobile .= '<li class="dropdown"><div class="dropdown-wrapper"><a href="' . $href . '" class="main-link">' . $label . '</a><button type="button" class="dropdown-toggle" aria-expanded="false" aria-label="' . esc_attr('Показать подменю ' . $i['t']) . '">' . rbkgames_child_arrow() . '</button></div>';
			$mobile .= '<div class="mobile-dropdown-menu"><div class="menu">' . rbkgames_child_groups_html($i['g']) . '</div></div></li>';
		} else {
			$mobile .= '<li><a href="' . $href . '" class="main-link">' . $label . '</a></li>';
		}
	}
	$desktop .= '<li><button class="search-button" type="button" aria-label="Кнопка поиска" data-rbk-search-open>' . $search . '</button></li>';

	$quick = '';
	foreach ($bar as $b) {
		$quick .= '<a href="' . esc_url(home_url($b[1])) . '" class="link ' . esc_attr($b[2]) . '">' . esc_html($b[0]) . '</a>';
	}

	ob_start();
	?>
<header id="top" class="rbk-header wp-block-template-part">
	<div class="centered unauthorized">
		<a href="<?php echo esc_url(home_url('/')); ?>" class="top-10 logo" aria-label="Home"><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="134" height="30"></a>
		<ul class="top-nav rbk-nav"><?php echo $desktop; // phpcs:ignore WordPress.Security.EscapeOutput ?></ul>
		<a href="#" class="sandwich-link rbk-burger" aria-label="Главное меню" aria-expanded="false" role="button"><span></span></a>
		<div class="mobile-nav-wrapper">
			<div class="centered">
				<ul class="mobile-nav"><?php echo $mobile; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<li><button class="search-button" type="button" aria-label="Кнопка поиска" data-rbk-search-open><?php echo $search; // phpcs:ignore WordPress.Security.EscapeOutput ?></button></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="quick-bar">
		<div class="centered"><span class="header">Центр внимания:</span><?php echo $quick; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
</header>
<div class="rbk-search-modal" id="search-modal" aria-hidden="true" hidden>
	<button type="button" class="modal-close" aria-label="Закрыть" data-rbk-search-close>&times;</button>
	<form method="get" role="search" action="<?php echo esc_url(home_url('/')); ?>" autocomplete="off">
		<input type="search" class="search-input" name="s" placeholder="Что ищем ?" aria-label="Поиск">
	</form>
</div>
	<?php
	return ob_get_clean();
}

function rbkgames_child_footer() {
	$data  = rbkgames_child_data('footer');
	$links = apply_filters('rbkgames_child_footer_items', $data['links'] ?? []);
	$menu  = '';
	foreach ($links as $i) {
		$menu .= '<li><a href="' . rbkgames_child_url($i['u']) . '" class="grey-link">' . esc_html($i['t']) . '</a></li>';
	}
	$social = '';
	foreach ($data['social'] ?? [] as $s) {
		$social .= '<a href="' . esc_url($s['u']) . '" target="_blank" rel="noopener" class="social-link" aria-label="' . esc_attr($s['n']) . '">' . $s['svg'] . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted bundled SVG.
	}
	$logo = get_stylesheet_directory_uri() . '/assets/rbkgames-footer-logo.svg';
	return '<footer id="footer" class="footer rbk-footer wp-block-template-part"><div class="centered si-justify"><div class="footer-container">'
		. '<nav class="footer-nav-wrapper" aria-label="Footer"><ul class="rbkgames-footer-menu">' . $menu . '</ul></nav>'
		. '<div class="footer-socials"><a href="' . esc_url(home_url('/')) . '" class="logo" aria-label="Home"><img src="' . esc_url($logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '" width="132" height="30"></a>'
		. '<div class="footer-social">' . $social . '</div></div>'
		. '</div></div></footer>';
}

add_filter('render_block', function ($content, $block) {
	if (($block['blockName'] ?? '') === 'core/template-part') {
		$slug = $block['attrs']['slug'] ?? '';
		if ($slug === 'header') {
			return rbkgames_child_header();
		}
		if ($slug === 'footer') {
			return rbkgames_child_footer();
		}
	}
	return $content;
}, 20, 2);

add_action('wp_enqueue_scripts', function () {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	wp_enqueue_style('rbkgames-child-layout', $uri . '/assets/layout.css', ['rbkgames-child-style'], (string) filemtime($dir . '/assets/layout.css'));
	wp_enqueue_script('rbkgames-child-layout', $uri . '/assets/layout.js', [], (string) filemtime($dir . '/assets/layout.js'), true);
}, 30);
