<?php
/**
 * Search results
 *
 * @package Altijara
 */

get_header();
?>
<main class="container static-shell">
	<section class="static-page">
		<header class="static-page-header">
			<h1 class="static-title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'نتائج البحث عن: %s', 'altijara' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		</header>
		<div class="rec-items-list">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/post', 'card', array(
						'post'    => get_post(),
						'variant' => 'rec_item',
					) );
				endwhile;
				the_posts_pagination();
			else :
				echo '<p>' . esc_html__( 'لا توجد نتائج مطابقة.', 'altijara' ) . '</p>';
			endif;
			?>
		</div>
	</section>
</main>
<?php
get_footer();
