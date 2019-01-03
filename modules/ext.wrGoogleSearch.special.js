/**
 * Google Custom Search in [[Special:Search]]
 * (c) 2014-2018 Kol-Zchut Ltd. & Dror S. [FFS]
 * GPLv2 or later
 *
 */

/* global google */
( function ( mw, $ ) {
	'use strict';

	var gs;
	gs = mw.wrGoogleSearch = {
		googleSearchControl: null,

		init: function () {
			var cseID = mw.config.get( 'wgWRGoogleSearchCSEID' ),
				cseScript = '//cse.google.com/cse.js?cx=' + cseID;

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
			$( document ).ready( function () {
				$( '.form-search, .searchForm' ).submit( function ( event ) {
					var $searchInput = $( this ).find( '.mw-searchInput' ),
						query = $searchInput.val();
					gs.executeSearch( query );
					event.preventDefault();
				} );
			} );
		},

		googleOnLoadCallback: function () {
			if ( document.readyState === 'complete' ) {
				// Document is ready when CSE element is initialized
				gs.renderGoogleSearchElements();
			} else {
				// Document is not ready yet when CSE element is initialized
				google.setOnLoadCallback( function () {
					gs.renderGoogleSearchElements();
				}, true );
			}
		},

		renderGoogleSearchElements: function () {
			var prefilledQuery;

			$( '#googleSearchLoading' ).hide();

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

			// Do we have a pre-filled query? then log a search, because CSE will execute it
			// immmediately
			prefilledQuery = gs.getInitialQuery();
			if ( prefilledQuery ) {
				gs.logSearchPerformed( prefilledQuery );
			}
		},

		/* This is used only if we have another input on the page, to sync the two */
		getInitialQuery: function () {
			return gs.getHashParam( 'gsc.q' ) || mw.util.getParamValue( 'q' );
		},

		getHashParam: function ( key ) {
			var value = window.location.hash.split( key + '=' )[ 1 ];
			if ( typeof ( value ) === 'undefined' ) {
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
	// $( '#googleSearchLoading' ).prepend( $.createSpinner( 'googleSearchLoading' ) );

}( mediaWiki, jQuery ) );
