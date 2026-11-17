<?php
/**
 * Liste des événements : filtres par type, onglets À venir / Passés, filtre organisateur.
 *
 * @package Monstroid2
 */

$types      = get_terms( array( 'taxonomy' => 'event_type', 'hide_empty' => true ) );
$organizers = get_terms( array( 'taxonomy' => 'event_organizer', 'hide_empty' => true ) );
$tax_args   = array();

if ( is_tax( array( 'event_type', 'event_organizer' ) ) ) {
	$term       = get_queried_object();
	$tax_args[] = array( 'taxonomy' => $term->taxonomy, 'field' => 'term_id', 'terms' => $term->term_id );
}

$lists = array(
	'upcoming' => monstroid2_events_query( 'upcoming', $tax_args ),
	'past'     => monstroid2_events_query( 'past', $tax_args ),
);
?>
<div class="m2-events" data-m2-events>

	<?php if ( $types && ! is_wp_error( $types ) ) : ?>
		<nav class="m2-events__types" aria-label="<?php esc_attr_e( 'Types d\'événement', 'monstroid2' ); ?>">
			<button type="button" class="m2-events__type is-active" data-filter-type="*"><?php esc_html_e( 'Tous', 'monstroid2' ); ?></button>
			<?php foreach ( $types as $type ) : ?>
				<button type="button" class="m2-events__type" data-filter-type="<?php echo esc_attr( $type->slug ); ?>"><?php echo esc_html( $type->name ); ?></button>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<div class="m2-events__tabs" role="tablist">
		<button type="button" role="tab" class="m2-events__tab is-active" aria-selected="true" data-tab="upcoming"><?php esc_html_e( 'Événements à venir', 'monstroid2' ); ?></button>
		<button type="button" role="tab" class="m2-events__tab" aria-selected="false" data-tab="past"><?php esc_html_e( 'Événements passés', 'monstroid2' ); ?></button>
	</div>

	<?php if ( $organizers && ! is_wp_error( $organizers ) ) : ?>
		<div class="m2-events__orgs">
			<span class="m2-events__orgs-label"><?php esc_html_e( 'Organisés par', 'monstroid2' ); ?></span>
			<div class="m2-events__orgs-list">
				<button type="button" class="m2-events__org is-active" data-filter-org="*"><?php esc_html_e( 'Tous', 'monstroid2' ); ?></button>
				<?php foreach ( $organizers as $org ) : ?>
					<button type="button" class="m2-events__org" data-filter-org="<?php echo esc_attr( $org->slug ); ?>"><?php echo esc_html( $org->name ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php foreach ( $lists as $key => $query ) : ?>
		<div class="m2-events__panel<?php echo 'upcoming' === $key ? ' is-active' : ''; ?>" role="tabpanel" data-panel="<?php echo esc_attr( $key ); ?>">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/events/card' );
				endwhile;
				wp_reset_postdata();
			endif;
			?>
			<p class="m2-events__empty"<?php echo $query->found_posts ? ' hidden' : ''; ?>>
				<?php echo 'upcoming' === $key ? esc_html__( 'Aucun événement à venir pour le moment.', 'monstroid2' ) : esc_html__( 'Aucun événement passé.', 'monstroid2' ); ?>
			</p>
		</div>
	<?php endforeach; ?>
</div>
