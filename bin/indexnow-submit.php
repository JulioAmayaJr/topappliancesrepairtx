<?php
/**
 * Submit every indexable URL to IndexNow (Bing, Yandex, Seznam, Naver).
 * Run with: bin/wp eval-file /project/bin/indexnow-submit.php
 *
 * The URL list is the public one: the home page, the system pages, all published
 * posts and all published entries of the custom post types, always on the public
 * domain. Paginated and noindex URLs are deliberately excluded.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}
if ( ! class_exists( '\RankMath\Instant_Indexing\Api' ) ) {
	WP_CLI::error( 'Rank Math Instant Indexing module is not active.' );
}

$urls = array( top_public_base() . '/' );
foreach ( array( 'service', 'brand', 'service_area' ) as $pt ) {
	$archive = get_post_type_archive_link( $pt );
	if ( $archive ) {
		$urls[] = top_public_url( $archive );
	}
}
foreach ( get_posts(
	array(
		'post_type'      => array( 'post', 'page', 'service', 'brand', 'service_area', 'error_code' ),
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'fields'         => 'ids',
	)
) as $id ) {
	if ( 'noindex' === get_post_meta( $id, 'rank_math_robots', true ) ) {
		continue;
	}
	$urls[] = top_public_url( (string) get_permalink( $id ) );
}
$urls = array_values( array_unique( array_filter( $urls ) ) );

$api = \RankMath\Instant_Indexing\Api::get();
$ok  = $api->submit( $urls, true );
WP_CLI::log( sprintf( 'Submitted %d URLs to IndexNow: %s', count( $urls ), $ok ? 'accepted' : 'failed' ) );
foreach ( array_slice( $urls, 0, 5 ) as $u ) {
	WP_CLI::log( '  ' . $u );
}
WP_CLI::log( '  ...' );
