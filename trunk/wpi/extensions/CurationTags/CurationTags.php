<?php

require_once(WPI_SCRIPT_PATH . "/MetaTag.php");
require_once("CurationTagsMailer.php");

$wfCurationTagsPath = WPI_URL . "/extensions/CurationTags";

//Register AJAX functions
$wgAjaxExportList[] = "CurationTagsAjax::getTagNames";
$wgAjaxExportList[] = "CurationTagsAjax::getTagData";
$wgAjaxExportList[] = "CurationTagsAjax::saveTag";
$wgAjaxExportList[] = "CurationTagsAjax::removeTag";
$wgAjaxExportList[] = "CurationTagsAjax::getAvailableTags";
$wgAjaxExportList[] = "CurationTagsAjax::getTagHistory";
$wgAjaxExportList[] = "CurationTagsAjax::getTags";

$wgExtensionFunctions[] = "wfCurationTags";

function wfCurationTags() {
	global $wgParser;
	$wgParser->setHook( "curationTags", "displayCurationTags" );

	global $wgMessageCache;
	$wgMessageCache->addMessages(
	array(
	'tagemail_subject' => '{{SITENAME}} page $PAGETITLE has been changed by $PAGEEDITOR',
	'tagemail_body' => 'Dear $WATCHINGUSERNAME,


$PAGEEDITOR $ACTIONd curation tag "$TAGNAME" on page $PAGETITLE. See $PAGETITLE_URL for the current version.

Contact the editor:
mail: $PAGEEDITOR_EMAIL
wiki: $PAGEEDITOR_WIKI

There will be no other notifications in case of further changes unless you visit this page.
You could also reset the notification flags for all your watched pages on your watchlist.

			 Your friendly {{SITENAME}} notification system

--
To change your watchlist settings, visit
{{fullurl:{{ns:special}}:Watchlist/edit}}

Feedback and further assistance:
{{fullurl:{{MediaWiki:Helppage}}}}'
	)
);
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
	if(!$revision) {
		$parser->mTitle->getLatestRevId();
	}
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

/**
 * Processes events after a curation tag has changed
 */
function curationTagChanged($tag) {
	global $wgEnotifUseJobQ;

	$hist = MetaTag::getHistoryForPage($tag->getPageId(), wfTimestamp(TS_MW));

	if(count($hist) > 0) {
		$taghist = $hist[0];
		$enotif = new TagChangeNotification($taghist);
		$enotif->notifyOnTagChange();
	}
}

/**
 * API for reading/writing Curation tags
 **/
class CurationTag {
	private static $TAG_LIST_PAGE = "CurationTagsDefinition";

	/**
	 * Tags with this prefix will be recognized
	 * as curation tags. Other tags will be ignored
	 * by this API.
	 */
	public static $TAG_PREFIX = "Curation:";
	private static $tagDefinition;

	/**
	 * Get the display name for the given tag name
	 */
	public static function getDisplayName($tagname) {
		$xpath = 'Tag[@name="' . $tagname . '"]/@displayName';
		$dn = self::getTagDefinition()->xpath($xpath);
		return $dn ? (string)$dn[0]['displayName'] : $tagname;
	}

	/**
	 * Get the description for the given tag name
	 */
	public static function getDescription($tagname) {
		$xpath = 'Tag[@name="' . $tagname . '"]/@description';
		$dn = self::getTagDefinition()->xpath($xpath);
		return $dn ? (string)$dn[0]['description'] : '';
	}

	/**
	 * Get the names of all available curation tags.
	 */
	public static function getTagNames() {
		$xpath = 'Tag/@name';
		$dn = self::getTagDefinition()->xpath($xpath);
		$names = array();
		foreach($dn as $e) $names[] = $e['name'];
		return $names;
	}

	/**
	 * Get all pages that have the given curation tag.
	 * @param $name The tag name
	 * @return An array with page ids
	 */
	public static function getPagesForTag($tagname) {
		return MetaTag::getPagesForTag($tagname);
	}

	/**
	 * Get the SimpleXML representation of the tag definition
	 * X		$dn = self::getTagDefinition()->xpath($xpath);
		return $dn ? (string)$dn[0]['displayName'] : $tagname;ML.
	 **/
	public static function getTagDefinition() {
		if(!self::$tagDefinition) {
			$title = Title::newFromText(self::$TAG_LIST_PAGE);
			$ref = Revision::newFromTitle($title);
			if($ref) {
				self::$tagDefinition = new SimpleXMLElement($ref->getText());
			} else {
				self::$tagDefinition = new SimpleXMLElement('<?xml version="1.0"?><TagDefinitions></TagDefinitions>');
			}
		}
		return self::$tagDefinition;
	}

	/**
	 * Create or update the tag, based on the provided tag information
	 */
	public static function saveTag($pageId, $name, $text, $revision = false) {
		if(!self::isCurationTag($name)) {
			self::errorNoCurationTag($name);
		}

		$tag = new MetaTag($name, $pageId);
		$tag->setText($text);
		if($revision && $revision != 'false') {
			$tag->setPageRevision($revision);
		}
		$tag->save();
		curationTagChanged($tag);
	}

	/**
	 * Remove the given curation tag for the given page.
	 */
	public static function removeTag($tagname, $pageId) {
		if(!self::isCurationTag($tagname)) {
			self::errorNoCurationTag($tagname);
		}

		$tag = new MetaTag($tagname, $pageId);
		$tag->remove();
		curationTagChanged($tag);
	}

	public static function getCurationTags($pageId) {
		$tags = MetaTag::getTagsForPage($pageId);
		$curTags = array();
		foreach($tags as $t) {
			if(self::isCurationTag($t->getName())) {
				$curTags[$t->getName()] = $t;
			}
		}
		return $curTags;
	}

	public static function getCurationTagsByName($tagname) {
		if(!self::isCurationTag($tagname)) {
			self::errorNoCurationTag($tagname);
		}
		return MetaTag::getTags($tagname);
	}

	/**
	 * Get tag history for the given page
	 */
	public static function getHistory($pageId, $fromTime = 0) {
		$allhist = MetaTag::getHistoryForPage($pageId, $fromTime);
		$hist = array();
		foreach($allhist as $h) {
			if(self::isCurationTag($h->getTagName())) {
				$hist[] = $h;
			}
		}
		return $hist;
	}

	/**
	 * Get the curation tag history for all pages
	 **/
	public static function getAllHistory($fromTime = 0) {
		$allhist = MetaTag::getAllHistory('', $fromTime);
		$hist = array();
		foreach($allhist as $h) {
			if(self::isCurationTag($h->getTagName())) {
				$hist[] = $h;
			}
		}
		return $hist;
	}

	/**
	 * Checks if the tagname is a curation tag
	 **/
	public static function isCurationTag($tagName) {
		$expr = "/^" . CurationTag::$TAG_PREFIX . "/";
		return preg_match($expr, $tagName);
	}

	private static function errorNoCurationTag($tagName) {
		throw new Exception("Tag '$tagName' is not a curation tag!");
	}
}

/**
 * Ajax API for reading/writing curation tags
 **/
class CurationTagsAjax {
	/**
	 * Get the tag names for the given page.
	 * @return an XML snipped containing a list of tag names of the form:
	 * <TagNames><Name>tag1</Name><Name>tag2</Name>...<Name>tagn</Name></TagNames>
	 */
	public static function getTagNames($pageId) {
		$tags = CurationTag::getCurationTags($pageId);
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
	 * Remove the given tag
	 * @return an XML snipped containing the tagname of the removed tag:
	 * <Name>tagname</Name>
	 */
	public static function removeTag($name, $pageId) {
		CurationTag::removeTag($name, $pageId);

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
		CurationTag::saveTag($pageId, $name, $text, $revision);

		$doc = new DOMDocument();
		$root = $doc->createElement("Name");
		$root->appendChild($doc->createTextNode($name));
		$doc->appendChild($root);

		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return $resp;
	}

	/**
	 * Get the tag history for the given page.
	 * @param $pageId The page id
	 * @param $fromTime An optional cutoff, if provided, only
	 * history entries after this time will be returned.
	 * @return An xml encoded response containing the history:
	 * <History fromTime='timestamp'>
	 * 		<HistoryRow tagName = 'tagname' ...(other history attributes)/>
	 *		...
	 * </History>
	 */
	public static function getTagHistory($pageId, $fromTime = '0') {
		global $wgLang, $wgUser;

		$hist = CurationTag::getHistory($pageId, $fromTime);

		$doc = new DOMDocument();
		$root = $doc->createElement("History");
		$doc->appendChild($root);

		foreach($hist as $h) {
			$elm = $doc->createElement("HistoryRow");
			$elm->setAttribute('tag_name', $h->getTagName());
			$elm->setAttribute('page_id', $h->getPageId());
			$elm->setAttribute('action', $h->getAction());
			$elm->setAttribute('user', $h->getUser());
			$elm->setAttribute('time', $h->getTime());

			$timeText = $wgLang->timeanddate($h->getTime());
			$elm->setAttribute('timeText', $timeText);

			$uid = $h->getUser();
			$nm = $uid;
			$u = User::newFromId($uid);
			if($u) {
				$nm = $u->getName();
			}
			$userText = $wgUser->getSkin()->userLink($uid, $nm);
			$elm->setAttribute('userText', $userText);

			$root->appendChild($elm);
		}

		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return $resp;
	}

	/**
	 * Get all curation tags (and their contents) at once.
	 */
	public static function getTags($pageId, $pageRev = 0) {
		$tags = CurationTag::getCurationTags($pageId);
		$doc = new DOMDocument();
		$root = $doc->createElement("Tags");
		$doc->appendChild($root);

		foreach($tags as $t) {
			$elm = self::getTagXml($doc, $t, $pageRev);
			$root->appendChild($elm);
		}

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
	public static function getTagData($name, $pageId, $pageRev = 0) {
		$tag = new MetaTag($name, $pageId);

		$doc = new DOMDocument();
		$elm = self::getTagXML($doc, $tag, $pageRev);
		$doc->appendChild($elm);

		$resp = new AjaxResponse($doc->saveXML());
		$resp->setContentType("text/xml");
		return $resp;
	}

	public static function getTagXML($doc, $tag, $pageRev = 0) {
		//Create a template call and use the parser to
		//convert this to HTML
		$userAdd = User::newFromId($tag->getUserAdd());
		$userMod = User::newFromId($tag->getUserMod());

		$name = $tag->getName();
		$pageId = $tag->getPageId();

		$tmp = $name;
		$tmp .= "|tag_name={$tag->getName()}";
		$tmp .= "|tag_text={$tag->getText()}";
		$tmp .= "|user_add={$tag->getUserAdd()}";
		$tmp .= "|user_add_name={$userAdd->getName()}";
		$tmp .= "|user_mod_name={$userMod->getName()}";
		$tmp .= "|user_add_realname={$userAdd->getRealName()}";
		$tmp .= "|user_mod_realname={$userMod->getRealName()}";
		$tmp .= "|user_mod={$tag->getUserMod()}";
		$tmp .= "|time_add={$tag->getTimeAdd()}";
		$tmp .= "|time_mod={$tag->getTimeMod()}";
		$tmp .= "|page_revision={$pageRev}";

		if($tag->getPageRevision()) {
			$tmp .= "|tag_revision={$tag->getPageRevision()}";
		}

		$tmp = "{{Template:" . $tmp . "}}";

		$parser = new Parser();
		$out = $parser->parse($tmp, Title::newFromID($pageId), new ParserOptions());
		$html = $out->getText();

		$elm = $doc->createElement("Tag");
		$elm->setAttribute('name', $tag->getName());
		$elm->setAttribute('page_id', $tag->getPageId());
		$elm->setAttribute('user_add', $tag->getUserAdd());
		$elm->setAttribute('time_add', $tag->getTimeAdd());
		$elm->setAttribute('user_mod', $tag->getUserMod());
		$elm->setAttribute('time_mod', $tag->getTimeMod());
		if($tag->getPageRevision()) {
			$elm->setAttribute('revision', $tag->getPageRevision());
		}
		$elm_text = $doc->createElement("Text");
		$elm_text->appendChild($doc->createTextNode($tag->getText()));
		$elm->appendChild($elm_text);

		$elm_html = $doc->createElement("Html");
		$elm_html->appendChild($doc->createTextNode($html));
		$elm->appendChild($elm_html);

		return $elm;
	}

	/**
	 * Get the available curation tags.
	 * @return An xml document containing the list of tags on the
	 * CurationTagsDefinition wiki page
	 */
	public static function getAvailableTags() {
		$td = CurationTag::getTagDefinition();
		$resp = new AjaxResponse($td->asXML());
		$resp->setContentType("text/xml");
		return $resp;
	}
}
