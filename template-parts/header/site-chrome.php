<?php
/**
 * Site header chrome — driven by theme options + WP menus.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header  = altijara_get_option( 'header', array() );
$urls    = altijara_theme_urls();
$logo    = ! empty( $header['logo_url'] ) ? $header['logo_url'] : '';
$logo_h  = ! empty( $header['logo_hover_url'] ) ? $header['logo_hover_url'] : $logo;
$ad_sc   = ! empty( $header['ad_shortcode'] ) ? trim( (string) $header['ad_shortcode'] ) : '';
$ad_img  = ! empty( $header['ad_image'] ) ? $header['ad_image'] : '';
$ad_url  = ! empty( $header['ad_url'] ) ? $header['ad_url'] : ( $urls['advertise'] ?? home_url( '/advertise/' ) );
$theme_login = $urls['login'] ?? home_url( '/login/' );
$theme_sub   = $urls['subscribe'] ?? home_url( '/subscribe/' );
// Never send front CTA to wp-login.php — always use theme login page.
$login_u = $theme_login;
if ( ! empty( $header['login_url'] ) ) {
	$custom_login = (string) $header['login_url'];
	if ( $custom_login && false === stripos( $custom_login, 'wp-login.php' ) ) {
		$login_u = $custom_login;
	}
}
$sub_u = ! empty( $header['subscribe_url'] ) ? $header['subscribe_url'] : $theme_sub;
$login_t = ! empty( $header['login_text'] ) ? $header['login_text'] : __( 'تسجيل الدخول', 'altijara' );
$sub_t   = ! empty( $header['subscribe_text'] ) ? $header['subscribe_text'] : __( 'اشترك', 'altijara' );
$bg      = ! empty( $header['background'] ) ? $header['background'] : '';
$sticky  = ! empty( $header['sticky'] );
$lw      = ! empty( $header['logo_width'] ) ? (int) $header['logo_width'] : 0;
$lh      = ! empty( $header['logo_height'] ) ? (int) $header['logo_height'] : 0;
$logo_style = '';
if ( $lw || $lh ) {
	$logo_style = ' style="' . ( $lw ? 'width:' . $lw . 'px;' : '' ) . ( $lh ? 'height:' . $lh . 'px;' : '' ) . '"';
}
$header_style = $bg ? ' style="background:' . esc_attr( $bg ) . '"' : '';
$header_class = 'main-header' . ( $sticky ? ' is-sticky-header' : '' );
?>
<?php if ( ! empty( $header['show_top_bar'] ) ) : ?>
<div class="top-utility-bar">
  <div class="container top-utility-inner">
	<?php altijara_render_top_bar_nav(); ?>
	<div class="top-left-actions">
	  <?php if ( ! empty( $header['top_bar_text'] ) ) : ?>
		<span class="top-bar-extra-text"><?php echo esc_html( $header['top_bar_text'] ); ?></span>
	  <?php endif; ?>
	  <?php if ( ! empty( $header['show_date'] ) ) : ?>
	  <div class="top-date-display">
		<svg class="top-date-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
		<span id="topDateText"><?php echo esc_html( altijara_format_date_ar() ); ?></span>
	  </div>
	  <span class="top-tools-divider"></span>
	  <?php endif; ?>
	  <?php if ( ! empty( $header['show_social'] ) ) : ?>
	  <div class="top-social-links">
		<?php altijara_render_social_icons( 'header', 12 ); ?>
	  </div>
	  <?php endif; ?>
	</div>
  </div>
</div>
<?php endif; ?>

<header class="<?php echo esc_attr( $header_class ); ?>"<?php echo $header_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
  <div class="container header-grid">
	<div class="header-brand">
	  <button class="menu-toggle-btn" id="sectionsMenuBtn" aria-label="<?php esc_attr_e( 'أقسام المجلة', 'altijara' ); ?>" aria-expanded="false" aria-controls="sectionsDrawer" type="button">
		<span></span><span></span><span></span>
	  </button>
	  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand-logo-link" aria-label="<?php bloginfo( 'name' ); ?>">
		<?php if ( $logo ) : ?>
		  <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="brand-logo-img brand-logo-default"<?php echo $logo_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		  <?php if ( $logo_h && $logo_h !== $logo ) : ?>
			<img src="<?php echo esc_url( $logo_h ); ?>" alt="" class="brand-logo-img brand-logo-hover"<?php echo $logo_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		  <?php endif; ?>
		<?php else : ?>
		  <span class="brand-logo-text"><?php bloginfo( 'name' ); ?></span>
		<?php endif; ?>
	  </a>
	</div>
	<div class="header-sponsor-banner" data-ad-slot="header-billboard">
	  <?php if ( $ad_sc ) : ?>
		<div class="header-ad-shortcode">
		  <?php echo do_shortcode( $ad_sc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ad plugin shortcode HTML ?>
		</div>
	  <?php elseif ( $ad_img ) : ?>
		<a href="<?php echo esc_url( $ad_url ); ?>" class="sponsor-banner-img-link">
		  <img src="<?php echo esc_url( $ad_img ); ?>" alt="<?php esc_attr_e( 'إعلان مجلة التجارة', 'altijara' ); ?>" class="header-ad-img">
		</a>
	  <?php else : ?>
		<div class="header-ad-placeholder" aria-label="<?php esc_attr_e( 'Advertisement', 'altijara' ); ?>">
		  <span class="header-ad-placeholder__label"><?php esc_html_e( 'Advertisement', 'altijara' ); ?></span>
		  <span class="header-ad-placeholder__size">728 × 90</span>
		</div>
	  <?php endif; ?>
	</div>
	<div class="header-actions o-header__top-column o-header__top-column--right">
	  <?php if ( ! empty( $header['show_search'] ) ) : ?>
	  <button class="header-search-btn o-header__top-icon-link--search<?php echo empty( $header['mobile_show_search'] ) ? ' hide-on-mobile' : ''; ?>" id="headerSearchBtn" aria-label="<?php esc_attr_e( 'بحث في الموقع', 'altijara' ); ?>" title="<?php esc_attr_e( 'بحث', 'altijara' ); ?>" type="button">
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
		  <circle cx="11" cy="11" r="8"></circle>
		  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
		</svg>
	  </button>
	  <?php endif; ?>
	  <?php if ( ! empty( $header['show_login'] ) ) : ?>
	  <a class="o-header__top-myaccount login-link" id="o-header-top-link-signin" href="<?php echo esc_url( $login_u ); ?>">
		<svg class="o-header__myaccount-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
		  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
		  <circle cx="12" cy="7" r="4"></circle>
		</svg>
		<span><?php echo esc_html( $login_t ); ?></span>
	  </a>
	  <?php endif; ?>
	  <?php if ( ! empty( $header['show_subscribe'] ) ) : ?>
	  <a class="o-header__top-button o-header__top-button--hide-m btn-subscribe<?php echo empty( $header['mobile_show_subscribe'] ) ? '' : ''; ?>" href="<?php echo esc_url( $sub_u ); ?>"><?php echo esc_html( $sub_t ); ?></a>
	  <?php endif; ?>
	</div>
  </div>
</header>

<?php if ( ! empty( $header['show_search'] ) ) : ?>
<div class="header-search-overlay" id="headerSearchOverlay" hidden></div>
<div class="header-search-bar" id="headerSearchBar" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'بحث في مجلة التجارة', 'altijara' ); ?>" hidden>
  <div class="container header-search-inner">
	<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="header-search-form" role="search">
	  <svg class="header-search-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<circle cx="11" cy="11" r="8"></circle>
		<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
	  </svg>
	  <input type="search" name="s" class="header-search-input" id="headerSearchInput" placeholder="<?php esc_attr_e( 'ابحث في مجلة التجارة (الأخبار، الشركات، التقارير، أعداد المجلة...)', 'altijara' ); ?>" autocomplete="off" enterkeyhint="search">
	  <button type="submit" class="header-search-submit"><?php esc_html_e( 'بحث', 'altijara' ); ?></button>
	  <button type="button" class="header-search-close" id="headerSearchClose" aria-label="<?php esc_attr_e( 'إغلاق البحث', 'altijara' ); ?>">
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	  </button>
	</form>
	<div class="header-search-results" id="headerSearchResults"></div>
	<p class="header-search-hint"><?php esc_html_e( 'اضغط Esc للإغلاق · اكتب حرفين على الأقل للنتائج', 'altijara' ); ?></p>
  </div>
</div>
<?php endif; ?>

<nav class="primary-nav-bar" aria-label="<?php esc_attr_e( 'الأقسام الرئيسية', 'altijara' ); ?>">
  <div class="container">
	<?php altijara_render_primary_nav(); ?>
  </div>
</nav>

<?php altijara_render_mobile_drawer_markup(); ?>
