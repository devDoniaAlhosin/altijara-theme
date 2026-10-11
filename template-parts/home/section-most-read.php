<?php
/**
 * Most Read ranked list — prototype classes (cars-section companion).
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'most_read';
$label  = ! empty( $config['label'] ) ? $config['label'] : 'الأكثر قراءة في قطاع الأعمال';
?>
<div class="most-read-col" data-section="<?php echo esc_attr( $key ); ?>">
	<div class="most-read-heading"><?php echo esc_html( $label ); ?></div>
	<ol class="most-read-list" data-wp-cat="most-read">
		<?php if ( empty( $posts ) ) : ?>
			<li class="most-read-item">
				<span class="most-read-num">—</span>
				<span class="most-read-text"><?php esc_html_e( 'لا مقالات حالياً', 'altijara' ); ?></span>
			</li>
		<?php else : ?>
			<?php foreach ( $posts as $i => $item ) : ?>
				<li class="most-read-item">
					<span class="most-read-num"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="most-read-text" data-full-title="<?php echo esc_attr( get_the_title( $item ) ); ?>">
						<?php echo esc_html( get_the_title( $item ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ol>
</div>
