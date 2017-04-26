<?php
/** \file
* \brief Contains setup code for the WRGoogleSearch Extension.
*/

/**
 * WRGoogleSearch Extension for MediaWiki
 * Originally based on GoogleSiteSearch (v2.0) by Ryan Finnie
 *
 * Copyright (C) Dror S. & Kol-Zchut Ltd, Ryan Finnie
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

$wgExtensionCredits['specialpage'][] = [
	'path' => __FILE__,
	'name' => 'WRGoogleSearch',
	'author' => [
		'Dror S. [FFS] ([http://www.kolzchut.org.il Kol-Zchut])',
		'Ryan Finnie'
	],
	'url' => 'https://www.mediawiki.org/wiki/Extension:GoogleSiteSearch',
	'descriptionmsg' => 'wrgooglesearch-desc',
	'version' => '1.1.0',
];

# Default configuration globals
$wgWRGoogleSearchCSEID = '';
$wgWRGoogleSearchOnly = false;
$wgWRGoogleSearchExemptGroups = [];
$wgWRGoogleSearchEnableSitelinksSearch = true;


# Define special page
$wgAutoloadClasses['SpecialWRGoogleSearch'] = __DIR__ . '/SpecialWRGoogleSearch.php';
$wgAutoloadClasses['WRGoogleSearchHooks'] = __DIR__ . '/WRGoogleSearch.hooks.php';

# Define localization
$wgExtensionMessagesFiles['WRGoogleSearch'] = __DIR__ . '/WRGoogleSearch.i18n.php';
$wgExtensionMessagesFiles['WRGoogleSearchAlias'] = __DIR__  . '/WRGoogleSearch.alias.php';
$wgSpecialPages['WRGoogleSearch'] = 'SpecialWRGoogleSearch';
$wgSpecialPageGroups['WRGoogleSearch'] = 'redirects';


# Define hooks
$wgHooks['BeforePageDisplay'][] = 'WRGoogleSearchHooks::onBeforePageDisplay';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'WRGoogleSearchHooks::onResourceLoaderGetConfigVars';
$wgHooks['SkinAfterBottomScripts'][] = 'WRGoogleSearchHooks::onSkinAfterBottomScripts';

# Define ResourceLoader js/css modules
$modulesTemplate = [
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'WikiRights/WRGoogleSearch/modules',
];

$wgResourceModules['ext.wrGoogleSearch.general'] = $modulesTemplate + [
	'scripts' => 'ext.wrGoogleSearch.general.js',
	'position' => 'bottom',
	'dependencies' => 'mediawiki.util',
];

$wgResourceModules['ext.wrGoogleSearch.special'] = $modulesTemplate + [
	'scripts' => 'ext.wrGoogleSearch.special.js',
	'styles' => [
		'ext.wrGoogleSearch.special.less',
		'ext.wrGoogleSearch.results.css'
	],
	'position' => 'top',
];

unset( $modulesTemplate );


