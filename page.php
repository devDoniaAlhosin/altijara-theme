<?php
/**
 * Default page template
 *
 * @package Altijara
 */

get_header();
?>
<main class="container static-shell">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<section class="static-page">
			<header class="static-page-header">
				<h1 class="static-title"><?php the_title(); ?></h1>
			</header>
			<div class="static-page-body article-body-content">
				<?php the_content(); ?>
			</div>
		</section>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
