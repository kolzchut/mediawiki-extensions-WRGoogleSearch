<?php

namespace MediaWiki\Extension\WRGoogleSearch\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

/**
 * Special page to search the site using Google Custom Search Engine (CSE)
 */
class SpecialWRGoogleSearch extends SpecialPage {

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct( 'WRGoogleSearch' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ): void {
		// Strip underscores from title parameter; most of the time we'll want
		// text from here. But don't strip underscores from actual text params!
		$titleParam = str_replace( '_', ' ', (string)$subPage );

		$request = $this->getRequest();

		// Fetch the search term
		$searchQuery = $request->getText( 'q', $titleParam );
		$searchQuery = str_replace( "\n", " ", $searchQuery );

		if ( $request->getVal( 'fulltext' ) ) {
			$this->showResults( $searchQuery );
		} else {
			// Try to see if we got a direct hit
			$this->goResult( $searchQuery );
		}
	}

	/**
	 * If an exact title match can be found, jump straight ahead to it.
	 *
	 * @param string $term
	 */
	public function goResult( string $term ): void {
		# Try to go to page as entered.
		$t = Title::newFromText( $term );

		# If there's an exact match, jump right there. Like core's search,
		# interwiki titles go through Special:GoToInterwiki.
		if ( $t !== null && $t->isKnown() ) {
			$this->getOutput()->redirect( $t->getFullUrlForRedirect() );
			return;
		}

		# No match, show search results
		$this->showResults( $term );
	}

	/**
	 * @param string $term
	 * @return void
	 */
	public function showResults( string $term ): void {
		$this->setupPage( $term );

		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.wrGoogleSearch.special' );
		$out->addModules( 'ext.wrGoogleSearch.special' );

		// The term may come from the subpage as well as from 'q'
		$searchLoadingMsg = ( $term === '' ?
			''
			: '<div class="google-search-loading">' . $this->msg( 'wrgooglesearch-loading' )->escaped() . '</div>' );

		// Google's element reads the term only from 'q' or its own URL hash, so
		// hand the script the term this page resolved, to run a subpage search.
		$out->addHTML( Html::rawElement(
			'div',
			[
				'id' => 'googleSearchResults',
				'data-search-term' => $term === '' ? null : $term,
			],
			$searchLoadingMsg
		) );
	}

	/**
	 * @param string $term
	 * @return void
	 */
	protected function setupPage( string $term ): void {
		$this->setHeaders();
		$this->outputHeader();
		$outputPage = $this->getOutput();
		$outputPage->getMetadata()->setPreventClickjacking( false );
		$outputPage->addModuleStyles( 'mediawiki.special' );

		if ( $term !== '' ) {
			$outputPage->setPageTitleMsg( $this->msg( 'searchresults' ) );

			$htmlTitleElement = $this->msg( 'pagetitle' )->rawParams(
				$this->msg( 'searchresults-title' )->rawParams( $term )->text()
			);
			$outputPage->setHTMLTitle( $htmlTitleElement );
		}
	}

	/**
	 * Same as in SpecialSearch, which we don't inherit from because it's too different
	 *
	 * @inheritDoc
	 */
	protected function getGroupName(): string {
		return 'pages';
	}
}
