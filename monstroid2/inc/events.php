<?php
/**
 * Événements : custom post type, taxonomies, champs (image OU vidéo), assets.
 *
 * @package Monstroid2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. Post type + taxonomies
 * ---------------------------------------------------------------------- */
function monstroid2_register_events() {

	register_post_type( 'event', array(
		'labels' => array(
			'name'               => __( 'Événements', 'monstroid2' ),
			'singular_name'      => __( 'Événement', 'monstroid2' ),
			'add_new'            => __( 'Ajouter', 'monstroid2' ),
			'add_new_item'       => __( 'Ajouter un événement', 'monstroid2' ),
			'edit_item'          => __( 'Modifier l\'événement', 'monstroid2' ),
			'new_item'           => __( 'Nouvel événement', 'monstroid2' ),
			'view_item'          => __( 'Voir l\'événement', 'monstroid2' ),
			'all_items'          => __( 'Tous les événements', 'monstroid2' ),
			'search_items'       => __( 'Rechercher un événement', 'monstroid2' ),
			'not_found'          => __( 'Aucun événement trouvé', 'monstroid2' ),
			'not_found_in_trash' => __( 'Aucun événement dans la corbeille', 'monstroid2' ),
			'menu_name'          => __( 'Événements', 'monstroid2' ),
		),
		'public'       => true,
		'has_archive'  => 'evenements',
		'rewrite'      => array( 'slug' => 'evenement', 'with_front' => false ),
		'menu_icon'    => 'dashicons-calendar-alt',
		'menu_position'=> 6,
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
	) );

	register_taxonomy( 'event_type', 'event', array(
		'labels' => array(
			'name'          => __( 'Types d\'événement', 'monstroid2' ),
			'singular_name' => __( 'Type d\'événement', 'monstroid2' ),
			'add_new_item'  => __( 'Ajouter un type', 'monstroid2' ),
			'menu_name'     => __( 'Types', 'monstroid2' ),
		),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'type-evenement' ),
	) );

	register_taxonomy( 'event_organizer', 'event', array(
		'labels' => array(
			'name'          => __( 'Organisateurs', 'monstroid2' ),
			'singular_name' => __( 'Organisateur', 'monstroid2' ),
			'add_new_item'  => __( 'Ajouter un organisateur', 'monstroid2' ),
			'menu_name'     => __( 'Organisateurs', 'monstroid2' ),
		),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'organisateur' ),
	) );
}
add_action( 'init', 'monstroid2_register_events' );

/** Flush des permaliens à l'activation du thème. */
function monstroid2_events_flush_rewrite() {
	monstroid2_register_events();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'monstroid2_events_flush_rewrite' );

/* -------------------------------------------------------------------------
 * 2. Meta box : média (image OU vidéo), date, heures, inscription
 * ---------------------------------------------------------------------- */
function monstroid2_event_meta_keys() {
	return array(
		'_event_media_type' => 'key',      // image | video
		'_event_image_id'   => 'absint',
		'_event_video_id'   => 'absint',   // vidéo de la médiathèque
		'_event_video_url'  => 'esc_url_raw', // YouTube / Vimeo / mp4 externe
		'_event_date'       => 'sanitize_text_field', // Y-m-d
		'_event_time_start' => 'sanitize_text_field', // H:i
		'_event_time_end'   => 'sanitize_text_field',
		'_event_location'   => 'sanitize_text_field',
		'_event_link'       => 'esc_url_raw',
		'_event_link_label' => 'sanitize_text_field',
	);
}

