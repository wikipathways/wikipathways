<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install DiffAppletPage, put the following line in LocalSettings.php:
require_once( "$IP/wpi/extensions/Relations/Relations.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Relations Viewer',
	'author' => 'Chetan Bansal',
	'url' => '',
	'description' => 'Visualize relationship between Pathways',
	'version' => '1.0.0',
);


$wgAutoloadClasses['Relations'] = dirname(__FILE__) . '/Relations_body.php';
$wgSpecialPages['Relations'] = 'Relations';
$wgExtensionMessagesFiles['Relations'] = dirname(__FILE__) . '/Relations.i18n.php';

?>
