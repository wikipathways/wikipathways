<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install RecentPathwayChanges, put the following line in LocalSettings.php:
require_once( "$IP/extensions/RecentPathwayChanges/RecentPathwayChanges.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['RecentPathwayChanges'] = dirname(__FILE__) . '/RecentPathwayChanges_body.php';
$wgSpecialPages['RecentPathwayChanges'] = 'RecentPathwayChanges';
$wgHooks['LoadAllMessages'][] = 'RecentPathwayChanges::loadMessages';

?>
