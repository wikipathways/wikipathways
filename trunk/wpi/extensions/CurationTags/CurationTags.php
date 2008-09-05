<?php

require_once(WPI_SCRIPT_PATH . "/MetaTag.php");

$wfCurationTagsPath = WPI_URL . "/extensions/CurationTags";

//Register AJAX functions
$wgAjaxExportList[] = "CurationTagsAjax::getTagNames";
$wgAjaxExportList[] = "CurationTagsAjax::getTagData";
$wgAjaxExportList[] = "CurationTagsAjax::saveTag";
$wgAjaxExportList[] = "CurationTagsAjax::removeTag";
$wgAjaxExportList[] = "CurationTagsAjax::getAvailableTags";

$wgExtensionFunctions[] = "wfCurationTags";

function wfCurationTags() {
    global $wgParser;
    $wgParser->setHook( "curationTags", "displayCurationTags" );
}

function displayCurationTags($input, $argv, &$parser) {
	global $wgOut, $wfCurationTagsPath;
	
	//Add CSS
	//Hack to add a css that's not in the skins directory
	global $wgStylePath;
	$oldStylePath = $wgStylePath;
	$wgStylePath = $wfCurationTagsPath;
	$wgOut->addStyle("CurationTags.css");
	$wgStylePath = $oldStylePath;
	
	$title = $parser->getTitle();
	$mayEdit = $title->userCan('edit') ? true : false;
	$revision = $parser->getRevisionId();
	
	$helpLink = Title::newFromText("CurationTags", NS_HELP)->getFullURL();
	
	//Add javascript
	$wgOut->addScript("<script type=\"{$wgJsMimeType}\" src=\"$wfCurationTagsPath/CurationTags.js\"></script>\n");
	$wgOut->addScript(
		"<script type=\"{$wgJsMimeType}\">" .
		"CurationTags.extensionPath=\"$wfCurationTagsPath\";" .
		"CurationTags.mayEdit=\"$mayEdit\";" .
		"CurationTags.pageRevision=\"$revision\";" .
		"CurationTags.helpLink=\"$helpLink\";" .
		"</script>\n"
	);

	$pageId = $parser->mTitle->getArticleID();
	$elementId = 'curationTagDiv';
	return "<div id='$elementId'></div><script type=\"{$wgJsMimeType}\">CurationTags.insertDiv('$elementId', '$pageId');</script>\n";
}

class CurationTagsAjax {
	public static $TAG_LIST_PAGE = "CurationTagsDefinition";
	
	/**
	 * Get the tag names for the given page.
	 * @return an XML snipped containing a list of tag names of the form:
	 * <TagNames><Name>tag1</Name><Name>tag2</Name>...<Name>tagn</Name></TagNames>
	 */
	public static function getTagNames($pageId) {
		$tags = MetaTag::getTagsForPage($pageId);
		$doc = new DOMDocument();
		$root = $doc->createElement("TagNames");
		$doc->appendChild($root);
		
		foreach($tags as $t) {
			$e = $doc->createElement("Name");
			$e->appendChild($doc->createTextNode($t->getName()));
			$root->appendChild($e);
		}
		
		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return $resp;
	}
	
	/**
	 * Get the tag information encoded in XML
	 */
	public static function getTag($name, $pageId) {
		$tag = new MetaTag($name, $pageId);
		
		$resp = new AjaxResponse(self::xmlFromTag($tag));
		$resp->setContentType("text/xml");
		return $resp;
	}
	
	/**
	 * Remove the given tag
	 * @return an XML snipped containing the tagname of the removed tag:
	 * <Name>tagname</Name>
	 */
	public static function removeTag($name, $pageId) {
		$tag = new MetaTag($name, $pageId);
		$tag->remove();
		
		$doc = new DOMDocument();
		$root = $doc->createElement("Name");
		$root->appendChild($doc->createTextNode($name));
		$doc->appendChild($root);
		
		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return $resp;
	}
	
	/**
	 * Create or update the tag, based on the provided tag information
	 * @return an XML snipped containing the tagname of the created tag:
	 * <Name>tagname</Name>
	 */
	public static function saveTag($name, $pageId, $text, $revision = false) {
		$tag = new MetaTag($name, $pageId);
		$tag->setText($text);
		if($revision && $revision != 'false') {
			$tag->setPageRevision($revision);
		}
		$tag->save();
		
		$doc = new DOMDocument();
		$root = $doc->createElement("Name");
		$root->appendChild($doc->createTextNode($name));
		$doc->appendChild($root);
		
		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return $resp;
	}
	
	/**
	 * Get the data for this tag.
	 * @return An xml encoded response, in the form:
	 * <Tag name='tagname' ...(other tag attributes)>
	 * 		<Html>the html code</html>
	 * 		<Text>the tag text</text>
	 * 	</Tag>
	 */
	public static function getTagData($name, $pageId) {
		//Create a template call and use the parser to
		//convert this to HTML
		$tag = new MetaTag($name, $pageId);
		$tmp = $name;
		$tmp .= "|tag_text={$tag->getText()}";
		$tmp .= "|user_add={$tag->getUserAdd()}";
		$tmp .= "|user_mod={$tag->getUserMod()}";
		$tmp .= "|time_add={$tag->getTimeAdd()}";
		$tmp .= "|time_mod={$tag->getTimeMod()}";
		
		if($tag->getPageRevision()) {
			$tmp .= "|tag_revision={$tag->getPageRevision()}";
		}
		
		$tmp = "{{Template:" . $tmp . "}}";

		$parser = new Parser();
		$out = $parser->parse($tmp, Title::newFromID($pageId), new ParserOptions());
		$html = $out->getText();
		
		$doc = new DOMDocument();
		$root = $doc->createElement("Tag");
		$doc->appendChild($root);
		$root->setAttribute('name', $tag->getName());
		$root->setAttribute('page_id', $tag->getPageId());
		$root->setAttribute('user_add', $tag->getUserAdd());
		$root->setAttribute('time_add', $tag->getTimeAdd());
		$root->setAttribute('user_mod', $tag->getUserMod());
		$root->setAttribute('time_mod', $tag->getTimeMod());
		if($tag->getPageRevision()) {
			$root->setAttribute('revision', $tag->getPageRevision());
		}
		$elm_text = $doc->createElement("Text");
		$elm_text->appendChild($doc->createTextNode($tag->getText()));
		$root->appendChild($elm_text);
		
		$elm_html = $doc->createElement("Html");
		$elm_html->appendChild($doc->createTextNode($html));
		$root->appendChild($elm_html);
		
		$resp = new AjaxResponse($doc->saveXML());
		$resp->setContentType("text/xml");
		return $resp;
	}
	
	/**
	 * Get the available curation tags.
	 * @return An xml document containing the list of tags on the 
	 * CurationTagsDefinition wiki page
	 */
	public static function getAvailableTags() {
		$title = Title::newFromText(self::$TAG_LIST_PAGE);
		$ref = Revision::newFromTitle($title);
		
		$resp = new AjaxResponse($ref->getText());
		$resp->setContentType("text/xml");
		return $resp;
	}
}
?>
