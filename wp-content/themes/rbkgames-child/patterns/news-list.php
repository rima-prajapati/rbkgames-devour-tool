<?php
/**
 * Title: News list
 * Slug: rbkgames-child/news-list
 * Categories: query
 * Inserter: yes
 */
?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Новости</h2>
<!-- /wp:heading -->
<!-- wp:query {"query":{"perPage":9,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"medium"} /-->
<!-- wp:post-date /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
