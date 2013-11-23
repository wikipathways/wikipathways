<?php

$wfPageEditorPath = WPI_URL . "/extensions/PageEditor";

//Register AJAX functions
$wgAjaxExportList[] = "PageEditor::save";

$wgExtensionFunctions[] = "wfPageEditor";

function wfPageEditor() {
	global $wgParser;
	$wgParser->setHook( "pageEditor", "displayPageEditor" );
}

function displayPageEditor($input, $argv, $parser) {
	global $wgOut, $wfPageEditorPath, $wgUser;

	//Check user rights
	if( !$wgUser->isLoggedIn() || wfReadOnly()) {
		return "<div style=\"height: 0px;\"></div>"; //Don't return any applet code
	}

	//Add CSS
	//Hack to add a css that's not in the skins directory
	global $wgStylePath, $wgJsMimeType;
	$oldStylePath = $wgStylePath;
	$wgStylePath = $wfPageEditorPath;
	$wgOut->addStyle("PageEditor.css");
	$wgStylePath = $oldStylePath;

	$title = $parser->getTitle();
	$mayEdit = $title->userCan('edit') ? true : false;
	$revision = $parser->getRevisionId();
	if(!$revision) {
		$parser->mTitle->getLatestRevId();
	}

	//Add javascript
	$targetId = $argv['id'];
	$type = $argv['type'];
	$content = json_encode($input);
	$pwId = $title->getText();
	$userCanEdit = $title->userCan('edit') ? "true" : "false";

	$wgOut->addScript("<script type=\"{$wgJsMimeType}\" src=\"{$wfPageEditorPath}/PageEditor.js\"></script>\n");

	$script = "<div style=\"height: 0px;\"><script type='{$wgJsMimeType}'>var p = new PageEditor('$targetId', '$type', $content, '$pwId', $userCanEdit);</script></div>";

	return $script;
}

class PageEditor {
	public static function save($pwId, $type, $content) {
		try {
			$pathway = new Pathway($pwId);

			switch($type) {
				case "description":
					$doc = new DOMDocument();
					$gpml = $pathway->getGpml();
					$doc->loadXML($gpml);
					//Save description
					$description = false;
					$root = $doc->documentElement;
					foreach($root->childNodes as $n) {
						if($n->nodeName == "Comment" &&
							$n->getAttribute('Source') == COMMENT_WP_DESCRIPTION) {
							$description = $n;
							break;
						}
					}

					if(!$description) {
						$description = $doc->createElement("Comment");
						$description->setAttribute("Source", COMMENT_WP_DESCRIPTION);
						$root->insertBefore($description, $root->firstChild);
					}
					$description->nodeValue = $content;

					//Save the new GPML
					$gpml = $doc->saveXML();
					$pathway->updatePathway($gpml, "Modified " . $type);
					break;
				case "title":
					$doc = new DOMDocument();
					$gpml = $pathway->getGpml();
					$doc->loadXML($gpml);
					$doc->documentElement->setAttribute("Name", $content);
					$gpml = $doc->saveXML();
					$pathway->updatePathway($gpml, "Modified " . $type);
					break;
			}
		} catch(Exception $e) {
			$r = new AjaxResponse($e);
			$r->setResponseCode(500);
			wfHttpError( 500, $e->getMessage() );
			return $r;
		}
		return new AjaxResponse("");
	}

	//From http://stackoverflow.com/questions/3361036/php-simplexml-insert-node-at-certain-position
	static function simplexml_insert_after(SimpleXMLElement $sxe, SimpleXMLElement $insert, SimpleXMLElement $target) {
		$target_dom = dom_import_simplexml($target);
		$insert_dom = $target_dom->ownerDocument->importNode(dom_import_simplexml($insert), true);
		if ($target_dom->nextSibling) {
			return $target_dom->parentNode->insertBefore($insert_dom, $target_dom->nextSibling);
		} else {
			return $target_dom->parentNode->appendChild($insert_dom);
		}
	}
}
?>
