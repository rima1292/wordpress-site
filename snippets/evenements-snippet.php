<?php
/**
 * SNIPPET « Événements » (image OU vidéo) – à coller dans Code Snippets / WPCode.
 * Page à créer : titre « Événements », slug « evenements », contenu : [evenements]
 */

/* ---------- 1. Type de contenu + taxonomies ---------- */
add_action( 'init', function () {
	register_post_type( 'evenement', array(
		'labels' => array(
			'name' => 'Événements', 'singular_name' => 'Événement', 'add_new' => 'Ajouter',
			'add_new_item' => 'Ajouter un événement', 'edit_item' => 'Modifier l\'événement',
			'all_items' => 'Tous les événements', 'menu_name' => 'Événements',
		),
		'public' => true, 'has_archive' => false, 'show_in_rest' => true,
		'rewrite' => array( 'slug' => 'evenement', 'with_front' => false ),
		'menu_icon' => 'dashicons-calendar-alt', 'menu_position' => 6,
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
	) );
	register_taxonomy( 'type_evenement', 'evenement', array(
		'label' => 'Types', 'hierarchical' => true, 'show_admin_column' => true, 'show_in_rest' => true,
		'rewrite' => array( 'slug' => 'type-evenement' ),
	) );
	register_taxonomy( 'organisateur', 'evenement', array(
		'label' => 'Organisateurs', 'hierarchical' => true, 'show_admin_column' => true, 'show_in_rest' => true,
		'rewrite' => array( 'slug' => 'organisateur' ),
	) );
	if ( ! get_option( 'evx_flushed' ) ) { flush_rewrite_rules(); update_option( 'evx_flushed', 1 ); }
} );

/* ---------- 2. Champs : média (image OU vidéo), date, heures, lieu, lien ---------- */
function evx_fields() {
	return array(
		'_evx_media' => 'sanitize_key', '_evx_image' => 'absint', '_evx_video' => 'absint',
		'_evx_video_url' => 'esc_url_raw', '_evx_date' => 'sanitize_text_field',
		'_evx_start' => 'sanitize_text_field', '_evx_end' => 'sanitize_text_field',
		'_evx_lieu' => 'sanitize_text_field', '_evx_lien' => 'esc_url_raw', '_evx_btn' => 'sanitize_text_field',
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'evx_box', 'Détails de l\'événement', 'evx_box_html', 'evenement', 'normal', 'high' );
} );

