<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install SearchPathways, put the following line in LocalSettings.php:
require_once( "$IP/extensions/SearchPathways/SearchPathways.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['SearchPathways'] = dirname(__FILE__) . '/SearchPathways_body.php';
$wgSpecialPages['SearchPathways'] = 'SearchPathways';
$wgHooks['LoadAllMessages'][] = 'SearchPathways::loadMessages';

$wfSearchPagePath = WPI_URL . "/extensions/SearchPathways";

require_once("SearchPathwaysAjax.php");
?>
