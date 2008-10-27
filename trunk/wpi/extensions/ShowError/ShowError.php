<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this special page, put the following line in LocalSettings.php:
require_once( "$IP/extensions/ShowError/ShowError.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['ShowError'] = dirname(__FILE__) . '/ShowError_body.php';
$wgSpecialPages['ShowError'] = 'ShowError';
$wgHooks['LoadAllMessages'][] = 'ShowError::loadMessages';

?>