function evx_box_html( $post ) {
	wp_nonce_field( 'evx_save', 'evx_nonce' );
	$v = array();
	foreach ( array_keys( evx_fields() ) as $k ) { $v[ $k ] = get_post_meta( $post->ID, $k, true ); }
	$media = $v['_evx_media'] ? $v['_evx_media'] : 'image';
	$img   = $v['_evx_image'] ? wp_get_attachment_image_url( $v['_evx_image'], 'medium' ) : '';
	$vid   = $v['_evx_video'] ? wp_get_attachment_url( $v['_evx_video'] ) : '';
	?>
	<style>
		.evx-panel{display:none;padding:12px;background:#f6f7f7;border:1px solid #ddd;border-radius:4px;margin:8px 0}
		.evx-panel.on{display:block}.evx-prev img,.evx-prev video{max-width:100%;max-height:240px;display:block;margin-bottom:8px}
		.evx-row{display:flex;gap:16px;flex-wrap:wrap}.evx-row p{flex:1;min-width:150px}
	</style>
	<div id="evx-box">
		<p><strong>Contenu média :</strong>
			<label><input type="radio" name="_evx_media" value="image" <?php checked( $media, 'image' ); ?>> Image</label> &nbsp;
			<label><input type="radio" name="_evx_media" value="video" <?php checked( $media, 'video' ); ?>> Vidéo</label>
		</p>
		<div class="evx-panel" data-p="image">
			<input type="hidden" name="_evx_image" id="evx-image" value="<?php echo esc_attr( $v['_evx_image'] ); ?>">
			<div class="evx-prev" id="evx-image-prev"><?php if ( $img ) echo '<img src="' . esc_url( $img ) . '">'; ?></div>
			<button type="button" class="button evx-pick" data-t="image">Choisir une image</button>
			<button type="button" class="button-link evx-del" data-t="image">Retirer</button>
			<p class="description">Si vide, l'image mise en avant est utilisée.</p>
		</div>
		<div class="evx-panel" data-p="video">
			<input type="hidden" name="_evx_video" id="evx-video" value="<?php echo esc_attr( $v['_evx_video'] ); ?>">
			<div class="evx-prev" id="evx-video-prev"><?php if ( $vid ) echo '<video src="' . esc_url( $vid ) . '" controls muted></video>'; ?></div>
			<button type="button" class="button evx-pick" data-t="video">Téléverser / choisir une vidéo</button>
			<button type="button" class="button-link evx-del" data-t="video">Retirer</button>
			<p>… ou lien YouTube / Vimeo / .mp4 :<br>
			<input type="url" class="widefat" name="_evx_video_url" value="<?php echo esc_attr( $v['_evx_video_url'] ); ?>" placeholder="https://www.youtube.com/watch?v=..."></p>
			<p class="description">L'image mise en avant sert d'affiche à la vidéo.</p>
		</div>
		<div class="evx-row">
			<p>Date<br><input type="date" name="_evx_date" value="<?php echo esc_attr( $v['_evx_date'] ); ?>"></p>
			<p>Début<br><input type="time" name="_evx_start" value="<?php echo esc_attr( $v['_evx_start'] ); ?>"></p>
			<p>Fin<br><input type="time" name="_evx_end" value="<?php echo esc_attr( $v['_evx_end'] ); ?>"></p>
		</div>
		<p>Lieu<br><input type="text" class="widefat" name="_evx_lieu" value="<?php echo esc_attr( $v['_evx_lieu'] ); ?>"></p>
		<div class="evx-row">
			<p style="flex:2">Lien d'inscription<br><input type="url" class="widefat" name="_evx_lien" value="<?php echo esc_attr( $v['_evx_lien'] ); ?>"></p>
			<p>Texte du bouton<br><input type="text" class="widefat" name="_evx_btn" value="<?php echo esc_attr( $v['_evx_btn'] ); ?>" placeholder="Je m'inscris"></p>
		</div>
	</div>
	<script>
	jQuery(function($){
		var $b=$('#evx-box'),frames={};
		function sw(){var t=$b.find('[name=_evx_media]:checked').val()||'image';$b.find('.evx-panel').removeClass('on').filter('[data-p='+t+']').addClass('on');}
		$b.on('change','[name=_evx_media]',sw);sw();
		$b.on('click','.evx-pick',function(e){e.preventDefault();var t=$(this).data('t');
			if(!frames[t]){frames[t]=wp.media({title:t==='video'?'Choisir une vidéo':'Choisir une image',library:{type:t},multiple:false,button:{text:'Utiliser'}});
				frames[t].on('select',function(){var a=frames[t].state().get('selection').first().toJSON();$('#evx-'+t).val(a.id);
					var $p=$('#evx-'+t+'-prev').empty();
					if(t==='video'){$('<video controls muted>').attr('src',a.url).appendTo($p);}
					else{$('<img>').attr('src',a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url).appendTo($p);}});}
			frames[t].open();});
		$b.on('click','.evx-del',function(e){e.preventDefault();var t=$(this).data('t');$('#evx-'+t).val('');$('#evx-'+t+'-prev').empty();});
	});
	</script>
	<?php
}

add_action( 'admin_enqueue_scripts', function () {
	$s = get_current_screen();
	if ( $s && 'evenement' === $s->post_type ) { wp_enqueue_media(); }
} );

add_action( 'save_post_evenement', function ( $post_id ) {
	if ( ! isset( $_POST['evx_nonce'] ) || ! wp_verify_nonce( $_POST['evx_nonce'], 'evx_save' ) ) return;
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) return;
	foreach ( evx_fields() as $k => $fn ) {
		$val = isset( $_POST[ $k ] ) ? call_user_func( $fn, wp_unslash( $_POST[ $k ] ) ) : '';
		if ( '' === $val || 0 === $val ) { delete_post_meta( $post_id, $k ); } else { update_post_meta( $post_id, $k, $val ); }
	}
} );

/* ---------- 3. Fonctions d'affichage ---------- */
function evx_date( $id ) { $d = get_post_meta( $id, '_evx_date', true ); return $d ? date_i18n( 'j F Y', strtotime( $d ) ) : ''; }
function evx_time( $id ) {
	$s = get_post_meta( $id, '_evx_start', true ); $e = get_post_meta( $id, '_evx_end', true );
	return $s && $e ? "De $s à $e" : ( $s ? "À $s" : '' );
}
function evx_is_past( $id ) { $d = get_post_meta( $id, '_evx_date', true ); return $d && $d < current_time( 'Y-m-d' ); }

