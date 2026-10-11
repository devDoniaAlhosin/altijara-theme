<?php
/**
 * Shared «المزيد» archive link (prototype classes preserved).
 *
 * @package Altijara
 * @var string $args['url']
 * @var string $args['label'] Optional link text.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$url   = isset( $args['url'] ) ? $args['url'] : home_url( '/' );
$label = isset( $args['label'] ) ? $args['label'] : __( 'المزيد', 'altijara' );
?>
<a href="<?php echo esc_url( $url ); ?>" class="section-more-nav"><?php echo esc_html( $label ); ?><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg></a>
