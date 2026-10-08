<?php
/**
 * Title: Footer
 * Slug: top-appliance/footer
 * Categories: top-chrome
 * Description: NAP, hours, service and area links, social links and copyright (all from Business Info / post types).
 * Viewport Width: 1440
 */
?>
<!-- wp:group {"className":"ta-footer","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer"><!-- wp:group {"className":"ta-footer__grid ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__grid ta-wrap"><!-- wp:group {"className":"ta-footer__brand","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__brand"><!-- wp:top/brand {"variant":"footer"} /-->

<!-- wp:paragraph {"className":"ta-footer__about"} -->
<p class="ta-footer__about">Same-day repair for refrigerators, washers, dryers, dishwashers, ovens and more, across Houston and nearby cities.</p>
<!-- /wp:paragraph -->

<!-- wp:top/social-links /--></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"nav","className":"ta-footer__col","layout":{"type":"default"},"ariaLabel":"Repair services"} -->
<nav class="wp-block-group ta-footer__col" aria-label="Repair services"><!-- wp:heading {"className":"ta-footer__title"} -->
<h2 class="wp-block-heading ta-footer__title">Repair services</h2>
<!-- /wp:heading -->

<!-- wp:top/post-links {"source":"service"} /--></nav>
<!-- /wp:group -->

<!-- wp:group {"tagName":"nav","className":"ta-footer__col","layout":{"type":"default"},"ariaLabel":"Service areas"} -->
<nav class="wp-block-group ta-footer__col" aria-label="Service areas"><!-- wp:heading {"className":"ta-footer__title"} -->
<h2 class="wp-block-heading ta-footer__title">Service areas</h2>
<!-- /wp:heading -->

<!-- wp:top/post-links {"source":"service_area"} /--></nav>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-footer__col ta-footer__contact","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__col ta-footer__contact"><!-- wp:heading {"className":"ta-footer__title"} -->
<h2 class="wp-block-heading ta-footer__title">Contact</h2>
<!-- /wp:heading -->

<!-- wp:top/business-info /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-footer__bottom ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__bottom ta-wrap"><!-- wp:top/copyright /-->

<!-- wp:paragraph {"className":"ta-footer__legal"} -->
<p class="ta-footer__legal"><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About us</a> <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact us</a> <a href="<?php echo esc_url( home_url( '/sitemap_index.xml' ) ); ?>">Sitemap</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