function monstroid2_event_add_meta_box() {
	add_meta_box( 'monstroid2-event-details', __( 'Détails de l\'événement', 'monstroid2' ), 'monstroid2_event_meta_box_html', 'event', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'monstroid2_event_add_meta_box' );

function monstroid2_event_meta_box_html( $post ) {
	wp_nonce_field( 'monstroid2_event_save', 'monstroid2_event_nonce' );

	$m = array();
	foreach ( array_keys( monstroid2_event_meta_keys() ) as $key ) {
		$m[ $key ] = get_post_meta( $post->ID, $key, true );
	}
	$type      = $m['_event_media_type'] ? $m['_event_media_type'] : 'image';
	$image_url = $m['_event_image_id'] ? wp_get_attachment_image_url( $m['_event_image_id'], 'medium' ) : '';
	$video_src = $m['_event_video_id'] ? wp_get_attachment_url( $m['_event_video_id'] ) : '';
	?>
	<div class="m2-event-box">
		<p><strong><?php esc_html_e( 'Contenu média', 'monstroid2' ); ?></strong></p>
		<p class="m2-event-media-switch">
			<label><input type="radio" name="_event_media_type" value="image" <?php checked( $type, 'image' ); ?>> <?php esc_html_e( 'Image', 'monstroid2' ); ?></label>
			&nbsp;&nbsp;
			<label><input type="radio" name="_event_media_type" value="video" <?php checked( $type, 'video' ); ?>> <?php esc_html_e( 'Vidéo', 'monstroid2' ); ?></label>
		</p>

		<div class="m2-event-panel" data-panel="image">
			<input type="hidden" name="_event_image_id" id="_event_image_id" value="<?php echo esc_attr( $m['_event_image_id'] ); ?>">
			<div class="m2-event-preview" id="m2-event-image-preview"><?php if ( $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt=""><?php endif; ?></div>
			<button type="button" class="button m2-media-select" data-target="image"><?php esc_html_e( 'Choisir une image', 'monstroid2' ); ?></button>
			<button type="button" class="button-link m2-media-remove" data-target="image"><?php esc_html_e( 'Retirer', 'monstroid2' ); ?></button>
			<p class="description"><?php esc_html_e( 'Si vide, l\'image mise en avant est utilisée.', 'monstroid2' ); ?></p>
		</div>

		<div class="m2-event-panel" data-panel="video">
			<input type="hidden" name="_event_video_id" id="_event_video_id" value="<?php echo esc_attr( $m['_event_video_id'] ); ?>">
			<div class="m2-event-preview" id="m2-event-video-preview"><?php if ( $video_src ) : ?><video src="<?php echo esc_url( $video_src ); ?>" controls muted></video><?php endif; ?></div>
			<button type="button" class="button m2-media-select" data-target="video"><?php esc_html_e( 'Téléverser / choisir une vidéo', 'monstroid2' ); ?></button>
			<button type="button" class="button-link m2-media-remove" data-target="video"><?php esc_html_e( 'Retirer', 'monstroid2' ); ?></button>
			<p>
				<label for="_event_video_url"><?php esc_html_e( '… ou lien vidéo (YouTube, Vimeo, .mp4) :', 'monstroid2' ); ?></label><br>
				<input type="url" class="widefat" name="_event_video_url" id="_event_video_url" value="<?php echo esc_attr( $m['_event_video_url'] ); ?>" placeholder="https://www.youtube.com/watch?v=...">
			</p>
			<p class="description"><?php esc_html_e( 'L\'image mise en avant sert d\'affiche (poster) à la vidéo.', 'monstroid2' ); ?></p>
		</div>

		<hr>
		<div class="m2-event-grid">
			<p><label><?php esc_html_e( 'Date', 'monstroid2' ); ?><br><input type="date" name="_event_date" value="<?php echo esc_attr( $m['_event_date'] ); ?>"></label></p>
			<p><label><?php esc_html_e( 'Heure de début', 'monstroid2' ); ?><br><input type="time" name="_event_time_start" value="<?php echo esc_attr( $m['_event_time_start'] ); ?>"></label></p>
			<p><label><?php esc_html_e( 'Heure de fin', 'monstroid2' ); ?><br><input type="time" name="_event_time_end" value="<?php echo esc_attr( $m['_event_time_end'] ); ?>"></label></p>
		</div>
		<p><label><?php esc_html_e( 'Lieu', 'monstroid2' ); ?><br><input type="text" class="widefat" name="_event_location" value="<?php echo esc_attr( $m['_event_location'] ); ?>" placeholder="Campus de Lyon, en ligne…"></label></p>
		<div class="m2-event-grid">
			<p style="flex:2"><label><?php esc_html_e( 'Lien d\'inscription', 'monstroid2' ); ?><br><input type="url" class="widefat" name="_event_link" value="<?php echo esc_attr( $m['_event_link'] ); ?>"></label></p>
			<p style="flex:1"><label><?php esc_html_e( 'Texte du bouton', 'monstroid2' ); ?><br><input type="text" class="widefat" name="_event_link_label" value="<?php echo esc_attr( $m['_event_link_label'] ); ?>" placeholder="<?php esc_attr_e( 'Je m\'inscris', 'monstroid2' ); ?>"></label></p>
		</div>
	</div>
	<?php
}

function monstroid2_event_save_meta( $post_id ) {
	if ( ! isset( $_POST['monstroid2_event_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['monstroid2_event_nonce'] ), 'monstroid2_event_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( monstroid2_event_meta_keys() as $key => $sanitize ) {
		$value = isset( $_POST[ $key ] ) ? call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) ) : '';
		if ( '_event_media_type' === $key && ! in_array( $value, array( 'image', 'video' ), true ) ) {
			$value = 'image';
		}
		if ( '' === $value || 0 === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_event', 'monstroid2_event_save_meta' );

/** Colonne « Date » dans l'admin. */
add_filter( 'manage_event_posts_columns', function ( $cols ) {
	$cols['event_date'] = __( 'Date de l\'événement', 'monstroid2' );
	$cols['event_media'] = __( 'Média', 'monstroid2' );
	return $cols;
} );
add_action( 'manage_event_posts_custom_column', function ( $col, $post_id ) {
	if ( 'event_date' === $col ) {
		echo esc_html( monstroid2_event_formatted_date( $post_id ) );
	} elseif ( 'event_media' === $col ) {
		echo 'video' === get_post_meta( $post_id, '_event_media_type', true ) ? '🎬 ' . esc_html__( 'Vidéo', 'monstroid2' ) : '🖼 ' . esc_html__( 'Image', 'monstroid2' );
	}
}, 10, 2 );

/* -------------------------------------------------------------------------
 * 3. Assets
 * ---------------------------------------------------------------------- */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'event' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'monstroid2-events-admin', get_theme_file_uri( 'assets/css/events-admin.css' ), array(), monstroid2_theme()->version() );
	wp_enqueue_script( 'monstroid2-events-admin', get_theme_file_uri( 'assets/js/events-admin.js' ), array( 'jquery' ), monstroid2_theme()->version(), true );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'monstroid2-events', get_theme_file_uri( 'assets/css/events.css' ), array(), monstroid2_theme()->version() );
	wp_register_script( 'monstroid2-events', get_theme_file_uri( 'assets/js/events.js' ), array(), monstroid2_theme()->version(), true );

	if ( is_post_type_archive( 'event' ) || is_singular( 'event' ) || is_tax( array( 'event_type', 'event_organizer' ) ) || is_page_template( 'page-templates/events.php' ) ) {
		wp_enqueue_style( 'monstroid2-events' );
		wp_enqueue_script( 'monstroid2-events' );
	}
}, 20 );

/* -------------------------------------------------------------------------
 * 4. Helpers d'affichage
 * ---------------------------------------------------------------------- */

/** Date formatée « 24 octobre 2026 ». */
function monstroid2_event_formatted_date( $post_id = null ) {
	$date = get_post_meta( $post_id ? $post_id : get_the_ID(), '_event_date', true );
	return $date ? date_i18n( 'j F Y', strtotime( $date ) ) : '';
}

/** Plage horaire « De 10:00 à 13:00 ». */
function monstroid2_event_formatted_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$start   = get_post_meta( $post_id, '_event_time_start', true );
	$end     = get_post_meta( $post_id, '_event_time_end', true );
	if ( $start && $end ) {
		/* translators: 1: start time, 2: end time */
		return sprintf( __( 'De %1$s à %2$s', 'monstroid2' ), $start, $end );
	}
	return $start ? sprintf( __( 'À %s', 'monstroid2' ), $start ) : '';
}

/** Est-ce un événement passé ? */
function monstroid2_event_is_past( $post_id = null ) {
	$date = get_post_meta( $post_id ? $post_id : get_the_ID(), '_event_date', true );
	return $date && $date < current_time( 'Y-m-d' );
}

/** Transforme une URL YouTube / Vimeo en URL d'embed, sinon false. */
function monstroid2_event_embed_url( $url ) {
	if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $mt ) ) {
		return 'https://www.youtube-nocookie.com/embed/' . $mt[1] . '?rel=0';
	}
	if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~', $url, $mt ) ) {
		return 'https://player.vimeo.com/video/' . $mt[1];
	}
	return false;
}

