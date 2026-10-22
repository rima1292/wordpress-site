<?php
/**
 * Page d'archive des événements : /evenements/
 *
 * @package Monstroid2
 */

get_header(); ?>

<div class="m2-events-page container">
	<header class="m2-events-page__header">
		<h1 class="m2-events-page__title"><?php
			if ( is_tax() ) {
				single_term_title();
			} else {
				esc_html_e( 'Événements', 'monstroid2' );
			}
		?></h1>
		<?php the_archive_description( '<div class="m2-events-page__intro">', '</div>' ); ?>
	</header>

	<?php get_template_part( 'template-parts/events/listing' ); ?>
</div>

<?php
get_footer();
