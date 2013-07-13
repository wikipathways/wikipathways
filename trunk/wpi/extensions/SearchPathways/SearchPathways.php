<?php

$wgAutoloadClasses['SearchPathways'] = dirname(__FILE__) . '/SearchPathways_body.php';
$wgAutoloadClasses['SearchPathwaysAjax'] = dirname(__FILE__) . '/SearchPathwaysAjax.php';
$wgSpecialPages['SearchPathways'] = 'SearchPathways';
$wgExtensionMessagesFiles['SearchPathways'] = dirname( __FILE__ ) . '/SearchPathways.i18n.php';
$wfSearchPagePath = WPI_URL . "/extensions/SearchPathways";
$wgAjaxExportList[] = "SearchPathwaysAjax::doSearch";
$wgAjaxExportList[] = "SearchPathwaysAjax::getResults";
