<?php
/**
 * Reusable article card (preserves prototype classes)
 *
 * @package Altijara
 * @var WP_Post $args['post']
 * @var string  $args['kicker']
 * @var string  $args['variant'] chamber_item|rec_item|bottom_card
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$post = isset( $args['post'] ) ? get_post( $args['post'] ) : null;
if ( ! $post ) {
	return;
}
$variant = isset( $args['variant'] ) ? $args['variant'] : 'rec_item';
$kicker  = isset( $args['kicker'] ) ? $args['kicker'] : ( function_exists( 'altijara_get_listing_category_name' ) ? altijara_get_listing_category_name( $post->ID ) : altijara_get_primary_category_name( $post->ID ) );
$url     = get_permalink( $post );
$title   = get_the_title( $post );
$date    = altijara_format_date_ar( $post->ID );

if ( 'chamber_item' === $variant ) :
	?>
<li class="chamber-opinion-item">
	<a href="<?php echo esc_url( $url ); ?>" class="chamber-item-thumb">
		<?php
		altijara_the_thumb_img(
			$post->ID,
			'medium',
			array(
				'alt'      => $title,
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '72px',
			)
		);
		?>
		<?php
		if ( function_exists( 'altijara_render_video_play_badge' ) ) {
			altijara_render_video_play_badge( $post->ID );
		}
		?>
	</a>
	<div class="chamber-item-body">
		<?php if ( $kicker ) : ?>
			<span class="chamber-item-kicker"><?php echo esc_html( $kicker ); ?></span>
		<?php endif; ?>
		<h4 class="chamber-item-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h4>
		<span class="chamber-item-time"><?php echo esc_html( $date ); ?></span>
	</div>
</li>
	<?php
elseif ( 'rec_item' === $variant ) :
	?>
<div class="rec-item-card">
	<div class="rec-item-body">
		<?php if ( $kicker ) : ?>
			<span class="rec-kicker"><?php echo esc_html( $kicker ); ?></span>
		<?php endif; ?>
		<a href="<?php echo esc_url( $url ); ?>" class="rec-title"><?php echo esc_html( $title ); ?></a>
		<span class="rec-time"><?php echo esc_html( $date ); ?></span>
	</div>
	<?php
	altijara_the_thumb_img(
		$post->ID,
		'medium',
		array(
			'alt'      => '',
			'class'    => 'rec-item-thumb',
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => '120px',
		)
	);
	?>
</div>
	<?php
endif;
