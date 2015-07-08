<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this special page, put the following line in LocalSettings.php:
require_once( "$IP/extensions/TissueAnalyzer/TissueAnalyzer.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['TissueAnalyzer'] = dirname(__FILE__) . '/TissueAnalyzer_body.php';
$wgSpecialPages['TissueAnalyzer'] = 'TissueAnalyzer';
$wgHooks['LoadAllMessages'][] = 'TissueAnalyzer::loadMessages';

?>
