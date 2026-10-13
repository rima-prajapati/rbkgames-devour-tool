<?php
/**
 * Render: RBK Editor's choice.
 */
if (!defined('ABSPATH')) {
	exit;
}
echo rbkgames_child_render_featured([ // phpcs:ignore WordPress.Security.EscapeOutput
	'heading' => rbkgames_child_f('heading', 'Онлайн игры и новости из мира игр'),
	'section_title' => rbkgames_child_f('section_title', 'Выбор редакции'),
	'count' => rbkgames_child_f('count', 5),
	'excerpt_length' => rbkgames_child_f('excerpt_length', 25),
]);
