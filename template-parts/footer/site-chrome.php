<?php
/**
 * Site footer chrome — theme options + WP menus + shared social.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer = altijara_get_option( 'footer', array() );
$style  = '';
if ( ! empty( $footer['background'] ) || ! empty( $footer['text_color'] ) ) {
	$style = ' style="';
	if ( ! empty( $footer['background'] ) ) {
		$style .= 'background:' . esc_attr( $footer['background'] ) . ';';
	}
	if ( ! empty( $footer['text_color'] ) ) {
		$style .= 'color:' . esc_attr( $footer['text_color'] ) . ';';
	}
	$style .= '"';
}
$copyright = ! empty( $footer['copyright'] ) ? $footer['copyright'] : '{year}@ التجارة جميع الحقوق محفوظة';
$copyright = str_replace( '{year}', (string) wp_date( 'Y' ), $copyright );
$logo      = ! empty( $footer['logo_url'] ) ? $footer['logo_url'] : '';
$desc      = ! empty( $footer['description'] ) ? $footer['description'] : '';
?>
<footer class="editorial-dark-footer"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
  <div class="container">
	<div class="footer-grid">
	  <?php if ( ! empty( $footer['show_brand'] ) ) : ?>
	  <div class="footer-col-brand">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo-link">
		  <?php if ( $logo ) : ?>
			<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="footer-logo-img">
		  <?php else : ?>
			<span><?php bloginfo( 'name' ); ?></span>
		  <?php endif; ?>
		</a>
		<?php if ( $desc ) : ?>
		  <p class="footer-brand-desc"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $footer['contact_address'] ) || ! empty( $footer['contact_email'] ) || ! empty( $footer['contact_phone'] ) ) : ?>
		  <div class="footer-contact-block">
			<?php if ( ! empty( $footer['contact_address'] ) ) : ?>
			  <p class="footer-contact-line"><?php echo esc_html( $footer['contact_address'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $footer['contact_email'] ) ) : ?>
			  <p class="footer-contact-line"><a href="mailto:<?php echo esc_attr( $footer['contact_email'] ); ?>"><?php echo esc_html( $footer['contact_email'] ); ?></a></p>
			<?php endif; ?>
			<?php if ( ! empty( $footer['contact_phone'] ) ) : ?>
			  <p class="footer-contact-line"><?php echo esc_html( $footer['contact_phone'] ); ?></p>
			<?php endif; ?>
		  </div>
		<?php endif; ?>
	  </div>
	  <?php endif; ?>

	  <?php if ( ! empty( $footer['show_sections'] ) ) : ?>
	  <div class="footer-col-sections">
		<?php altijara_render_footer_sections_menu(); ?>
	  </div>
	  <?php endif; ?>

	  <?php if ( ! empty( $footer['show_quick'] ) ) : ?>
	  <div class="footer-col-about">
		<h4 class="footer-heading"><?php echo esc_html( ! empty( $footer['quick_heading'] ) ? $footer['quick_heading'] : __( 'روابط تهمك', 'altijara' ) ); ?></h4>
		<?php
		if ( ! altijara_render_footer_quick_menu() ) {
			// Fallback only when no WP menu assigned — keep structure, empty list.
			echo '<ul class="footer-links-list" data-wp-menu="0"></ul>';
		}
		?>
	  </div>
	  <?php endif; ?>

	  <?php if ( ! empty( $footer['show_social'] ) ) : ?>
	  <div class="footer-col-social">
		<h4 class="footer-heading"><?php echo esc_html( ! empty( $footer['social_heading'] ) ? $footer['social_heading'] : __( 'تابعونا على', 'altijara' ) ); ?></h4>
		<div class="footer-social-icons">
		  <?php altijara_render_social_icons( 'footer', 19 ); ?>
		</div>
	  </div>
	  <?php endif; ?>
	</div>

	<div class="footer-bottom-row">
	  <p class="footer-copyright-text"><?php echo esc_html( $copyright ); ?></p>
	</div>
  </div>
</footer>
