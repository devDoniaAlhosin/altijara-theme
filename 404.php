<?php
/**
 * 404 template
 *
 * @package Altijara
 */

get_header();
?>
<main class="container static-shell">
	<section class="static-page">
		<header class="static-page-header">
			<h1 class="static-title"><?php esc_html_e( 'الصفحة غير موجودة', 'altijara' ); ?></h1>
			<p class="static-lead"><?php esc_html_e( 'تعذّر العثور على الصفحة المطلوبة.', 'altijara' ); ?></p>
			<p><a class="static-btn static-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'العودة للرئيسية', 'altijara' ); ?></a></p>
		</header>
	</section>
</main>
<?php
get_footer();
