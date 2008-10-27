<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this special page, put the following line in LocalSettings.php:
require_once( "$IP/extensions/MarkDeprecated/MarkDeprecated.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['MarkDeprecated'] = dirname(__FILE__) . '/MarkDeprecated_body.php';
$wgSpecialPages['MarkDeprecated'] = 'MarkDeprecated';
$wgHooks['LoadAllMessages'][] = 'MarkDeprecated::loadMessages';

?>
