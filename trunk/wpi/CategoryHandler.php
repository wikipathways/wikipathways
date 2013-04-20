<?php

class CategoryHandler {
	private $pathway;

	function __construct($pathway) {
		$this->pathway = $pathway;
	}

	/**
	 * Get all categories the pathway belongs to
	 * (As stored in the MediaWiki table)
	 */
	public function getCategories() {
		//Get the categories from mediawiki
		$categories = $this->getMediaWikiCategories();
		return $categories;
	}

	/**
	 * Set the given categories for the pathway.
	 * $categories should be an array where the keys are the
	 * category names and the value is 1 when the pathway should
	 * be in the category, and 0 when the pathway should not be in
	 * the category.
	 */
	public function setCategories($categories) {
		$changed = false; //Don't save if nothing changed

		$doc = new DOMDocument();
		$gpml = $this->pathway->getGpml();
		$doc->loadXML($gpml);
		$root = $doc->documentElement;

		$catnodes = array();

		//Find all category nodes
		foreach($root->childNodes as $n) {
			if($n->nodeName == "Comment" &&
				$n->getAttribute('Source') == 'WikiPathways-category') {
				$catnodes[$n->nodeValue] = $n;
			}
		}

		//Remove categories that are set to 0
		foreach(array_keys($categories) as $cat) {
			$value = $categories[$cat];
			if(array_key_exists($cat, $catnodes)) {
				//Remove if value is 0
				if($value == 0) {
					$node = $catnodes[$cat];
					$root->removeChild($node);
					$changed = true;
				}
			} else { //Add if value is 1
				if($value == 1) {
					$node = $doc->createElement("Comment");
					$node->setAttribute("Source", "WikiPathways-category");
					$node->nodeValue = $cat;
					$root->insertBefore($node, $root->firstChild);
					$changed = true;
				}
			}
		}

		if($changed) {
			$gpml = $doc->saveXML();
			$this->pathway->updatePathway($gpml, "Modified categories");
		}
	}

	private function getMediaWikiCategories() {
		$categories = array();
		$title = $this->pathway->getTitleObject();
		$id = $title->getArticleID();

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( "categorylinks", array("cl_to"), array("cl_from" => $id));
		while( $row = $dbr->fetchRow( $res )) {
			if($row[0]) {
				array_push($categories, $row[0]);
			}
		}
		$dbr->freeResult($res);
		return $categories;

	}

	private function getGpmlCategories() {
		if($this->pathway->isDeleted()) {
			return array();
		} else {
			$cats = $this->pathway->getPathwayData()->getWikiCategories();
			array_push($cats, $this->pathway->species());
			return $cats;
		}
	}

}
