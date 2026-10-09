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
<div class="wp-block-group ta-wrap"><!-- wp:group {"className":"ta-section__head ta-section__head--center","layout":{"type":"default"}} -->
<div class="wp-block-group ta-section__head ta-section__head--center"><!-- wp:paragraph {"className":"ta-eyebrow"} -->
<p class="ta-eyebrow">Brands we service</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">We Repair All Major Appliance Brands</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ta-section__lede"} -->
<p class="ta-section__lede">From everyday Whirlpool and GE to built-in Sub-Zero and Thermador. If your brand isn’t listed, call us anyway.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:top/brand-grid /-->

<!-- wp:paragraph {"className":"ta-more ta-more--center"} -->
<p class="ta-more ta-more--center"><a href="<?php echo esc_url( get_post_type_archive_link( 'brand' ) ?: home_url( '/brands/' ) ); ?>">View all brands →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
