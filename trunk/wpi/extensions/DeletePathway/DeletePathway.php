<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
		echo <<<EOT
To install this special page, put the following line in LocalSettings.php:
require_once( "$IP/extensions/DeletePathway/DeletePathway.php" );
EOT;
		exit( 1 );
}

$wgAutoloadClasses['DeletePathway'] = dirname(__FILE__) . '/DeletePathway_body.php';
$wgSpecialPages['DeletePathway'] = 'DeletePathway';
$wgExtensionMessagesFiles['DeletePathways'] = dirname( __FILE__ ) . '/DeletePathway.i18n.php';
