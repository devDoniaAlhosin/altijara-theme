<?php
/**
 * Template Name: تسجيل الدخول
 * Login page — layout fixed; copy from page meta.
 *
 * @package Altijara
 */
get_header();

$pid   = get_the_ID();
$m     = altijara_get_page_meta( $pid );
$title = get_the_title();
$urls  = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$sub   = $urls['subscribe'] ?? home_url( '/subscribe/' );
$oauth_err = isset( $_GET['oauth_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<main class="container static-shell">
	<section class="static-page static-page--narrow">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! empty( $m['lead'] ) ) : ?>
				<p class="static-lead"><?php echo esc_html( $m['lead'] ); ?></p>
			<?php endif; ?>
		</header>

		<div class="static-panel static-auth-panel">
			<?php if ( $oauth_err ) : ?>
				<div class="static-success is-visible" role="alert" style="background:rgba(185,28,28,.08);border-color:rgba(185,28,28,.35);color:#7f1d1d;">
					<?php esc_html_e( 'تعذر إكمال تسجيل الدخول الاجتماعي. تحقق من إعدادات Google/Facebook في خيارات القالب.', 'altijara' ); ?>
				</div>
			<?php endif; ?>

			<form class="static-form" id="loginForm" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
				<?php
				if ( function_exists( 'altijara_render_social_login_buttons' ) ) {
					altijara_render_social_login_buttons( 'login' );
				}
				?>
				<div class="static-field">
					<label for="lEmail"><?php esc_html_e( 'البريد الإلكتروني أو اسم المستخدم', 'altijara' ); ?></label>
					<input id="lEmail" name="log" type="text" required autocomplete="username" placeholder="name@example.com">
				</div>
				<div class="static-field">
					<label for="lPassword"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?></label>
					<input id="lPassword" name="pwd" type="password" required autocomplete="current-password" placeholder="••••••••">
				</div>

				<div class="auth-row">
					<label class="static-check">
						<input type="checkbox" name="rememberme" value="forever" checked>
						<span><?php esc_html_e( 'تذكرني', 'altijara' ); ?></span>
					</label>
					<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>" class="static-text-link"><?php echo esc_html( $m['forgot_label'] ?? __( 'نسيت كلمة المرور؟', 'altijara' ) ); ?></a>
				</div>

				<input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url( '/' ) ); ?>">

				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary auth-submit" name="wp-submit"><?php esc_html_e( 'دخول', 'altijara' ); ?></button>
				</div>

				<p class="static-form-note"><?php echo esc_html( $m['subscribe_note'] ?? '' ); ?> <a href="<?php echo esc_url( $sub ); ?>"><?php echo esc_html( $m['subscribe_link_label'] ?? __( 'اشترك', 'altijara' ) ); ?></a></p>
			</form>
		</div>
	</section>
</main>
<?php
get_footer();
