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
	static $msgLoaded = false;
	
	function __construct($pathway) {
		$this->pathway = $pathway;
		$this->data = $pathway->getPathwayData();
		
		global $wgMessageCache;
		if(!self::$msgLoaded) {
			$wgMessageCache->addMessages( array(
				'private_warning' => '{{SERVER}}{{SCRIPTPATH}}/skins/common/images/lock.png This pathway will not be visible to other users until $DATE. ' .
				'To make it publicly available before that time, <span class="plainlinks">[{{fullurl:{{FULLPAGENAMEE}}|action=manage_permissions}} change the permissions]</span>.'
			), 'en' );
			self::$msgLoaded = true;
		}
	}

	function getContent() {	
		$text = <<<TEXT
{$this->privateWarning()}
{{Template:PathwayPage:Top}}
{$this->curationTags()}
{$this->descriptionText()}
{$this->ontologyTags()}
{$this->bibliographyText()}
{$this->categoryText()}
{{Template:PathwayPage:Bottom}}
TEXT;
		return $text;
	}
	
	function privateWarning() {
		global $wgScriptPath, $wgLang;
		
		$warn = '';
		if(!$this->pathway->isPublic()) {
			$url = SITE_URL;
			$msg = wfMsg('private_warning');
			
			$pp = $this->pathway->getPermissionManager()->getPermissions();
			$expdate = $pp->getExpires();
			$expdate = $wgLang->date($expdate, true);
			$msg = str_replace('$DATE', $expdate, $msg);
			$warn = "<div class='private_warn'>$msg</div>";
		}
		return $warn;
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
	

	function ontologyTags() {
		global $wpiEnableOtag;
		if($wpiEnableOtag) {
			$otags = "== Ontology Tags ==\n" .
				"<OntologyTags></OntologyTags>";
			return $otags;
		}
	}


	function bibliographyText() {
		global $wgUser;
		
		$out = "<pathwayBibliography></pathwayBibliography>";
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
		 $download = array(
                        'PathVisio (.gpml)' => self::getDownloadURL($pathway, 'gpml'),
                        'Scalable Vector Graphics (.svg)' => self::getDownloadURL($pathway, 'svg'),
                        'Gene list (.txt)' => self::getDownloadURL($pathway, 'txt'),
                        'Biopax level 3 (.owl)' => self::getDownloadURL($pathway, 'owl'),
                        'Eu.Gene (.pwf)' => self::getDownloadURL($pathway, 'pwf'),
                        'Png image (.png)' => self::getDownloadURL($pathway, 'png'),
                        'Acrobat (.pdf)' => self::getDownloadURL($pathway, 'pdf'),
           );
		if (browser_detection( 'os' ) == 'win' || browser_detection( 'os' ) == 'nt'){
               $download['GenMAPP (.mapp)'] = self::getDownloadURL($pathway, 'mapp');
		}

		$downloadlist = '';
		foreach(array_keys($download) as $key) {
			$downloadlist .= "<li><a href='{$download[$key]}'>$key</a></li>";
		}
		
		$dropdown = <<<DROPDOWN
<ul id="nav" name="nav">		
<li><a href="#nogo2" class="button buttondown"><span>Download</span></a>
		<ul>
			$downloadlist
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
