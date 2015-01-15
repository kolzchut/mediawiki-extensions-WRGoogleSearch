<?php
/** \file
* \brief Contains setup code for the WRGoogleSearch Extension.
*/

/**
 * WRGoogleSearch Extension for MediaWiki
 * Originally based on GoogleSiteSearch (v2.0) by Ryan Finnie
 *
 * Copyright (C) Dror Snir, Ryan Finnie
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 1 );
}

$GLOBALS['wgExtensionCredits']['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'WRGoogleSearch',
	'author' => 'Dror Snir, Ryan Finnie',
	'url' => 'https://www.mediawiki.org/wiki/Extension:GoogleSiteSearch',
	'descriptionmsg' => 'wrgooglesearch-desc',
	'version' => '1.0.0',
);

# Default configuration globals
	$GLOBALS['wgWRGoogleSearchCSEID'] = '';
	$GLOBALS['wgWRGoogleSearchOnly'] = false;
	$GLOBALS['wgWRGoogleSearchExemptGroups'] = array();
	
# Define special page
$GLOBALS['wgAutoloadClasses']['SpecialWRGoogleSearch'] = __DIR__ . '/SpecialWRGoogleSearch.php';
$GLOBALS['wgAutoloadClasses']['WRGoogleSearch'] = __DIR__ . '/WRGoogleSearch.php';

# Define localization
$GLOBALS['wgExtensionMessagesFiles']['WRGoogleSearch'] = __DIR__ . '/WRGoogleSearch.i18n.php';
$GLOBALS['wgExtensionMessagesFiles']['WRGoogleSearchAlias'] = __DIR__  . '/WRGoogleSearch.alias.php';
	$GLOBALS['wgSpecialPages']['WRGoogleSearch'] = 'SpecialWRGoogleSearch';
	$GLOBALS['wgSpecialPageGroups']['WRGoogleSearch'] = 'redirects';

	
# Define hooks
$GLOBALS['wgHooks']['BeforePageDisplay'][] = 'WRGoogleSearch::onBeforePageDisplay';
$GLOBALS['wgHooks']['ResourceLoaderGetConfigVars'][] = 'WRGoogleSearch::onResourceLoaderGetConfigVars';


# Define ResourceLoader js/css modules
$modulesTemplate = array(
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'WikiRights/WRGoogleSearch/modules',
	'group' => 'ext.wrGoogleSearch',
);

$GLOBALS['wgResourceModules']['ext.wrGoogleSearch.general'] = $modulesTemplate + array(
	'scripts' => 'ext.wrGoogleSearch.general.js',
	'position' => 'bottom',
);

$GLOBALS['wgResourceModules']['ext.wrGoogleSearch.special'] = $modulesTemplate + array(
	'scripts' => 'ext.wrGoogleSearch.special.js',
	'styles' => array(
		'ext.wrGoogleSearch.special.less',
		'ext.wrGoogleSearch.results.less'
	),
	'position' => 'top',
);

class WRGoogleSearch {
	/** Add CSE ID to JS vars */
	function onResourceLoaderGetConfigVars( &$vars )
	{
		global $wgWRGoogleSearchCSEID;

		if (!empty($wgWRGoogleSearchCSEID)) {
			/* Add the ID, necessary for the RL module */
			$vars['wgWRGoogleSearchCSEID'] = $wgWRGoogleSearchCSEID;
		};

		return true;
	}

	/** Load the RL module */
	function onBeforePageDisplay( OutputPage &$out, Skin &$skin )
	{
		global $wgWRGoogleSearchOnly;
		$user = $out->getUser();

		if ( $wgWRGoogleSearchOnly && !WRGoogleSearch::isUserExempt( $user ) ) {
			$out->addModules( 'ext.wrGoogleSearch.general' );
		}
		return true;
	}


	static function isSpecialGoogleSearch( Title $title )
	{
		return $title->isSpecial('WRGoogleSearch');
	}

	static function isUserExempt( User &$user )
	{
		global $wgWRGoogleSearchExemptGroups;

		$userGroups = $user->getEffectiveGroups(true);
		$match = array_intersect( $userGroups, $wgWRGoogleSearchExemptGroups );
		return ( !empty( $match ) );
	}

}
