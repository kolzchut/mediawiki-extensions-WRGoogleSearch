<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'WikiRights/WRGoogleSearch' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['WRGoogleSearch'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['WRGoogleSearchAlias'] = __DIR__ . '/WRGoogleSearch.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the WRGoogleSearch extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the WRGoogleSearch extension requires MediaWiki 1.25+' );
}
