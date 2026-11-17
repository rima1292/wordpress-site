<?php
/**
 * Détail d'un événement (image OU vidéo en tête).
 *
 * @package Monstroid2
 */

get_header(); ?>

<div class="m2-events-page m2-event-single container">
	<?php while ( have_posts() ) : the_post();
		$types      = get_the_terms( get_the_ID(), 'event_type' );
		$organizers = get_the_terms( get_the_ID(), 'event_organizer' );
		$date       = monstroid2_event_formatted_date();
		$time       = monstroid2_event_formatted_time();
		$location   = get_post_meta( get_the_ID(), '_event_location', true );
		$link       = get_post_meta( get_the_ID(), '_event_link', true );
		$link_label = get_post_meta( get_the_ID(), '_event_link_label', true );
		$archive    = get_post_type_archive_link( 'event' );
		?>
		<article id="event-<?php the_ID(); ?>" <?php post_class(); ?>>
			<a class="m2-event-single__back" href="<?php echo esc_url( $archive ); ?>">&larr; <?php esc_html_e( 'Tous les événements', 'monstroid2' ); ?></a>

			<header class="m2-event-single__header">
				<?php if ( $types && ! is_wp_error( $types ) ) : ?>
					<div class="m2-event-card__tags">
						<?php foreach ( $types as $term ) : ?>
							<a class="m2-event-tag" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<h1 class="m2-events-page__title"><?php the_title(); ?></h1>
				<?php if ( monstroid2_event_is_past() ) : ?>
					<p class="m2-event-single__past"><?php esc_html_e( 'Cet événement est terminé.', 'monstroid2' ); ?></p>
				<?php endif; ?>
			</header>

			<?php monstroid2_event_media( get_the_ID(), 'full' ); ?>

			<div class="m2-event-single__layout">
				<div class="m2-event-single__content"><?php the_content(); ?></div>

				<aside class="m2-event-single__aside">
					<ul class="m2-event-card__meta">
						<?php if ( $date ) : ?><li><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><?php echo esc_html( $date ); ?></li><?php endif; ?>
						<?php if ( $time ) : ?><li><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><?php echo esc_html( $time ); ?></li><?php endif; ?>
						<?php if ( $location ) : ?><li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><?php echo esc_html( $location ); ?></li><?php endif; ?>
						<?php if ( $organizers && ! is_wp_error( $organizers ) ) : ?><li><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg><?php echo esc_html( implode( ', ', wp_list_pluck( $organizers, 'name' ) ) ); ?></li><?php endif; ?>
					</ul>
					<?php if ( $link && ! monstroid2_event_is_past() ) : ?>
						<a class="m2-event-btn" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( $link_label ? $link_label : __( 'Je m\'inscris', 'monstroid2' ) ); ?>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
						</a>
					<?php endif; ?>
				</aside>
			</div>
		</article>
	<?php endwhile; ?>
</div>

<?php
get_footer();
