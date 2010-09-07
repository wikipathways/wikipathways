<?php
# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
   exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'OntologyIndex',
	'author' => 'Chetan Bansal',
	'url' => '',
	'description' => 'Index Pathways by Ontology Tags',
	'version' => '1.0.0',
);

$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['ontologyindex'] = $dir . 'ontologyindex_body.php'; # Tell MediaWiki to load the extension body.
$wgExtensionMessagesFiles['ontologyindex'] = $dir . 'ontologyindex.i18n.php';
$wgExtensionAliasesFiles['ontologyindex'] = $dir . 'ontologyindex.alias.php';
$wgSpecialPages['ontologyindex'] = 'ontologyindex'; # Let MediaWiki know about your new special page.
?>