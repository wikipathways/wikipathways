<?php
$wgExtensionFunctions[] = "wfAuthorInfo";

$wgAjaxExportList[] = "jsGetAuthors";

$wfAuthorInfoPath = WPI_URL . "/extensions/AuthorInfo";

function wfAuthorInfo() {
    global $wgParser;
    $wgParser->setHook( "AuthorInfo", "renderAuthorInfo" );
}

function renderAuthorInfo($input, $argv, &$parser) {
	global $wfAuthorInfoPath;
	$parser->disableCache();
	
	$limit = htmlentities($argv["limit"]);
	if(!$limit) $limit = 0;
	$bots = htmlentities($argv["bots"]);
	if(!$bots) $bots = false;

	//Add CSS
	//Hack to add a css that's not in the skins directory
	global $wgStylePath, $wgOut;
	$oldStylePath = $wgStylePath;
	$wgStylePath = $wfAuthorInfoPath;
	$wgOut->addStyle("AuthorInfo.css");
	$wgStylePath = $oldStylePath;
	
	$html = "<script type=\"text/javascript\" src=\"$wfAuthorInfoPath/AuthorInfo.js\"></script>\n";
	$id = $parser->getTitle()->getArticleId();
	$html .= "<div id='authorInfoContainer'></div><script type=\"text/javascript\">" .
		"AuthorInfo.init('authorInfoContainer', '$id', '$limit', '$bots');</script>";
	return $html;
}


/**
 * Called from javascript to get the author list.
 * @param $pageId The id of the page to get the authors for.
 * @param $limit Limit the number of authors to query. Leave empty to get all authors.
 * @param $includeBots Whether to include users marked as bot.
 * @return An xml document containing all authors for the given page
 */
function jsGetAuthors($pageId, $limit = '', $includeBots = false) {
	$title = Title::newFromId($pageId);
	if($includeBots === 'false') $includeBots = false;
	$authorList = new AuthorInfoList($title, $limit, $includeBots);
	$doc = $authorList->getXml();
	$resp = new AjaxResponse($doc->saveXML());
	$resp->setContentType("text/xml");
	return $resp;
}

class AuthorInfoList {
	private $title;
	private $limit;
	private $showBots;
	
	private $authors;
	
	public function __construct($title, $limit = '', $showBots = false) {
		$this->title = $title;
		if($limit) $this->limit = $limit + 1;
		$this->showBots = $showBots;
		$this->load();
	}
	
	private function load() {
		$dbr = wfGetDB( DB_SLAVE );
		$limit = '';
		if($this->limit) {
			$limit = "LIMIT 0, {$this->limit}";
		}
		
		//Get users for page
		$page_id = $this->title->getArticleId();
		$query = "SELECT DISTINCT(rev_user) FROM revision WHERE " .
			"rev_page = {$page_id} $limit";
			
		$res = $dbr->query($query);
		$this->authors = array();
		while($row = $dbr->fetchObject( $res )) {
			$user = User::newFromId($row->rev_user);
			if($user->isAnon()) continue; //Skip anonymous users
			if(!$user->isAllowed("bot") || $this->showBots) {
				$this->authors[] = new AuthorInfo($user, $this->title);
			}
		}
		
		//Sort the authors by editCount
		usort($this->authors, "AuthorInfo::compareByEdits");
		$dbr->freeResult( $res );
	}
	
	/**
	 * Render the author list.
	 * @return A HTML snipped containing the author list
	 */
	public function renderAuthorList() {
		$html = '';
		foreach($this->authors as $a) {
			$html .= $a->renderAuthor() . ", ";
		}
		return substr($html, 0, -2);
	}
	
	/**
	 * Get an XML document containing the author info
	 */
	public function getXml() {
		$doc = new DOMDocument();
		$root = $doc->createElement("AuthorList");
		$doc->appendChild($root);
		
		foreach($this->authors as $a) {
			$a->addXml($doc, $root);
		}
		return $doc;
	}
}

class AuthorInfo {
	private $title;
	private $user;
	private $editCount;
	
	public function __construct($user, $title) {
		$this->title = $title;
		$this->user = $user;
		$this->load();
	}
	
	public function getEditCount() {
		return $this->editCount;
	}
	
	private function load() {
		$dbr = wfGetDB( DB_SLAVE );
		$query = "SELECT COUNT(rev_user) AS editCount FROM revision " .
			"WHERE rev_user={$this->user->getId()} " .
			"AND rev_page={$this->title->getArticleId()}";
		$res = $dbr->query($query);
		$row = $dbr->fetchObject( $res );
		$this->editCount = $row->editCount;
		$dbr->freeResult( $res );
	}
	
	public function getDisplayName() {
		$name = $this->user->getRealName();
		
		//Filter out email addresses
		if(preg_match("/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD", $name)) {
			$name = ''; //use username instead
		}
		if(!$name) $name = $this->user->getName();
		return $name;
	}
	
	private function getAuthorLink() {
		global $wgScriptPath;
		$title = Title::newFromText('User:' . $this->user->getTitleKey());
		$href = $title->getFullUrl();
		return $href;
	}
	
	/**
	 * Creates the HTML code to display a single
	 * author
	 */
	public function renderAuthor() {
		$name = $this->getDisplayName();
		$href = $this->getAuthorLink();
		$link = "<A href=\"$href\" title=\"Number of edits: {$this->editCount}\">" .
			htmlspecialchars($name) . "</A>";
		return $link;
	}
	
	/**
	 * Add an XML node for this author to the
	 * given node.
	 */
	public function addXml($doc, $node) {
		$e = $doc->createElement("Author");
		$e->setAttribute("Name", $this->getDisplayName());
		$e->setAttribute("EditCount", $this->editCount);
		$e->setAttribute("Url", $this->getAuthorLink());
		$node->appendChild($e);
	}
	
	public static function compareByEdits($a1, $a2) {
		$c = $a2->getEditCount() - $a1->getEditCount();
		if($c == 0) { //If equal edits, compare by realname
			$c = strcasecmp($a1->getDisplayName(), $a2->getDisplayName());
		}
		return $c;
	}
}

?>
