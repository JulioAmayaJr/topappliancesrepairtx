<?php
/**
 * Title: Header
 * Slug: top-appliance/header
 * Categories: top-chrome
 * Description: Sticky header: logo, mega menu, Call Now and Book Service. On mobile: logo, call button and menu.
 * Viewport Width: 1440
 */
?>
<!-- wp:group {"className":"ta-header","layout":{"type":"default"}} -->
<div class="wp-block-group ta-header"><!-- wp:group {"className":"ta-header__inner ta-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group ta-header__inner ta-wrap"><!-- wp:top/brand /-->

<!-- wp:top/mega-menu /-->

<!-- wp:buttons {"className":"ta-header__ctas"} -->
<div class="wp-block-buttons ta-header__ctas"><!-- wp:button {"className":"ta-btn--call","metadata":{"bindings":{"url":{"source":"top/business","args":{"key":"phone_href"}}}}} -->
<div class="wp-block-button ta-btn--call"><a class="wp-block-button__link wp-element-button">Call Now</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"ta-btn--book","metadata":{"bindings":{"url":{"source":"top/business","args":{"key":"booking_url"}}}}} -->
<div class="wp-block-button ta-btn--book"><a class="wp-block-button__link wp-element-button">Book Service</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
