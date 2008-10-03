<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this special page, put the following line in LocalSettings.php:
require_once( "$IP/extensions/SpecialWishList/SpecialWishList.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['SpecialCurationTags'] = dirname(__FILE__) . '/SpecialCurationTags_body.php';
$wgSpecialPages['SpecialCurationTags'] = 'SpecialCurationTags';
$wgHooks['LoadAllMessages'][] = 'SpecialCurationTags::loadMessages';

?>
