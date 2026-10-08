<?php
defined( 'ABSPATH' ) || exit;
printf( '<p %s>&copy; %s %s. All rights reserved.</p>', top_block_wrapper( 'ta-copyright' ), esc_html( wp_date( 'Y' ) ), esc_html( top_biz( 'name' ) ) );
