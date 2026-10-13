<?php
/**
 * Render: RBK Post grid.
 */
if (!defined('ABSPATH')) {
	exit;
}
rbkgames_child_render_grid([
	'section_title' => rbkgames_child_f('section_title', ''),
	'title_link' => rbkgames_child_f('title_link', ''),
	'layout' => rbkgames_child_f('layout', 'cards'),
	'category' => rbkgames_child_f('category', 0),
	'count' => rbkgames_child_f('count', 9),
	'excerpt_length' => rbkgames_child_f('excerpt_length', 45),
	'show_date' => function_exists('get_field') ? get_field('show_date') : true,
	'button_text' => rbkgames_child_f('button_text', ''),
	'button_url' => rbkgames_child_f('button_url', ''),
	'section_class' => rbkgames_child_f('section_class', 'rbk-articles'),
]);
