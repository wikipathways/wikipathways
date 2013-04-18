<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
		echo <<<EOT
To install BrowsePathways, put the following line in LocalSettings.php:
require_once( "$IP/extensions/BrowsePathwayPage2/BrowsePathwayPage.php" );
EOT;
		exit( 1 );
}

$wgAutoloadClasses['BrowsePathways'] = dirname(__FILE__) . '/BrowsePathways_body.php';
$wgAutoloadClasses['LegacyBrowsePathways'] = dirname(__FILE__) . '/BrowsePathways_body.php';
$wgSpecialPages['BrowsePathwaysPage'] = 'LegacyBrowsePathways';
$wgSpecialPages['BrowsePathways'] = 'BrowsePathways';
$wgHooks['LoadAllMessages'][] = 'BrowsePathways::loadMessages';

?>
