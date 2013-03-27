<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	echo <<<EOT
		To install DiffAppletPage, put the following line in LocalSettings.php:
	require_once( "$IP/extensions/DiffAppletPage/DiffAppletPage.php" );
EOT;
	exit( 1 );
}

$wgAutoloadClasses['DiffAppletPage'] = dirname(__FILE__) . '/DiffAppletPage_body.php';
														 $wgSpecialPages['DiffAppletPage'] = 'DiffAppletPage';
$wgHooks['LoadAllMessages'][] = 'DiffAppletPage::loadMessages';
