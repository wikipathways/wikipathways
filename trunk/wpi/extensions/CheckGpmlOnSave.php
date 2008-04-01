<?php
require_once('wpi/wpi.php');

//Register a hook that checks for valid GPML for all
//saves in the Pathway namespace
$wgHooks['ArticleSave'][] = 'wfCheckGpml';

function wfCheckGpml(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
	if($article->getTitle()->getNamespace() == NS_PATHWAY) {
		if($error = Pathway::validateGpml($text)) {
			return "<h1>Invalid GPML</h1><p><code>$error</code>";
		}
	}
	return true;
}
