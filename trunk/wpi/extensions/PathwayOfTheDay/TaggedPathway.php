<?php

class TaggedPathway extends PathwayOfTheDay {
	private $tag;

	function __construct($id, $date, $tag) {
		$this->tag = $tag;
		parent::__construct($id, $date);
	}

	/**
	Select a random pathway from all pathways
	with the given tag
	**/
	protected function fetchRandomPathway() {
		wfDebug("Fetching random pathway...\n");
		$pages = MetaTag::getPagesForTag($this->tag);
		if(count($pages) == 0) {
			throw new Exception("There are no pathways tagged with '{$this->tag}'!");
		}
		$pathways = array();
		foreach($pages as $p) {
			$title = Title::newFromId($p);
			if($title->getNamespace() == NS_PATHWAY && !$title->isRedirect()) {
				$pathway = Pathway::newFromTitle($title);
				if(!$pathway->isDeleted()) {
					$pathways[] = $pathway;
				}
			}
		}
		return $pathways[rand(0, count($pathways) - 1)]->getTitleObject()->getDbKey();
	}
}

