<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this special page, put the following line in LocalSettings.php:
require_once( "$IP/extensions/PrivatePathways/ListPrivatePathways.php" );
EOT;
        exit( 1 );
}

$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['ListPrivatePathways'] = $dir . 'ListPrivatePathways_body.php';
$wgExtensionMessagesFiles['ListPrivatePathways'] = $dir . 'ListPrivatePathways.i18n.php';
$wgSpecialPages['ListPrivatePathways'] = 'ListPrivatePathways';

?>
