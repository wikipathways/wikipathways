<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install SpecialWishList, put the following line in LocalSettings.php:
require_once( "$IP/extensions/SpecialWishList/SpecialWishList.php" );
EOT;
        exit( 1 );
}

$wgAutoloadClasses['SpecialWishList'] = dirname(__FILE__) . '/SpecialWishList_body.php';
$wgSpecialPages['SpecialWishList'] = 'SpecialWishList';
$wgHooks['LoadAllMessages'][] = 'SpecialWishList::loadMessages';

?>
