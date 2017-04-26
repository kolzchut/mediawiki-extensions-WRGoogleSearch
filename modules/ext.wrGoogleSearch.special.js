/**
 * Google Custom Search in [[Special:Search]]
 * (c) 2014-2017 Kol-Zchut Ltd. & Dror S. [FFS]
 * GPLv2 or later
 *
 */

/* global mediaWiki */
( function ( mw, $ ) {
    'use strict';

	mw.wrGoogleSearch = {
		googleSearchControl: null,

		init: function() {
			/* Do not automatically parse tags, run callback on load */
			window.__gcse = {
				parsetags: 'explicit',
				callback: this.googleOnLoadCallback
			};

			var cseID = mw.config.get('wgWRGoogleSearchCSEID');
			var cseScript = '//cse.google.com/cse.js?cx=' + cseID;
			mw.loader.load( cseScript );

			$(document).ready(function() {
				$('.form-search').submit(function (event) {
					var $searchInput = $(this).find('.mw-searchInput');
					var query = $searchInput.val();
					mw.wrGoogleSearch.executeSearch(query);
					event.preventDefault();
				});
			});
		},

		googleOnLoadCallback: function() {
			if( document.readyState === 'complete' ) {
				// Document is ready when CSE element is initialized
				mw.wrGoogleSearch.renderGoogleSearchElements();
			} else {
				// Document is not ready yet when CSE element is initialized
				google.setOnLoadCallback(function() {
					mw.wrGoogleSearch.renderGoogleSearchElements();
				}, true);
			}
		},

		renderGoogleSearchElements: function() {
			$( '#googleSearchLoading' ).hide();

			var googleSearchControl = this.googleSearchControl = google.search.cse.element;
			googleSearchControl.render({
				div: 'googleSearchResults',
				tag: 'searchresults-only',
				gname: 'searchresults-block',
				attributes: {
					enableHistory: true,
					refinementStyle: 'tab',
					linkTarget: '_self'
				}
			});

			/*
			 googleSearchControl.setSearchStartingCallback(this, function (searchControl, searcher, query) {
			 $('.mw-searchInput').val(query);
			 });
			 mw.wrGoogleSearch.getInitialQuery();

			 */
		},

		getHashParam: function (key) {
			var value = window.location.hash.split(key + '=')[1];
			if (typeof(value) === 'undefined') {
				return null;
			}
			return value.split('&')[0];
		},

		executeSearch: function( query ) {
			mw.log( 'Search form submitted. Query is: ' + query );
			this.googleSearchControl.getElement('searchresults-block').execute( query );
		},

		getInitialQuery: function() {
			var query = this.getHashParam( 'gsc.q' );
			if( query === null ) {	//HistoryManagement starts the search itself if gsc.q is present
				query = mw.util.getParamValue( 'q' );
			}
			if( query !== null && query !== '' ) {
				this.executeSearch( query );
				var $inputField = $( '.mw-searchInput' );
				$inputField.val( query );
				//$inputField.removeClass( 'googleBranded' );
			}
		}

	};

	mw.wrGoogleSearch.init();
		
	//$( '#googleSearchLoading' ).prepend( $.createSpinner( 'googleSearchLoading' ) );


}( mediaWiki, jQuery ) );

