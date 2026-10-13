# Content model (inc/content-model.php)

- Permalink structure: `/%postname%/` (posts and pages resolve at root `/{slug}/`).
- Post type `post` = all articles/news/reviews. Section = category with slug `news`, `articles`, `reviews`.
  Canonical URLs: /news/{slug}/, /publications/articles/{slug}/, /publications/reviews/{slug}/.
- Rewrites (all map to `index.php?name={slug}`): the three bases above and
  /games/{game}/(news|article|articles|reviews)/{slug}/.
- Post meta `_rbk_source_path` (e.g. `/games/foo/news/bar/`) overrides the generated permalink – set it on import
  for any post whose original URL differs (game sub-paths).
- Post type `game` (slug base `games`): hub at /games/{game}/. Link posts to a game via meta `_rbk_game` (game slug).
- Pages: normal WP pages at root, e.g. /about/.
- Post slugs must be unique across posts (lookup by name only).
