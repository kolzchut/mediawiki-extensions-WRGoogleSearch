<?php
/**
 * Special page to search the site using Google Custom Search Engine (CSE)
 *
 * @author Dror Snir
 */


class SpecialWRGoogleSearch extends SpecialPage {


	function __construct() {
		parent::__construct( 'WRGoogleSearch' );
	}
	
	function execute( $par ) {
		// Strip underscores from title parameter; most of the time we'll want
		// text from here. But don't strip underscores from actual text params!
		$titleParam = str_replace( '_', ' ', $par );

		$request = $this->getRequest();

		// Fetch the search term
		$term = str_replace( "\n", " ", $request->getText( 'q', $titleParam ) );


		if ( $request->getVal( 'fulltext' ) ) {
			$this->showResults( $term );
		} else {
			$this->goResult( $term );  // Try to see if we got a direct hit
		}
	}


	/**
	 * If an exact title match can be found, jump straight ahead to it.
	 *
	 * @param $term String
	 */
	public function goResult( $term ) {
		# Try to go to page as entered.
		$t = Title::newFromText( $term );

		# If there's an exact match, jump right there.
		if ( !is_null( $t ) && $t->isKnown() ) {
			$this->getOutput()->redirect( $t->getFullURL() );
			return;
		}

		# No match, show search results
		$this->showResults( $term );
	}

	/**
	 * @param $term String
	 */
	public function showResults( $term ) {
		wfProfileIn( __METHOD__ );
		$this->setupPage( $term );

		$out = $this->getOutput();
		$out->addModules( 'ext.wrGoogleSearch.special' );
		$term = $out->getRequest()->getText( 'q' );
				
		$searchLoadingMsg = ( empty( $term ) ? '' : '<div id="googleSearchLoading">' . wfMessage( 'wrgooglesearch-loading' )->text() . '</div>' );


		/*
		 *
		$searchUrl = htmlspecialchars( SpecialPage::getTitleFor( 'WRGoogleSearch' )->getLocalURL() );
		$btnSearchMsg = wfMessage( 'searchbutton' )->text();
		$outhtml = <<<GSCSCRIPT
		<div id="googleSearchFormWrapper">
			<form id="googleSearchForm" action="{$searchUrl}">
				<input type="text" id="googleSearchFormInput" class="mw-searchInput" name="q" autocomplete="false" />
				<input type="submit" value="{$btnSearchMsg}" class="ui-state-default ui-corner-all" />
			</form>
		</div>
		<div id="googleSearchResults">{$searchLoadingMsg}</div>
GSCSCRIPT;
	*/
		$outhtml = "<div id=\"googleSearchResults\">{$searchLoadingMsg}</div>";

		$out->AddHTML( $outhtml );

	
		//$out->parserOptions()->setEditSection( false );
		
		wfProfileOut( __METHOD__ );
	}

	/**
	 * @param $term string
	 */
	protected function setupPage( $term ) {
		$this->setHeaders();
		$this->outputHeader();
		$out = $this->getOutput();
		$out->allowClickjacking();
		$out->addModuleStyles( 'mediawiki.special' );

		if( strval( $term ) !== ''  ) {
			$out->setPageTitle( $this->msg( 'searchresults' ) );

			$htmlTitleElement = $this->msg( 'pagetitle' )->rawParams(
				$this->msg( 'searchresults-title' )->rawParams( $term )->text()
			);
			$out->setHTMLTitle( $htmlTitleElement );
		}
		
	}

}
