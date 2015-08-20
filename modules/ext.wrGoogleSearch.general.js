/**
 * Point MediaWiki's regular search inputs to Special:GoogleSearch
 *
 * (c) 2013 Dror S. & Kol-Zchut Ltd.
 * GPLv2 or later
 */

( function ( mw, $ ) {
    'use strict';
		$( document ).ready( function() {
			$( '.mw-searchInput').not( '.internalSearch' ).each( function() {
				var $parentForm = $( this ).closest( 'form' );
				$( this ).attr( 'name', 'q' );
				$parentForm.attr( 'action', mw.util.getUrl( 'Special:GoogleSearch') ); // Go to the Google Search Page
			});
		});

}( mediaWiki, jQuery ) );

