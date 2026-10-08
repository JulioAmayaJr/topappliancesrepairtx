<?php
/**
 * Title: Resources
 * Slug: top-appliance/resources
 * Categories: top-sections
 * Description: Error codes by appliance and the latest blog posts.
 * Viewport Width: 1440
 */
$blog = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' );
$codes = get_post_type_archive_link( 'error_code' ) ?: home_url( '/error-codes/' );
?>
<!-- wp:group {"tagName":"section","anchor":"resources","className":"ta-section ta-resources","layout":{"type":"default"}} -->
<section id="resources" class="wp-block-group ta-section ta-resources"><!-- wp:group {"className":"ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-wrap"><!-- wp:group {"className":"ta-section__head","layout":{"type":"default"}} -->
<div class="wp-block-group ta-section__head"><!-- wp:heading -->
<h2 class="wp-block-heading">Repair help and resources</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ta-section__lede"} -->
<p class="ta-section__lede">Check what an error code means before you call, or read practical tips from our technicians.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-resources__grid","layout":{"type":"default"}} -->
<div class="wp-block-group ta-resources__grid"><!-- wp:group {"className":"ta-codes","layout":{"type":"default"}} -->
<div class="wp-block-group ta-codes"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Appliance error codes</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Find out what the code on your washer, dryer, dishwasher, refrigerator or oven display means and what to do next.</p>
<!-- /wp:paragraph -->

<!-- wp:top/post-links {"source":"appliance","label":"Error codes by appliance"} /-->

<!-- wp:paragraph {"className":"ta-more"} -->
<p class="ta-more"><a href="<?php echo esc_url( $codes ); ?>">Browse all error codes</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-latest","layout":{"type":"default"}} -->
<div class="wp-block-group ta-latest"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Latest repair tips</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":7,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"className":"ta-posts"} -->
<div class="wp-block-query ta-posts"><!-- wp:post-template {"className":"ta-posts__list","layout":{"type":"default"}} -->
<!-- wp:post-featured-image {"aspectRatio":"16/9","sizeSlug":"card","className":"ta-post__img"} /-->

<!-- wp:group {"className":"ta-post__body","layout":{"type":"default"}} -->
<div class="wp-block-group ta-post__body"><!-- wp:post-title {"level":4,"isLink":true,"className":"ta-post__title"} /-->

<!-- wp:post-date {"className":"ta-post__date"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query -->

<!-- wp:paragraph {"className":"ta-more"} -->
<p class="ta-more"><a href="<?php echo esc_url( $blog ); ?>">Read all repair tips on the blog</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
