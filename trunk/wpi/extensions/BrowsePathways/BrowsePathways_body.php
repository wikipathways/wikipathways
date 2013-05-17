<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 */

/** AP20070419
 * Added wpi.php to access Pathway class and getAvailableSpecies()
 */
require_once('wpi/wpi.php');

class LegacyBrowsePathways extends LegacySpecialPage {
	function __construct() {
		parent::__construct( "BrowsePathwaysPage", "BrowsePathways" );
	}
}

class BrowsePathways extends SpecialPage {

	protected $tags = array("Featured", "Analysis Collection", "Needs Work");
	protected $adminTags = array("Under Construction", "Stub", "Tutorial", "Proposed for Deletion");
	protected $allOtherTags = array("Missing Gene Refs", "Missing Description", "Lit Refs Needed", "Unconnected Lines");

	protected $maxPerPage  = 960;
	protected $topLevelMax = 50;
	protected $name        = 'BrowsePathways';

	# Determines, which message describes the input field 'nsfrom' (->SpecialPrefixindex.php)
	var $nsfromMsg='allpagesfrom';

	function __construct( $empty = null ) {
		SpecialPage::SpecialPage( $this->name );
	}

	static function initMsg( ) {
		# Need this called in hook early on so messages load... maybe a bug in old MW?
		wfLoadExtensionMessages( 'BrowsePathways' );
	}

	function execute( $par) {
		global $wgOut, $wgRequest;

		$wgOut->setPagetitle( wfmsg( "browsepathways" ) );

		$species = $wgRequest->getVal("browse", 'Homo sapiens');
		$tag = $wgRequest->getVal("tag", 'Curation:FeaturedPathway');
		$nsForm = $this->pathwayForm( $species, $tag );

		$arr[] = wfMsg('browsepathways-uncategorized-species');
		$selectedSpecies = $this->getSelection( $species );
		$selectedTags    = $this->getSelectedTag( $tag );

		$wgOut->addHtml( $nsForm . '<hr />');
		$pageText = "<DPL>
				$selectedTags
				$selectedSpecies
				notnamespace=Image
				namespace=Pathway
				shownamespace=false
				mode=category
				ordermethod=title
			</DPL>";
		//var_dump($pageText);
		$wgOut->addWikiText( $pageText );
	}

	function getSelectedTag( $tag ) {
		return "tag=$tag";
	}


	function getSelection( $pick ) {
		$category = "category=";
		$selection = "";
		if ($pick == wfMsg('browsepathways-all-species') ) {
			$picked = '';
			$arr = Pathway::getAvailableSpecies();
			asort($arr);
			foreach ($arr as $index) {
				$picked .=  $index."|";
			}
			$picked[strlen($picked)-1] = ' ';
			$selection = $category.$picked;
		} else if ($pick == wfMsg('browsepathways-uncategorized-species')) {
			$category = 'notcategory=';
			$arr = Pathway::getAvailableSpecies();
			asort($arr);
			foreach ($arr as $index) {
				$selection .= $category.$index."\n";
			}
		} else {
			$picked = $pick;
			$selection = $category.$picked;
		}
		return  $selection;
	}


	function getSpeciesSelectionList( $species ) {
		$arr = Pathway::getAvailableSpecies();
		asort($arr);
		$arr[] = wfMsg('browsepathways-all-species');
		$arr[] = wfMsg('browsepathways-uncategorized-species');

		$sel = "\n<select onchange='this.form.submit()' name='browse' class='namespaceselector'>\n";
		foreach ($arr as $index) {
			$sel .= $this->makeSelectionOption( $index, $species );
		}
		$sel .= "</select>\n";
		return $sel;
	}

	function getTagSelectionList( $selected ) {
		$sel = "<select onchange='this.form.submit()' name='tag' class='namespaceselector'>\n";
		foreach( CurationTag::getTagNames() as $tag ) {
			$display = CurationTag::getDisplayName( $tag );
			$sel .= $this->makeSelectionOption( $tag, $selected, $display );
		}
		$sel .= "</select>\n";
		return $sel;
	}

	private function makeSelectionOption( $item, $selected, $display = null ) {
		$attr = array( "value" => $item );
		if( null === $display ) {
			$display = $item;
		}
		if ( $item == $selected ) {
			$attr['selected'] = 1;
		}

		return "\t" . Xml::element( "option", $attr, $display ) . "\n";
	}

	/**
	 * HTML for the top form
	 * @param string Species to show pathways for
	 */
	function pathwayForm ( $species, $tag ) {
		global $wgScript, $wgContLang, $wgOut;
		$t = SpecialPage::getTitleFor( $this->name );

		/**
		 * Species Selection
		 */
		$speciesSelect = $this->getSpeciesSelectionList( $species );
		$tagSelect = $this->getTagSelectionList( $tag );
		$submitbutton = '<noscript><input type="submit" value="Go" name="pick" /></noscript>';

		$out = "<form method='get' action='{$wgScript}'>";
		$out .= '<input type="hidden" name="title" value="'.$t->getPrefixedText().'" />';
		$out .= "
<table id='nsselect' class='allpages'>
	<tr>
		<td align='right'>". wfMsg("browsepathways-selectspecies") ."</td>
		<td align='left'>$speciesSelect</td>
		<td align='left'>$tagSelect</td>
		<td>$submitbutton</td>
	</tr>
</table>
";

		$out .= '</form>';
		return $out;
	}
}
