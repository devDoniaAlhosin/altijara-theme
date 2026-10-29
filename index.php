<?php
/**
 * Main fallback template
 *
 * @package Altijara
 */

get_header();
?>
<main class="container static-shell">
	<section class="static-page">
		<?php if ( have_posts() ) : ?>
			<header class="static-page-header">
				<h1 class="static-title"><?php echo esc_html( get_the_archive_title() ?: get_bloginfo( 'name' ) ); ?></h1>
			</header>
			<div class="rec-items-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/post', 'card', array(
						'post'    => get_post(),
						'variant' => 'rec_item',
					) );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<header class="static-page-header">
				<h1 class="static-title"><?php esc_html_e( 'لا توجد نتائج', 'altijara' ); ?></h1>
			</header>
		<?php endif; ?>
	</section>
</main>
<?php
get_footer();
