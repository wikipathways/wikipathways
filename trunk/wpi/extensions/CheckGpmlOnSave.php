<?php
require_once('wpi/wpi.php');

//Register a hook that checks for valid GPML for all
//saves in the Pathway namespace
$wgHooks['ArticleSave'][] = 'wfCheckGpml';
//Register a hook that updates categories after saving
$wgHooks['ArticleSaveComplete'][] = 'wfUpdateAfterSave';

function wfCheckGpml(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
	global $wpiDisableValidation; //Flag that can be set to disable validation

	if(!$wpiDisableValidation && $article->getTitle()->getNamespace() == NS_PATHWAY) {
		if($error = Pathway::validateGpml($text)) {
			return "<h1>Invalid GPML</h1><p><code>$error</code>";
		}
	}
	return true;
}

function wfUpdateAfterSave(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
	if($article->getTitle()->getNamespace() == NS_PATHWAY) {
		$pw = Pathway::newFromTitle($article->getTitle());
		$pw->updateCategories();
	}
	return true;
}
