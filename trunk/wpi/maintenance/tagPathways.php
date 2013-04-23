<?php
require_once('Maintenance.php');

/**
 * Script that applies a curation tag for each input pathway
 */
$pwPage = $_REQUEST['page'];
$tag = $_REQUEST['tag'];
$text =  $_REQUEST['text'];

if(!$pwPage) {
	print("Please specify the wiki page containing all pathways to be tagged.\n");
	exit();
}
if(!$tag) {
	print("Please specify the curation tag to add.\n");
	exit();
}

$pathways = Pathway::parsePathwayListPage($pwPage);

foreach($pathways as $pathway) {
	$revision = $pathway->getLatestRevision();
	$pageId = $pathway->getTitleObject()->getArticleId();

	echo("* Tagging {$pathway->getName()} ({$pathway->getSpecies()}); tag = '$tag', text = '$text'<BR>");

	if($doit) {
		CurationTag::saveTag($pageId, $tag, $text, $revision);
	}
}
