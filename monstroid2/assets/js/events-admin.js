/**
 * Meta box Événement : bascule image / vidéo + sélecteur de la médiathèque.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $box = $( '.m2-event-box' );
		if ( ! $box.length ) { return; }

		function togglePanels() {
			var type = $box.find( 'input[name="_event_media_type"]:checked' ).val() || 'image';
			$box.find( '.m2-event-panel' ).removeClass( 'is-active' )
				.filter( '[data-panel="' + type + '"]' ).addClass( 'is-active' );
		}
		$box.on( 'change', 'input[name="_event_media_type"]', togglePanels );
		togglePanels();

		var frames = {};
		$box.on( 'click', '.m2-media-select', function ( e ) {
			e.preventDefault();
			var target = $( this ).data( 'target' ); // image | video

			if ( ! frames[ target ] ) {
				frames[ target ] = wp.media( {
					title: target === 'video' ? 'Choisir une vidéo' : 'Choisir une image',
					button: { text: 'Utiliser ce fichier' },
					library: { type: target },
					multiple: false
				} );
				frames[ target ].on( 'select', function () {
					var att = frames[ target ].state().get( 'selection' ).first().toJSON();
					$( '#_event_' + target + '_id' ).val( att.id );
					var $prev = $( '#m2-event-' + target + '-preview' ).empty();
					if ( target === 'video' ) {
						$( '<video controls muted>' ).attr( 'src', att.url ).appendTo( $prev );
						$( '#_event_video_url' ).val( '' );
					} else {
						var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
						$( '<img alt="">' ).attr( 'src', url ).appendTo( $prev );
					}
				} );
			}
			frames[ target ].open();
		} );

		$box.on( 'click', '.m2-media-remove', function ( e ) {
			e.preventDefault();
			var target = $( this ).data( 'target' );
			$( '#_event_' + target + '_id' ).val( '' );
			$( '#m2-event-' + target + '-preview' ).empty();
		} );
	} );
} )( jQuery );
