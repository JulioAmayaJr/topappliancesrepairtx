<?php
/**
 * Title: Brands we service
 * Slug: top-appliance/brands
 * Categories: top-sections
 * Description: Brand wall (from the Brands post type) with a link to all brands.
 * Viewport Width: 1440
 */
?>
<!-- wp:group {"tagName":"section","anchor":"brands","className":"ta-section ta-section--tint ta-brands-sec","layout":{"type":"default"}} -->
<section id="brands" class="wp-block-group ta-section ta-section--tint ta-brands-sec"><!-- wp:group {"className":"ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-wrap"><!-- wp:group {"className":"ta-section__head","layout":{"type":"default"}} -->
<div class="wp-block-group ta-section__head"><!-- wp:heading -->
<h2 class="wp-block-heading">All major brands, including Sub-Zero</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ta-section__lede"} -->
<p class="ta-section__lede">Everyday and high-end appliances, freestanding or built in. If your brand isn’t listed, call us anyway.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:top/brand-grid /-->

<!-- wp:paragraph {"className":"ta-more"} -->
<p class="ta-more"><a href="<?php echo esc_url( get_post_type_archive_link( 'brand' ) ?: home_url( '/brands/' ) ); ?>">See all brands we service</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
