<?php
/**
 * Title: Article card grid
 * Slug: rbkgames-child/article-card-grid
 * Categories: query
 * Inserter: yes
 */
?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:heading -->
<h2 class="wp-block-heading">Статьи</h2>
<!-- /wp:heading -->
<!-- wp:query {"query":{"perPage":9,"postType":"post","order":"desc","orderBy":"date","inherit":false},"layout":{"type":"default"}} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"medium"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
