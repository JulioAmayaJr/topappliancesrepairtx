<?php
/**
 * Title: Footer
 * Slug: top-appliance/footer
 * Categories: top-chrome
 * Description: Four columns (brand, services, service areas, contact) + bottom bar. All data from Business Info and the post types.
 * Viewport Width: 1440
 */
$privacy = get_privacy_policy_url() ?: home_url( '/privacy-policy/' );
?>
<!-- wp:group {"className":"ta-footer","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer"><!-- wp:group {"className":"ta-footer__grid ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__grid ta-wrap"><!-- wp:group {"className":"ta-footer__brand","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__brand"><!-- wp:top/brand {"variant":"footer"} /-->

<!-- wp:paragraph {"className":"ta-footer__about"} -->
<p class="ta-footer__about">Same-day appliance repair for Houston homes, every major brand.</p>
<!-- /wp:paragraph -->

<!-- wp:top/business-info {"variant":"hours-line"} /-->

<!-- wp:top/social-links /--></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"nav","className":"ta-footer__col","layout":{"type":"default"},"ariaLabel":"Our services"} -->
<nav class="wp-block-group ta-footer__col" aria-label="Our services"><!-- wp:top/post-links {"source":"service","collapsible":true,"title":"Our Services","allLabel":"All Services →"} /--></nav>
<!-- /wp:group -->

<!-- wp:group {"tagName":"nav","className":"ta-footer__col","layout":{"type":"default"},"ariaLabel":"Service areas"} -->
<nav class="wp-block-group ta-footer__col" aria-label="Service areas"><!-- wp:top/post-links {"source":"service_area","collapsible":true,"title":"Service Areas","allLabel":"All Service Areas →"} /--></nav>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-footer__col ta-footer__contact","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__col ta-footer__contact"><!-- wp:heading {"className":"ta-footer__title"} -->
<h2 class="wp-block-heading ta-footer__title">Contact Us</h2>
<!-- /wp:heading -->

<!-- wp:top/business-info {"variant":"contact"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-footer__bottom ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-footer__bottom ta-wrap"><!-- wp:top/copyright /-->

<!-- wp:paragraph {"className":"ta-footer__legal"} -->
<p class="ta-footer__legal"><a href="<?php echo esc_url( $privacy ); ?>">Privacy Policy</a> <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a> <a href="<?php echo esc_url( home_url( '/sitemap_index.xml' ) ); ?>">Sitemap</a> <a class="ta-totop" href="#top">Back to top ↑</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
