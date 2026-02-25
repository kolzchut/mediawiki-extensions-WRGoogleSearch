<?php

namespace MediaWiki\Extension\WRGoogleSearch;

use MediaWiki\Config\Config;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\SkinAfterBottomScriptsHook;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderGetConfigVarsHook;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserGroupManager;

class Hooks implements
	BeforePageDisplayHook,
	ResourceLoaderGetConfigVarsHook,
	SkinAfterBottomScriptsHook
{

	private Config $config;
	private UserGroupManager $userGroupManager;

	/**
	 * @param Config $config
	 * @param UserGroupManager $userGroupManager
	 */
	public function __construct( Config $config, UserGroupManager $userGroupManager ) {
		$this->config = $config;
		$this->userGroupManager = $userGroupManager;
	}

	/**
	 * Add CSE ID to JS vars
	 * @inheritDoc
	 */
	public function onResourceLoaderGetConfigVars( array &$vars, $skin, Config $config ): void {
		$cseId = $this->config->get( 'WRGoogleSearchCSEID' );
		if ( !empty( $cseId ) ) {
			$vars['wgWRGoogleSearchCSEID'] = $cseId;
		}
	}

	/**
	 * Add schema for site search
	 * @inheritDoc
	 */
	public function onSkinAfterBottomScripts( $skin, &$text ): void {
		if ( !$this->config->get( 'WRGoogleSearchEnableSitelinksSearch' )
			|| empty( $this->config->get( 'WRGoogleSearchCSEID' ) )
			|| !$skin->getTitle()->isMainPage()
		) {
			return;
		}

		$mainPageUrl = Title::newMainPage()->getFullURL();
		$searchUrl = SpecialPage::getTitleFor( 'WRGoogleSearch' )->getFullURL();

		$sitelinksSearch = <<<HTML
\n	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "WebSite",
		"url": "$mainPageUrl",
		"potentialAction": {
			"@type": "SearchAction",
			"target": "$searchUrl?q={search_term_string}",
			"query-input": "required name=search_term_string"
		}
	}
	</script>\n
HTML;

		$text .= $sitelinksSearch;
	}

	/**
	 * Load the RL module
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->config->get( 'WRGoogleSearchOnly' )
			&& !$this->isUserExempt( $out->getUser() )
		) {
			$out->addModules( 'ext.wrGoogleSearch.general' );
		}
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	private function isUserExempt( User $user ): bool {
		$exemptGroups = $this->config->get( 'WRGoogleSearchExemptGroups' );
		$userGroups = $this->userGroupManager->getUserEffectiveGroups( $user );
		return !empty( array_intersect( $userGroups, $exemptGroups ) );
	}
}