function evx_media( $id, $size = 'large' ) {
	$type = get_post_meta( $id, '_evx_media', true );
	$img  = (int) get_post_meta( $id, '_evx_image', true );
	if ( ! $img ) { $img = (int) get_post_thumbnail_id( $id ); }
	$out = '<div class="evx-media">';
	if ( 'video' === $type ) {
		$vid = (int) get_post_meta( $id, '_evx_video', true );
		$url = $vid ? wp_get_attachment_url( $vid ) : get_post_meta( $id, '_evx_video_url', true );
		if ( $url && preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m ) ) {
			$out .= '<iframe src="https://www.youtube-nocookie.com/embed/' . esc_attr( $m[1] ) . '?rel=0" allowfullscreen loading="lazy" allow="autoplay; encrypted-media; picture-in-picture"></iframe>';
		} elseif ( $url && preg_match( '~vimeo\.com/(?:video/)?(\d+)~', $url, $m ) ) {
			$out .= '<iframe src="https://player.vimeo.com/video/' . esc_attr( $m[1] ) . '" allowfullscreen loading="lazy"></iframe>';
		} elseif ( $url ) {
			$poster = $img ? ' poster="' . esc_url( wp_get_attachment_image_url( $img, $size ) ) . '"' : '';
			$out   .= '<video src="' . esc_url( $url ) . '"' . $poster . ' controls playsinline preload="metadata"></video>';
		} elseif ( $img ) {
			$out .= wp_get_attachment_image( $img, $size );
		}
	} elseif ( $img ) {
		$out .= '<a href="' . esc_url( get_permalink( $id ) ) . '">' . wp_get_attachment_image( $img, $size ) . '</a>';
	}
	return $out . '</div>';
}

function evx_meta_list( $id ) {
	$ico = array(
		'd' => '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>',
		't' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
		'l' => '<svg viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>',
	);
	$o = '<ul class="evx-meta">';
	if ( $d = evx_date( $id ) ) $o .= '<li>' . $ico['d'] . esc_html( $d ) . '</li>';
	if ( $t = evx_time( $id ) ) $o .= '<li>' . $ico['t'] . esc_html( $t ) . '</li>';
	if ( $l = get_post_meta( $id, '_evx_lieu', true ) ) $o .= '<li>' . $ico['l'] . esc_html( $l ) . '</li>';
	return $o . '</ul>';
}

function evx_button( $id ) {
	$lien = get_post_meta( $id, '_evx_lien', true );
	$arrow = '<svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
	if ( $lien && ! evx_is_past( $id ) ) {
		$txt = get_post_meta( $id, '_evx_btn', true );
		return '<a class="evx-btn" href="' . esc_url( $lien ) . '" target="_blank" rel="noopener">' . esc_html( $txt ? $txt : 'Je m\'inscris' ) . $arrow . '</a>';
	}
	return '';
}

