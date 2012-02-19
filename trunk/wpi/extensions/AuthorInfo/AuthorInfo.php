<?php
//Register the extension
$wgExtensionCredits['validextensionclass'][] = array(
	'path' => __FILE__,
	'name' => 'AuthorInfo',
	'author' =>'Thomas Kelder', 
	'url' => 'https://www.mediawiki.org/wiki/Extension:AuthorInfo', 
	'description' => 'Displays a list of authors that contributed to an article, sorted by number of edits.'
);

//Create a module to load the javascript and css used by this extension
$wgResourceModules['ext.AuthorInfo'] = array(
	'scripts' => array( 'wpi/extensions/AuthorInfo/AuthorInfo.js' ),
	'styles' => array( 'wpi/extensions/AuthorInfo/AuthorInfo.css' )
);

//Load the classes used by this extension
$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['AuthorInfoList'] = $dir . 'AuthorInfo.body.php';
$wgAutoloadClasses['AuthorInfo'] = $dir . 'AuthorInfo.body.php';

//Register extension hooks
$wgHooks['ParserFirstCallInit'][] = 'AuthorInfoExtension::init';
//Register ajax functions
$wgAjaxExportList[] = "AuthorInfoExtension::jsGetAuthors";


class AuthorInfoExtension {
	static function init(Parser &$parser) {
		$parser->setHook( 'authorInfo', 'AuthorInfoExtension::render' );
        return true;
	}
	
	static function render($input, array $args, Parser $parser, PPFrame $frame) {
		global $wgOut;
		$wgOut->addModules('ext.AuthorInfo');
		 
		$parser->disableCache();

		$limit = htmlentities($args["limit"]);
		if(!$limit) $limit = 0;
		$bots = htmlentities($args["bots"]);
		if(!$bots) $bots = false;

		$id = $parser->getTitle()->getArticleId();
		$html = "<div id='authorInfoContainer'></div><script type=\"text/javascript\">" .
		"$(document).ready(function() { AuthorInfo.init('authorInfoContainer', '$id', '$limit', '$bots') });</script>";
		return $html;
	}
	
	/**
	 * Called from javascript to get the author list.
	 * @param $pageId The id of the page to get the authors for.
	 * @param $limit Limit the number of authors to query. Leave empty to get all authors.
	 * @param $includeBots Whether to include users marked as bot.
	 * @return An xml document containing all authors for the given page
	 */
	static function jsGetAuthors($pageId, $limit = '', $includeBots = false) {
		$title = Title::newFromId($pageId);
		if($includeBots === 'false') $includeBots = false;
		$authorList = new AuthorInfoList($title, $limit, $includeBots);
		$doc = $authorList->getXml();
		$resp = new AjaxResponse($doc->saveXML());
		$resp->setContentType("text/xml");
		return $resp;
	}

}

?>
