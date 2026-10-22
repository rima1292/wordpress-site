/**
 * Événements : onglets À venir / Passés + filtres type & organisateur.
 */
( function () {
	'use strict';

	document.querySelectorAll( '[data-m2-events]' ).forEach( function ( root ) {
		var state = { type: '*', org: '*' };

		function setActive( selector, el ) {
			root.querySelectorAll( selector ).forEach( function ( b ) {
				b.classList.toggle( 'is-active', b === el );
				if ( b.hasAttribute( 'aria-selected' ) ) {
					b.setAttribute( 'aria-selected', b === el ? 'true' : 'false' );
				}
			} );
		}

		function applyFilters() {
			root.querySelectorAll( '.m2-events__panel' ).forEach( function ( panel ) {
				var visible = 0;
				panel.querySelectorAll( '.m2-event-card' ).forEach( function ( card ) {
					var types = ( card.dataset.types || '' ).split( ' ' );
					var orgs  = ( card.dataset.organizers || '' ).split( ' ' );
					var ok = ( state.type === '*' || types.indexOf( state.type ) > -1 ) &&
						( state.org === '*' || orgs.indexOf( state.org ) > -1 );
					card.classList.toggle( 'is-hidden', ! ok );
					if ( ok ) { visible++; }
					// Mettre en pause les vidéos masquées.
					if ( ! ok ) {
						card.querySelectorAll( 'video' ).forEach( function ( v ) { v.pause(); } );
					}
				} );
				var empty = panel.querySelector( '.m2-events__empty' );
				if ( empty ) { empty.hidden = visible > 0; }
			} );
		}

		root.addEventListener( 'click', function ( e ) {
			var tab  = e.target.closest( '[data-tab]' );
			var type = e.target.closest( '[data-filter-type]' );
			var org  = e.target.closest( '[data-filter-org]' );

			if ( tab ) {
				setActive( '[data-tab]', tab );
				root.querySelectorAll( '.m2-events__panel' ).forEach( function ( p ) {
					var active = p.dataset.panel === tab.dataset.tab;
					p.classList.toggle( 'is-active', active );
					if ( ! active ) { p.querySelectorAll( 'video' ).forEach( function ( v ) { v.pause(); } ); }
				} );
			} else if ( type ) {
				state.type = type.dataset.filterType;
				setActive( '[data-filter-type]', type );
				applyFilters();
			} else if ( org ) {
				state.org = org.dataset.filterOrg;
				setActive( '[data-filter-org]', org );
				applyFilters();
			}
		} );
	} );
} )();
