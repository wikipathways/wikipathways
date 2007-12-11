<?php

$wgHooks['ParserBeforeStrip'][] = array('renderPathwayPage'); 	
$wgHooks['BeforePageDisplay'][] = array('addPreloaderScript');
function renderPathwayPage(&$parser, &$text, &$strip_state) {
	$title = $parser->getTitle();	
	if(	$title->getNamespace() == NS_PATHWAY &&
		preg_match("/^\s*\<\?xml/", $text)) 
	{
		$parser->disableCache();
		$pathway = Pathway::newFromTitle($title);
		$pathway->updateCache(FILETYPE_IMG); //In case the image page is removed
		$page = new PathwayPage($pathway);
		$text = $page->getContent();
	}
	return true;
}

function addPreloaderScript($out) {
	global $wgTitle, $wgUser, $wgScriptPath;

	if($wgTitle->getNamespace() == NS_PATHWAY && $wgUser->isLoggedIn()) {
		$base = $wgScriptPath . "/wpi/applet/";
		$class = "org.pathvisio.wikipathways.Preloader.class";
		
		$out->addHTML("<applet code='$class' codebase='$base'
			width='1' height='1' name='preloader'></applet>");
	}
	return true;
}

class PathwayPage {
	private $pathway;
	private $data;
	
	function __construct($pathway) {
		$this->pathway = $pathway;
		$this->data = $pathway->getPathwayData();
	}

	function getContent() {	
		$text = <<<TEXT
{{Template:PathwayPage:Top}}

{$this->descriptionText()}
{$this->bibliographyText()}
{$this->categoryText()}
{{Template:PathwayPage:Bottom}}
TEXT;
		return $text;
	}
	
	function descriptionText() {
		//Get WikiPathways description
		$button = $this->editButton('javascript:;', 'Edit description', 'descEdit');
		$description = $this->data->getWikiDescription();
		if(!$description) {
			$description = "<I>No description</I>";
		}
		$description = "== Description ==\n<div id='descr'>" .
			"<div style='float:right'>$button</div>\n" . $description . "</div>\n";
		$title = $this->pathway->getTitleObject()->getText();
		$description .= "{{#editApplet:descEdit|descr|0|$title|description|0|250px}}\n";
		
		//Get additional comments
		foreach($this->data->getGpml()->Comment as $comment) {
			if(	$comment['Source'] == COMMENT_WP_DESCRIPTION ||
				$comment['Source'] == COMMENT_WP_CATEGORY)
			{
				continue; //Skip description and category comments
			}
			$text = (string)$comment;
			$text = html_entity_decode($text);
			$text = nl2br($text);
			$text = PathwayPage::formatPubMed($text);
			if(!$text) continue;
			$comments .= "; " . $comment['Source'] . " : " . $text . "\n";
		}
		if($comments) {
			$description .= "\n=== Comments ===\n<div id='comments'>\n$comments<div>";
		}
		return $description;
	}
	
	function bibliographyText() {
		global $wgUser;
		
		$gpml = $this->data->getGpml();
		
		//Format literature references
		$pubXRefs = $this->data->getPublicationXRefs();
		foreach(array_keys($pubXRefs) as $id) {
			$xref = $pubXRefs[$id];

			$authors = $title = $source = $year = '';

			if((string)$xref->ID && (strtolower($xref->DB) == 'pubmed')) {
				//We have a pubmed id, use biblio extension
				$out .= "#$id $xref pmid=" . $xref->ID . "\n";
			} else {
				//Format the citation ourselves
				//Authors, title, source, year
				foreach($xref->AUTHORS as $a) {
					$authors .= "$a, ";
				}

				if($authors) $authors = substr($authors, 0, -2) . "; ";
				if($xref->TITLE) $title = $xref->TITLE . "; ";
				if($xref->SOURCE) $source = $xref->SOURCE;
				if($xref->YEAR) $year = ", " . $xref->YEAR;
				$out .= "#$id $authors$title$source$year\n";
			}
		}
		if($out) {
			$out = "<biblio>$out</biblio>\n";
		} else {
			$out = "''No bibliography''\n";
		}
		//No edit button for now, show help on how to add bibliography instead
		//$button = $this->editButton('javascript:;', 'Edit bibliography', 'bibEdit');
		#&$parser, $idClick = 'direct', $idReplace = 'pwThumb', $new = '', $pwTitle = '', $type = 'editor'
		if($wgUser->isLoggedIn()) {
			$help = "{{Template:Help:LiteratureReferences}}";
		}
		return "== Bibliography ==\n$out\n$help";
			//"<div id='bibliography'><div style='float:right'>$button</div>\n" .
			//"$out</div>\n{{#editApplet:bibEdit|bibliography|0||bibliography|0|250px}}";		
	}
	
	function categoryText() {
		$categories = $this->pathway->getCategoryHandler()->getCategories();
	
		foreach($categories as $c) {
			$cat = Title::newFromText($c, NS_CATEGORY);
			$name = $cat->getText();
			$link = $cat->getFullText();
			$catlist .= "* [[:$link|$name]]\n";
		}
		$button = $this->editButton('javascript:;', 'Edit categories', 'catEdit');
		$title = $this->pathway->getTitleObject()->getText();
		return "== Categories ==\n<div id='categories'>\n" .
			"<div style='float:right'>$button</div>\n" . 
			"$catlist</div>\n{{#editApplet:catEdit|categories|0|$title|categories|0|250px}}";
	}
	
	function editButton($href, $title, $id = '') {
		global $wgUser;
		# Check permissions
		if( $wgUser->isLoggedIn()) {
			$label = 'edit';
		} else {
			/*
			$pathwayURL = $this->pathway->getTitleObject()->getFullText();
			$href = SITE_URL . "/index.php?title=Special:Userlogin&returnto=$pathwayURL";
			$label = 'log in';
			$title = 'Log in to edit';
			*/
			return "";
		}
		return "<fancyButton title='$title' href='$href' id='$id'>$label</fancyButton>";
	}
	
	static function getDownloadURL($pathway, $type) {
		return WPI_SCRIPT_URL . "?action=downloadFile&type=$type&pwTitle={$pathway->getTitleObject()->getFullText()}";
	}
	
	static function editDropDown($pathway) {
		$download = array(
			'PathVisio (.gpml)' => self::getDownloadURL($pathway, 'gpml'),
			'GenMAPP (.mapp)' => self::getDownloadURL($pathway, 'mapp'),
			'Eu.Gene (.pwf)' => self::getDownloadURL($pathway, 'pwf'),
			'Png image (.png)' => self::getDownloadURL($pathway, 'png'),
			'Acrobat (.pdf)' => self::getDownloadURL($pathway, 'pdf'),
		);
		$pwTitle = $pathway->getTitleObject()->getFullText();
		$cytoscape = "<li><a href='" . WPI_SCRIPT_URL . "?launchCytoscape&pwTitle=$pwTitle'>Open in Cytoscape</a></li>";
		
		foreach(array_keys($download) as $key) {
			$downloadlist .= "<li><a href='{$download[$key]}'>$key</a></li>";
		}
		
		$drop = '<div style="float:right;">' . $drop . '</div>';
		$drop = "<a href='\#' class='button'><span>$drop</span></a>";
		$dropdown = <<<DROPDOWN
<ul id="nav">		
<li class="top"><a href="#nogo2" class="button buttondown"><span>Download</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]--><ul class="sub">
			<li><a class="fly" href="#nogo3">Download for<!--[if gte IE 7]><!--></a><!--<![endif]-->
					<!--[if lte IE 6]><table><tr><td><![endif]--><ul>
						$downloadlist
					</ul><!--[if lte IE 6]></td></tr></table></a><![endif]-->
			</li>
			$cytoscape
		</ul><!--[if lte IE 6]></td></tr></table></a><![endif]-->
</li>
</ul>
DROPDOWN;
		return $dropdown;
	}
	static function formatPubMed($text) {
	$link = "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=pubmed&cmd=Retrieve&dopt=AbstractPlus&list_uids=";
	if(preg_match_all("/PMID: ([0-9]+)/", $text, $ids)) {
		foreach($ids[1] as $id) {
			$text = str_replace($id, "[$link$id $id]", $text);
		}
	}
	return $text;
}
}
?>
