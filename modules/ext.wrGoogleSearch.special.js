/**
 * Google Custom Search in [[Special:Search]]
 * (c) 2014 Kol-Zchut Ltd. & Dror Snir <dror.snir@kolzchut.org.il>
 * GPLv2 or later
 *
 */

/* global mediaWiki */
( function ( mw, $ ) {
    "use strict";

	var googleSearchControl;

	mw.wrGoogleSearch = {

		init: function() {
			$.ajaxSetup({ cache: true });
			$.getScript('//www.google.com/jsapi', function() {
				/* global google */
				var lang = mw.config.get( 'wgContentLanguage' );
				var cseID = mw.config.get( 'wgWRGoogleSearchCSEID' );
				google.load('search', '1', { nocss: true, language : lang, callback : function() {	//style: google.loader.themes.V2_DEFAULT
					//$.removeSpinner( 'googleSearchLoading' );
					var customDrawOptions = new google.search.DrawOptions();
					customDrawOptions.enableSearchResultsOnly();
					googleSearchControl = new google.search.CustomSearchControl(cseID);

					googleSearchControl.setRefinementStyle(google.search.SearchControl.REFINEMENT_AS_TAB);
					googleSearchControl.draw('googleSearchResults', customDrawOptions);
					googleSearchControl.startHistoryManagement(function () {
					});
					googleSearchControl.setLinkTarget(google.search.Search.LINK_TARGET_SELF);

					googleSearchControl.setSearchStartingCallback(this, function (searchControl, searcher, query) {
						$('.mw-searchInput').val(query);
					});
					mw.wrGoogleSearch.getInitialQuery();

				}});
			});

			$(document).ready(function() {
				$('.form-search').submit(function (event) {
					var $searchInput = $(this).find('.mw-searchInput');
					var query = $searchInput.val();
					mw.wrGoogleSearch.executeSearch(query);
					//$searchInput.removeClass('googleBranded');
					event.preventDefault();
				});
			});
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
			googleSearchControl.execute( query );
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

