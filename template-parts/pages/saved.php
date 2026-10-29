<?php
/**
 * saved page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$urls  = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$login = $urls['login'] ?? home_url( '/login/' );
?>
<main class="container static-shell">
	<section class="static-page">
		<header class="static-page-header">
			<h1 class="static-title"><?php the_title(); ?></h1>
			<p class="static-lead"><?php esc_html_e( 'المقالات التي حفظتها لقراءتها لاحقاً.', 'altijara' ); ?></p>
		</header>

		<?php if ( ! is_user_logged_in() ) : ?>
			<div class="static-panel">
				<p><?php esc_html_e( 'سجّل الدخول لعرض المقالات المحفوظة في حسابك.', 'altijara' ); ?></p>
				<p>
					<a class="static-btn static-btn-primary" href="<?php echo esc_url( function_exists( 'altijara_bookmarks_login_url' ) ? altijara_bookmarks_login_url( get_permalink() ) : $login ); ?>">
						<?php esc_html_e( 'تسجيل الدخول', 'altijara' ); ?>
					</a>
				</p>
			</div>
		<?php else : ?>
			<div class="altijara-saved-page">
				<?php
				if ( function_exists( 'altijara_render_saved_articles_list' ) ) {
					altijara_render_saved_articles_list();
				}
				?>
			</div>
		<?php endif; ?>
	</section>
</main>
<?php

