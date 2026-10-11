<?php
/**
 * Single magazine issue
 *
 * @package Altijara
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main class="container static-shell">
		<section class="static-page static-page--wide">
			<header class="static-page-header">
				<h1 class="static-title"><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" style="max-width:320px;height:auto;display:block;margin:0 auto 24px;">
			<?php endif; ?>
			<div class="article-body-content">
				<?php the_content(); ?>
			</div>
			<p><a class="static-btn static-btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'issue' ) ); ?>"><?php esc_html_e( 'كل الأعداد', 'altijara' ); ?></a></p>
		</section>
	</main>
	<?php
endwhile;

get_footer();
