/**
 * Google Custom Search in [[Special:Search]]
 * (c) 2014-2018 Kol-Zchut Ltd. & Dror S. [FFS]
 * GPLv2 or later
 *
 */

/* global google */
'use strict';

const gs = mw.wrGoogleSearch = {
	googleSearchControl: null,

	init: function () {
		const cseID = mw.config.get( 'wgWRGoogleSearchCSEID' ),
			cseScript = 'https://cse.google.com/cse.js?cx=' + cseID;

		/* Do not automatically parse tags, run callback on load */
		// eslint-disable-next-line no-underscore-dangle
		window.__gcse = {
			parsetags: 'explicit',
			callback: gs.googleOnLoadCallback
		};
		/* eslint no-underscore-dangle: 1 */

		// We don't care when this will load, as it defines its own callback
		mw.loader.load( cseScript );

		// Bind the regular search form to execute the search using Google API
		Array.prototype.forEach.call(
			document.querySelectorAll( '.form-search, .searchForm' ),
			( form ) => {
				form.addEventListener( 'submit', ( event ) => {
					const query = form.querySelector( '.mw-searchInput, #searchInput' ).value;
					gs.executeSearch( query );
					event.preventDefault();
				} );
			}
		);
	},

	googleOnLoadCallback: function () {
		if ( document.readyState === 'complete' ) {
			// Document is ready when CSE element is initialized
			gs.renderGoogleSearchElements();
		} else {
			// Document is not ready yet when CSE element is initialized
			google.setOnLoadCallback( () => {
				gs.renderGoogleSearchElements();
			}, true );
		}
	},

	renderGoogleSearchElements: function () {
		const loadingEl = document.querySelector( '.google-search-loading' );
		if ( loadingEl ) {
			loadingEl.style.display = 'none';
		}

		gs.googleSearchControl = google.search.cse.element;
		gs.googleSearchControl.render( {
			div: 'googleSearchResults',
			tag: 'searchresults-only',
			gname: 'searchresults-block',
			attributes: {
				enableHistory: true,
				refinementStyle: 'tab',
				linkTarget: '_self',
				personalizedAds: false
			}
		} );

		// A query in the URL ('q' or the 'gsc.q' hash) is executed by CSE itself
		// immediately, so only log it. Otherwise run the term the page was given
		// some other way (Special:WRGoogleSearch/term), which CSE cannot see.
		const urlQuery = gs.getUrlQuery();
		if ( urlQuery ) {
			gs.logSearchPerformed( urlQuery );
		} else {
			const pageTerm = gs.getPageTerm();
			if ( pageTerm ) {
				gs.executeSearch( pageTerm );
			}
		}
	},

	/* The query CSE will pick up from the URL on its own */
	getUrlQuery: function () {
		return gs.getHashParam( 'gsc.q' ) || mw.util.getParamValue( 'q' );
	},

	/* The search term as resolved by the special page, from 'q' or the subpage */
	getPageTerm: function () {
		const resultsEl = document.getElementById( 'googleSearchResults' );
		return ( resultsEl && resultsEl.dataset.searchTerm ) || null;
	},

	getHashParam: function ( key ) {
		const value = window.location.hash.split( key + '=' )[ 1 ];
		if ( typeof value === 'undefined' ) {
			return null;
		}
		return value.split( '&' )[ 0 ];
	},

	executeSearch: function ( query ) {
		gs.logSearchPerformed( query );
		gs.googleSearchControl.getElement( 'searchresults-block' ).execute( query );
	},

	logSearchPerformed: function ( query ) {
		mw.log( 'Query performed: ' + query );
		// Also push to Google Tag Manager's dataLayer
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( {
			event: 'Site Search',
			'event data': {
				'search term': query
			}
		} );
	}

};

gs.init();
