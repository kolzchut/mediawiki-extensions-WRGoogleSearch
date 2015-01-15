/**
 * Google Custom Search Auto-complete for MediaWiki's regular search inputs
 * (c) 2013 Dror Snir <dror.snir@kolzchut.org.il>
 * GPLv2 or later
 *
 */

( function ( mw, $ ) {
    /* global mediaWiki */
    "use strict";
		$( document ).ready( function() {
			$( '.mw-searchInput').each( function() {
				var $parentForm = $( this ).closest("form");
				$( this ).attr( 'name', 'q' );
				$parentForm.attr( 'action', mw.util.getUrl( 'Special:GoogleSearch') ); // Go to the Google Search Page
			});
		});

}( mediaWiki, jQuery ) );

