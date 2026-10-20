<?php
/**
 * Issue CPT archive — SSR shared issues layout.
 *
 * @package Altijara
 */
get_header();
?>
<main class="container issues-main-wrapper">
	<?php
	if ( function_exists( 'altijara_render_issues_archive' ) ) {
		altijara_render_issues_archive();
	}
	?>
</main>
<?php
get_footer();
