<?php

$wgHooks['ParserBeforeStrip'][] = array('renderPathwayPage'); 	
$wgHooks['BeforePageDisplay'][] = array('addPreloaderScript');
function renderPathwayPage(&$parser, &$text, &$strip_state) {
	global $wgUser;
	
	$title = $parser->getTitle();	
	if(	$title->getNamespace() == NS_PATHWAY &&
		preg_match("/^\s*\<\?xml/", $text)) 
	{
		$parser->disableCache();
		
		$oldId = $_REQUEST['oldid'];
		
		try {
			$pathway = Pathway::newFromTitle($title);
			if($oldId) {
				$pathway->setActiveRevision($oldId);
			}
			$pathway->updateCache(FILETYPE_IMG); //In case the image page is removed
			$page = new PathwayPage($pathway);
			$text = $page->getContent();
		} catch(Exception $e) { //Return error message on any exception
			$text = <<<ERROR
= Error rendering pathway page =
This revision of the pathway probably contains invalid GPML code. If this happens to the most recent revision, try reverting
the pathway using the pathway history displayed below or contact the site administrators (see [[WikiPathways:About]]) to resolve this problem.
=== Pathway history ===
<pathwayHistory></pathwayHistory>
=== Error details ===
<pre>
{$e}
</pre>
ERROR;
			
		}
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
{$this->curationTags()}
{$this->descriptionText()}
{$this->bibliographyText()}
{$this->categoryText()}
{{Template:PathwayPage:Bottom}}
TEXT;
		return $text;
	}
	
	function curationTags() {
		$button = $this->editButton(
			'javascript:CurationTags.editTags();', 
			'Edit curation tags', 
			'tagEdit'
		);
		$description = $this->data->getWikiDescription();
		if(!$description) {
			$description = "<I>No description</I>";
		}
		$tags = "== Curation Tags ==\n" .
			"<CurationTags></CurationTags>";
		return $tags;
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
		$id = $this->pathway->getIdentifier();
		$description .= "{{#editApplet:descEdit|descr|0|$id|description|0|250px}}\n";
		
		//Get additional comments
		$comments = '';
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

			//Format the citation ourselves
			//Authors, title, source, year
			foreach($xref->AUTHORS as $a) {
				$authors .= "$a, ";
			}

			if($authors) $authors = substr($authors, 0, -2) . "; ";
			if($xref->TITLE) $title = $xref->TITLE . "; ";
			if($xref->SOURCE) $source = $xref->SOURCE;
			if($xref->YEAR) $year = ", " . $xref->YEAR;
			$out .= "# $authors''$title''$source$year";
			
			if((string)$xref->ID && (strtolower($xref->DB) == 'pubmed')) {
				//We have a pubmed id, use biblio extension
				$out .= " - [http://www.ncbi.nlm.nih.gov/pubmed/" . $xref->ID . " PubMed]";
			}
			$out .= "\n";
		}
		if($out) {
			
		} else {
			$out = "''No bibliography''\n";
		}
		//No edit button for now, show help on how to add bibliography instead
		//$button = $this->editButton('javascript:;', 'Edit bibliography', 'bibEdit');
		#&$parser, $idClick = 'direct', $idReplace = 'pwThumb', $new = '', $pwTitle = '', $type = 'editor'
		$help = '';
		if($wgUser->isLoggedIn()) {
			$help = "{{Template:Help:LiteratureReferences}}";
		}
		return "== Bibliography ==\n$out\n$help";
			//"<div id='bibliography'><div style='float:right'>$button</div>\n" .
			//"$out</div>\n{{#editApplet:bibEdit|bibliography|0||bibliography|0|250px}}";		
	}
	
	function categoryText() {
		$categories = $this->pathway->getCategoryHandler()->getCategories();
	
		$species = Pathway::getAvailableSpecies();
		
		$catlist = '';
		foreach($categories as $c) {
			$cat = Title::newFromText($c, NS_CATEGORY);
			if(!$cat) continue; //Prevent error when empty category is introduced in GPML
			
			$name = $cat->getText();
			if(in_array($name, $species)) {
				$browseCat = '&browseCat=All+Categories';
				$browse = "&browse=" . urlencode($name);
			} else {
				$browse = '&browse=All+Species';
				$browseCat = "&browseCat=" . urlencode($name);
			}
			$link = SITE_URL . "/index.php?title=Special:BrowsePathwaysPage{$browse}{$browseCat}";
			$catlist .= "* <span class='plainlinks'>[{$link} {$name}]</span>\n";
		}
		$button = $this->editButton('javascript:;', 'Edit categories', 'catEdit');
		$title = $this->pathway->getIdentifier();
		return "== Categories ==\n<div id='catdiv'>\n" .
			"<div style='float:right'>$button</div>\n" . 
			"$catlist</div>\n{{#editApplet:catEdit|catdiv|0|$id|categories|0|250px}}";
	}
	
	function editButton($href, $title, $id = '') {
		global $wgUser, $wgTitle;
		# Check permissions
		if( $wgUser->isLoggedIn() && $wgTitle && $wgTitle->userCan('edit')) {
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
		if($pathway->getActiveRevision()) {
			$oldid = "&oldid={$pathway->getActiveRevision()}";
		}
		return WPI_SCRIPT_URL . "?action=downloadFile&type=$type&pwTitle={$pathway->getTitleObject()->getFullText()}{$oldid}";
	}

	static function editDropDown($pathway) {
		global $wgOut;
		
		//AP20081218: Operating System Detection
		require_once 'DetectBrowserOS.php';
		//echo (browser_detection( 'os' ));
		if (browser_detection( 'os' ) != 'win' && browser_detection( 'os' ) != 'nt'){
                $download = array(
                        'PathVisio (.gpml)' => self::getDownloadURL($pathway, 'gpml'),
                        'Gene list (.txt)' => self::getDownloadURL($pathway, 'txt'),
                        'Eu.Gene (.pwf)' => self::getDownloadURL($pathway, 'pwf'),
                        'Png image (.png)' => self::getDownloadURL($pathway, 'png'),
                        'Acrobat (.pdf)' => self::getDownloadURL($pathway, 'pdf'),
                );
		} else {
		$download = array(
			'PathVisio (.gpml)' => self::getDownloadURL($pathway, 'gpml'),
			'GenMAPP (.mapp)' => self::getDownloadURL($pathway, 'mapp'),
			'Gene list (.txt)' => self::getDownloadURL($pathway, 'txt'),
			'Eu.Gene (.pwf)' => self::getDownloadURL($pathway, 'pwf'),
			'Png image (.png)' => self::getDownloadURL($pathway, 'png'),
			'Acrobat (.pdf)' => self::getDownloadURL($pathway, 'pdf'),
		);
		}

		$pwTitle = $pathway->getTitleObject()->getFullText();
		$cytoscape = "<li><a href='" . WPI_SCRIPT_URL . "?action=launchCytoscape&pwTitle=$pwTitle'>Open in Cytoscape</a></li>";
		
		$downloadlist = '';
		foreach(array_keys($download) as $key) {
			$downloadlist .= "<li><a href='{$download[$key]}'>$key</a></li>";
		}
		
		$dropdown = <<<DROPDOWN
<ul id="nav" name="nav">		
<li><a href="#nogo2" class="button buttondown"><span>Download</span></a>
		<ul>
			<li><a class="parent" href="#">Download for</a>
					<ul>
						$downloadlist
					</ul>
			</li>
			$cytoscape
		</ul>
</li>
</ul>

DROPDOWN;

		$script = <<<SCRIPT
<script type="text/javascript">

sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(" sfhover", "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

</script>
SCRIPT;
		$wgOut->addScript($script);
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
