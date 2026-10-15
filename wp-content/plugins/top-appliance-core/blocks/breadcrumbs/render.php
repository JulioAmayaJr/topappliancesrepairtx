<?php
/** Visible breadcrumbs; same trail as the BreadcrumbList JSON-LD (top_breadcrumb_trail()). */
defined( 'ABSPATH' ) || exit;
$trail = top_breadcrumb_trail();
if ( count( $trail ) < 2 ) {
	return;
}
$last = count( $trail ) - 1;
?>
<nav <?php echo top_block_wrapper( 'ta-crumbs' ); ?> aria-label="Breadcrumb">
	<ol>
		<?php foreach ( $trail as $i => $c ) : ?>
			<li>
				<?php if ( $i === $last ) : ?>
					<span aria-current="page"><?php echo esc_html( $c[0] ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( $c[1] ); ?>"><?php echo esc_html( $c[0] ); ?></a><?php echo top_icon( 'chevron-right', 'ta-crumbs__sep', 14 ); ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
