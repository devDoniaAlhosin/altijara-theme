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
$label  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, 'الأكثر قراءة في قطاع الأعمال' ) : ( isset( $config['label'] ) ? (string) $config['label'] : 'الأكثر قراءة في قطاع الأعمال' );
?>
<div class="most-read-col" data-section="<?php echo esc_attr( $key ); ?>">
	<?php if ( '' !== $label ) : ?>
	<div class="most-read-heading"><?php echo esc_html( $label ); ?></div>
	<?php endif; ?>
	<?php if ( empty( $posts ) ) : ?>
		<?php
		if ( function_exists( 'altijara_render_section_empty' ) ) {
			altijara_render_section_empty( 'altijara-section-empty--body', $key );
		} else {
			echo '<div class="altijara-section-empty altijara-section-empty--body" role="status"><p>' . esc_html__( 'لا مقالات في هذا القسم بعد', 'altijara' ) . '</p></div>';
		}
		?>
	<?php else : ?>
		<ol class="most-read-list" data-wp-cat="most-read">
			<?php foreach ( $posts as $i => $item ) : ?>
				<li class="most-read-item">
					<span class="most-read-num"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="most-read-text" data-full-title="<?php echo esc_attr( get_the_title( $item ) ); ?>">
						<?php echo esc_html( get_the_title( $item ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>
