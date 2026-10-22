<?php
/**
 * Carte d'un événement (image OU vidéo à gauche, infos à droite).
 *
 * @package Monstroid2
 */

$event_id   = get_the_ID();
$types      = get_the_terms( $event_id, 'event_type' );
$organizers = get_the_terms( $event_id, 'event_organizer' );
$type_slugs = $types && ! is_wp_error( $types ) ? wp_list_pluck( $types, 'slug' ) : array();
$org_slugs  = $organizers && ! is_wp_error( $organizers ) ? wp_list_pluck( $organizers, 'slug' ) : array();
$date       = monstroid2_event_formatted_date( $event_id );
$time       = monstroid2_event_formatted_time( $event_id );
$location   = get_post_meta( $event_id, '_event_location', true );
$link       = get_post_meta( $event_id, '_event_link', true );
$link_label = get_post_meta( $event_id, '_event_link_label', true );
$is_past    = monstroid2_event_is_past( $event_id );
?>
<article id="event-<?php the_ID(); ?>" <?php post_class( 'm2-event-card' ); ?>
	data-types="<?php echo esc_attr( implode( ' ', $type_slugs ) ); ?>"
	data-organizers="<?php echo esc_attr( implode( ' ', $org_slugs ) ); ?>">

	<?php monstroid2_event_media( $event_id, 'large' ); ?>

	<div class="m2-event-card__body">
		<?php if ( $type_slugs ) : ?>
			<div class="m2-event-card__tags">
				<?php foreach ( $types as $term ) : ?>
					<span class="m2-event-tag"><?php echo esc_html( $term->name ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h3 class="m2-event-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

		<?php if ( has_excerpt() || get_the_content() ) : ?>
			<div class="m2-event-card__excerpt"><?php the_excerpt(); ?></div>
		<?php endif; ?>

		<ul class="m2-event-card__meta">
			<?php if ( $date ) : ?>
				<li><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><?php echo esc_html( $date ); ?></li>
			<?php endif; ?>
			<?php if ( $time ) : ?>
				<li><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><?php echo esc_html( $time ); ?></li>
			<?php endif; ?>
			<?php if ( $location ) : ?>
				<li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><?php echo esc_html( $location ); ?></li>
			<?php endif; ?>
		</ul>

		<?php if ( $link && ! $is_past ) : ?>
			<a class="m2-event-btn" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
				<?php echo esc_html( $link_label ? $link_label : __( 'Je m\'inscris', 'monstroid2' ) ); ?>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		<?php else : ?>
			<a class="m2-event-btn m2-event-btn--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( 'En savoir plus', 'monstroid2' ); ?>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		<?php endif; ?>
	</div>
</article>
