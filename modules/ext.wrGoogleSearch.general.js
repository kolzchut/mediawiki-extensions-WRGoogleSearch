/**
 * Point MediaWiki's regular search inputs to Special:GoogleSearch
 *
 * (c) 2013 Dror S. & Kol-Zchut Ltd.
 * GPLv2 or later
 */

'use strict';

const googleSearchUrl = mw.util.getUrl( 'Special:GoogleSearch' );

Array.prototype.forEach.call(
	document.querySelectorAll( '.mw-searchInput:not(.internalSearch)' ),
	( input ) => {
		input.setAttribute( 'name', 'q' );
		input.closest( 'form' ).setAttribute( 'action', googleSearchUrl );
	}
);
