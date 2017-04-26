<?php

class WRGoogleSearchHooks {
	/** Add CSE ID to JS vars */
	static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgWRGoogleSearchCSEID;

		if ( !empty( $wgWRGoogleSearchCSEID ) ) {
			/* Add the ID, necessary for the RL module */
			$vars['wgWRGoogleSearchCSEID'] = $wgWRGoogleSearchCSEID;
		};

		return true;
	}

	public static function onSkinAfterBottomScripts( Skin $skin, &$text ) {
		global $wgWRGoogleSearchEnableSitelinksSearch, $wgWRGoogleSearchCSEID;
		if ( !$wgWRGoogleSearchEnableSitelinksSearch
		     || empty( $wgWRGoogleSearchCSEID )
		     || !$skin->getTitle()->isMainPage()
		) {
			return true;
		}

		$mainPageUrl = Title::newFromText( wfMessage( 'mainpage' )->plain() )->getFullURL();
		$searchUrl = SpecialPage::getTitleFor( 'WRGoogleSearch' )->getFullURL();

		$sitelinksSearch = <<<HTML
\n	<script type="application/ld+json">
		{
			"@context": "http://schema.org",
	  		"@type": "WebSite",
			"url": "{$mainPageUrl}",
			"potentialAction": {
				"@type": "SearchAction",
			    "target": "{$searchUrl}?q={search_term_string}",
			    "query-input": "required name=search_term_string"
	  		}
		}
	</script>\n
HTML;

		$text .= $sitelinksSearch;

		return true;
	}

	/** Load the RL module */
	static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		global $wgWRGoogleSearchOnly;
		$user = $out->getUser();

		if ( $wgWRGoogleSearchOnly && !self::isUserExempt( $user ) ) {
			$out->addModules( 'ext.wrGoogleSearch.general' );
		}
		return true;
	}

	static function isSpecialGoogleSearch( Title $title ) {
		return $title->isSpecial( 'WRGoogleSearch' );
	}

	static function isUserExempt( User &$user ) {
		global $wgWRGoogleSearchExemptGroups;

		$userGroups = $user->getEffectiveGroups( true );
		$match = array_intersect( $userGroups, $wgWRGoogleSearchExemptGroups );
		return ( !empty( $match ) );
	}

}