function evx_css() {
	static $done = false; if ( $done ) return ''; $done = true;
	return '<style>
	.evx{--a:#c8102e;--l:#e2e2e2;--s:#f5f2f0;color:#1d1d1b}
	.evx-types{display:flex;flex-wrap:wrap;gap:12px;margin:10px 0 40px}
	.evx-types button{background:var(--s);border:1px solid transparent;border-radius:10px;padding:16px 24px;font-weight:600;cursor:pointer;color:#1d1d1b}
	.evx-types button.on,.evx-types button:hover{border-color:var(--a);color:var(--a);background:#fff}
	.evx-tabs{display:flex;gap:40px;flex-wrap:wrap}
	.evx-tabs button{background:none;border:0;border-bottom:4px solid transparent;padding:0 0 12px;font-size:clamp(22px,3vw,38px);font-weight:700;color:#d4d4d4;cursor:pointer}
	.evx-tabs button.on{color:#1d1d1b;border-color:var(--a)}
	.evx-orgs{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;border-top:1px solid var(--l);border-bottom:1px solid var(--l);padding:8px 0;margin-bottom:20px;font-size:13px;font-weight:600;text-transform:uppercase}
	.evx-orgs div{display:flex;gap:18px;flex-wrap:wrap}
	.evx-orgs button{background:none;border:0;border-bottom:2px solid transparent;padding:4px 0;font:inherit;text-transform:inherit;cursor:pointer;color:#1d1d1b}
	.evx-orgs button.on{color:var(--a);border-color:var(--a)}
	.evx-panel{display:none}.evx-panel.on{display:block}.evx-empty{color:#777;padding:30px 0}
	.evx-card{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:center;padding:28px 0;border-bottom:1px solid var(--l)}
	.evx-card.hide{display:none}
	.evx-media{position:relative;width:100%;aspect-ratio:16/10;overflow:hidden;background:#000;border-radius:4px}
	.evx-media img,.evx-media video,.evx-media iframe{position:absolute;inset:0;width:100%!important;height:100%!important;border:0;object-fit:cover;max-width:none}
	.evx-media video{object-fit:contain}
	.evx-tag{display:inline-block;background:#b9dcef;color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;padding:5px 12px;border-radius:20px;margin:0 6px 10px 0}
	.evx-card h3{font-size:22px;margin:0 0 12px}.evx-card h3 a{color:inherit;text-decoration:none}.evx-card h3 a:hover{color:var(--a)}
	.evx-meta{list-style:none;margin:0 0 18px;padding:0}
	.evx-meta li{display:flex;align-items:center;gap:10px;font-size:13px;text-transform:uppercase;margin:8px 0}
	.evx svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex:none}
	.evx-btn{display:inline-flex;align-items:center;gap:10px;background:var(--a);color:#fff!important;text-transform:uppercase;font-weight:700;font-size:13px;padding:12px 22px;text-decoration:none}
	.evx-btn:hover{background:#9e0c24}
	.evx-single .evx-media{aspect-ratio:16/9;margin-bottom:30px}
	.evx-single-box{background:var(--s);padding:24px;border-radius:6px;margin-bottom:30px}
	@media(max-width:767px){.evx-card{grid-template-columns:1fr;gap:16px}.evx-tabs{gap:20px}}
	</style>';
}

/* ---------- 4. Shortcode [evenements] : la page liste ---------- */
add_shortcode( 'evenements', function () {
	$today = current_time( 'Y-m-d' );
	$types = get_terms( array( 'taxonomy' => 'type_evenement', 'hide_empty' => true ) );
	$orgs  = get_terms( array( 'taxonomy' => 'organisateur', 'hide_empty' => true ) );

	ob_start();
	echo evx_css();
	echo '<div class="evx" id="evx-list">';

	if ( $types && ! is_wp_error( $types ) ) {
		echo '<div class="evx-types"><button class="on" data-type="*">Tous</button>';
		foreach ( $types as $t ) echo '<button data-type="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</button>';
		echo '</div>';
	}

	echo '<div class="evx-tabs"><button class="on" data-tab="upcoming">Événements à venir</button><button data-tab="past">Événements passés</button></div>';

	if ( $orgs && ! is_wp_error( $orgs ) ) {
		echo '<div class="evx-orgs"><span>Organisés par</span><div><button class="on" data-org="*">Tous</button>';
		foreach ( $orgs as $o ) echo '<button data-org="' . esc_attr( $o->slug ) . '">' . esc_html( $o->name ) . '</button>';
		echo '</div></div>';
	}

	foreach ( array( 'upcoming', 'past' ) as $when ) {
		$q = new WP_Query( array(
			'post_type' => 'evenement', 'posts_per_page' => 'past' === $when ? 20 : -1,
			'meta_key' => '_evx_date', 'orderby' => 'meta_value', 'order' => 'past' === $when ? 'DESC' : 'ASC',
			'meta_query' => array( array( 'key' => '_evx_date', 'value' => $today, 'compare' => 'past' === $when ? '<' : '>=', 'type' => 'DATE' ) ),
		) );
		echo '<div class="evx-panel' . ( 'upcoming' === $when ? ' on' : '' ) . '" data-panel="' . $when . '">';
		while ( $q->have_posts() ) {
			$q->the_post(); $id = get_the_ID();
			$tt = get_the_terms( $id, 'type_evenement' ); $oo = get_the_terms( $id, 'organisateur' );
			$ts = $tt && ! is_wp_error( $tt ) ? wp_list_pluck( $tt, 'slug' ) : array();
			$os = $oo && ! is_wp_error( $oo ) ? wp_list_pluck( $oo, 'slug' ) : array();
			echo '<article class="evx-card" data-types="' . esc_attr( implode( ' ', $ts ) ) . '" data-orgs="' . esc_attr( implode( ' ', $os ) ) . '">';
			echo evx_media( $id );
			echo '<div>';
			if ( $ts ) foreach ( $tt as $t ) echo '<span class="evx-tag">' . esc_html( $t->name ) . '</span>';
			echo '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
			if ( has_excerpt() ) echo '<div>' . wpautop( esc_html( get_the_excerpt() ) ) . '</div>';
			echo evx_meta_list( $id ) . evx_button( $id );
			echo '</div></article>';
		}
		wp_reset_postdata();
		echo '<p class="evx-empty"' . ( $q->found_posts ? ' hidden' : '' ) . '>' . ( 'upcoming' === $when ? 'Aucun événement à venir pour le moment.' : 'Aucun événement passé.' ) . '</p>';
		echo '</div>';
	}
	echo '</div>';
	?>
	<script>
	(function(){var r=document.getElementById('evx-list');if(!r)return;var st={type:'*',org:'*'};
	function act(sel,el){r.querySelectorAll(sel).forEach(function(b){b.classList.toggle('on',b===el);});}
	function filt(){r.querySelectorAll('.evx-panel').forEach(function(p){var n=0;
		p.querySelectorAll('.evx-card').forEach(function(c){var ok=(st.type==='*'||(' '+c.dataset.types+' ').indexOf(' '+st.type+' ')>-1)&&(st.org==='*'||(' '+c.dataset.orgs+' ').indexOf(' '+st.org+' ')>-1);
			c.classList.toggle('hide',!ok);if(ok)n++;else c.querySelectorAll('video').forEach(function(v){v.pause();});});
		var e=p.querySelector('.evx-empty');if(e)e.hidden=n>0;});}
	r.addEventListener('click',function(e){var b=e.target.closest('button');if(!b)return;
		if(b.dataset.tab){act('[data-tab]',b);r.querySelectorAll('.evx-panel').forEach(function(p){var on=p.dataset.panel===b.dataset.tab;p.classList.toggle('on',on);if(!on)p.querySelectorAll('video').forEach(function(v){v.pause();});});}
		else if(b.dataset.type){st.type=b.dataset.type;act('[data-type]',b);filt();}
		else if(b.dataset.org){st.org=b.dataset.org;act('[data-org]',b);filt();}});})();
	</script>
	<?php
	return ob_get_clean();
} );

/* ---------- 5. Page détail d'un événement (média + infos au-dessus du texte) ---------- */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'evenement' ) || ! in_the_loop() || ! is_main_query() ) return $content;
	$id  = get_the_ID();
	$top = evx_css() . '<div class="evx evx-single">' . evx_media( $id, 'full' );
	$top .= '<div class="evx-single-box">' . evx_meta_list( $id ) . evx_button( $id );
	if ( evx_is_past( $id ) ) $top .= '<p style="color:#c8102e;font-weight:600;margin:0">Cet événement est terminé.</p>';
	$top .= '</div></div>';
	return $top . $content;
} );

/* ---------- 6. Compatibilité : shortcode dans les blocs / widgets HTML ---------- */
// Widgets « HTML personnalisé » et « Texte ».
add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'widget_custom_html_content', 'do_shortcode' );
// Widget « HTML » d'Elementor.
add_filter( 'elementor/widget/render_content', function ( $content, $widget ) {
	return ( 'html' === $widget->get_name() && false !== strpos( $content, '[evenements' ) ) ? do_shortcode( $content ) : $content;
}, 10, 2 );
// Bloc « HTML personnalisé » de Gutenberg.
add_filter( 'render_block', function ( $html, $block ) {
	return ( 'core/html' === $block['blockName'] && false !== strpos( $html, '[evenements' ) ) ? do_shortcode( $html ) : $html;
}, 10, 2 );

/* ---------- 7. Affichage automatique sur la page « evenements » (sans shortcode) ---------- */
add_filter( 'the_content', function ( $content ) {
	if ( is_page( 'evenements' ) && in_the_loop() && is_main_query()
		&& false === strpos( $content, 'evx-list' ) && ! has_shortcode( get_post()->post_content, 'evenements' ) ) {
		$content .= do_shortcode( '[evenements]' );
	}
	return $content;
}, 20 );
