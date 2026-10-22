<?php
/**
 * Template Name: Page Événements
 *
 * Utilisez ce modèle sur n'importe quelle page : le contenu de la page
 * (intro) s'affiche au-dessus de la liste des événements.
 *
 * @package Monstroid2
 */

get_header(); ?>

<div class="m2-events-page container">
	<?php while ( have_posts() ) : the_post(); ?>
		<header class="m2-events-page__header">
			<h1 class="m2-events-page__title"><?php the_title(); ?></h1>
			<?php if ( get_the_content() ) : ?>
				<div class="m2-events-page__intro"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>
	<?php endwhile; ?>

	<?php get_template_part( 'template-parts/events/listing' ); ?>
</div>

<?php
get_footer();
