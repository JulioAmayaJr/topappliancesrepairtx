<?php
/**
 * Title: Hero
 * Slug: top-appliance/hero
 * Categories: top-sections
 * Description: H1, subtitle, call and book buttons, warranty seal, Google reviews badge and the LCP image. Edit the H1 per page.
 * Keywords: hero, header, call, book, warranty
 * Viewport Width: 1440
 */
?>
<!-- wp:group {"tagName":"section","className":"ta-hero","layout":{"type":"default"}} -->
<section class="wp-block-group ta-hero"><!-- wp:group {"className":"ta-hero__inner ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-hero__inner ta-wrap"><!-- wp:group {"className":"ta-hero__copy","layout":{"type":"default"}} -->
<div class="wp-block-group ta-hero__copy"><!-- wp:heading {"level":1,"className":"ta-hero__title"} -->
<h1 class="wp-block-heading ta-hero__title">Same-Day Appliance Repair in Houston, TX</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ta-hero__lede"} -->
<p class="ta-hero__lede">Fridge not cooling? Washer won’t drain? We send a technician the same day, with a free estimate before any work starts and a warranty on parts and labor.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"ta-hero__ctas"} -->
<div class="wp-block-buttons ta-hero__ctas"><!-- wp:button {"className":"ta-btn--call ta-btn--lg","metadata":{"bindings":{"text":{"source":"top/business","args":{"key":"call_label"}},"url":{"source":"top/business","args":{"key":"phone_href"}}}}} -->
<div class="wp-block-button ta-btn--call ta-btn--lg"><a class="wp-block-button__link wp-element-button">Call us</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"ta-btn--book ta-btn--lg","metadata":{"bindings":{"url":{"source":"top/business","args":{"key":"booking_url"}}}}} -->
<div class="wp-block-button ta-btn--book ta-btn--lg"><a class="wp-block-button__link wp-element-button">Book Service</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"className":"ta-hero__proof","layout":{"type":"default"}} -->
<div class="wp-block-group ta-hero__proof"><!-- wp:top/seal /-->

<!-- wp:top/google-reviews /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ta-hero__media","layout":{"type":"default"}} -->
<div class="wp-block-group ta-hero__media"><?php echo top_image_block( 'hero', 'ta-hero__img ta-lcp', '(min-width: 1304px) 470px, (min-width: 900px) calc((100vw - 8rem) * 0.4), calc(100vw - 2rem)' ); // phpcs:ignore ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