/**
 * Affiche le média de l'événement : image OU vidéo.
 *
 * @param int    $post_id ID.
 * @param string $size    Taille d'image.
 */
function monstroid2_event_media( $post_id = null, $size = 'large' ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$type     = get_post_meta( $post_id, '_event_media_type', true );
	$image_id = (int) get_post_meta( $post_id, '_event_image_id', true );
	if ( ! $image_id ) {
		$image_id = (int) get_post_thumbnail_id( $post_id );
	}

	echo '<div class="m2-event-media m2-event-media--' . esc_attr( 'video' === $type ? 'video' : 'image' ) . '">';

	if ( 'video' === $type ) {
		$video_id  = (int) get_post_meta( $post_id, '_event_video_id', true );
		$video_url = $video_id ? wp_get_attachment_url( $video_id ) : get_post_meta( $post_id, '_event_video_url', true );
		$embed     = $video_url ? monstroid2_event_embed_url( $video_url ) : false;
		$poster    = $image_id ? wp_get_attachment_image_url( $image_id, $size ) : '';

		if ( $embed ) {
			printf(
				'<iframe src="%s" title="%s" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
				esc_url( $embed ),
				esc_attr( get_the_title( $post_id ) )
			);
		} elseif ( $video_url ) {
			printf(
				'<video src="%s" %s controls playsinline preload="metadata"></video>',
				esc_url( $video_url ),
				$poster ? 'poster="' . esc_url( $poster ) . '"' : ''
			);
		} elseif ( $image_id ) {
			echo wp_get_attachment_image( $image_id, $size );
		}
	} elseif ( $image_id ) {
		echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . wp_get_attachment_image( $image_id, $size ) . '</a>';
	} else {
		echo '<div class="m2-event-media__placeholder"></div>';
	}

	echo '</div>';
}

/**
 * Requête des événements à venir / passés.
 *
 * @param string $when     upcoming|past.
 * @param array  $tax_args Filtres supplémentaires.
 * @return WP_Query
 */
function monstroid2_events_query( $when = 'upcoming', $tax_args = array() ) {
	$today = current_time( 'Y-m-d' );
	$args  = array(
		'post_type'      => 'event',
		'posts_per_page' => 'past' === $when ? 12 : -1,
		'meta_key'       => '_event_date',
		'orderby'        => 'meta_value',
		'order'          => 'past' === $when ? 'DESC' : 'ASC',
		'meta_query'     => array(
			array(
				'key'     => '_event_date',
				'value'   => $today,
				'compare' => 'past' === $when ? '<' : '>=',
				'type'    => 'DATE',
			),
		),
	);
	if ( $tax_args ) {
		$args['tax_query'] = $tax_args;
	}
	return new WP_Query( $args );
}
