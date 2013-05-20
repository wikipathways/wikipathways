<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 */

/** AP20070419
 * Added wpi.php to access Pathway class and getAvailableSpecies()
 */
require_once('wpi/wpi.php');

class PathwaysPager extends AlphabeticPager {
	private $species;
	private $tag;
	private $ns = NS_PATHWAY;
	private $nsName;

	function __construct( $species, $tag ) {
		global $wgCanonicalNamespaceNames;

		$this->species = $species;
		$this->tag = $tag;
		if ( ! isset( $wgCanonicalNamespaceNames[ $this->ns ] ) ) {
			throw new MWException( "Invalid namespace {$this->ns}" );
		}
		$this->nsName = $wgCanonicalNamespaceNames[ $this->ns ];

		parent::__construct();
	}

	function getQueryInfo() {
		return array(
			'tables' => array( 'page', 'tag as t0', 'tag as t1', 'categorylinks' ),
				'fields' => array( 'page_title', 't1.tag_text' ),
				'conds' => array(
					'page_is_redirect' => '0',
					'page_namespace' => $this->ns,
					'cl_to' => $this->species,
					't0.tag_name' => $this->tag,
					't1.tag_name' => 'cache-name'
				),
				'join_conds' => array(
					'tag as t0' => array( 'JOIN', 't0.page_id = page.page_id'),
					'tag as t1' => array( 'JOIN', 't1.page_id = page.page_id'),
					'categorylinks' => array( 'JOIN', 'page.page_id=cl_from' )
				)
			);
		}

	function getIndexField() {
		return 't1.tag_text';
	}

	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
				$pathway = Pathway::newFromTitle( $title );
		$s = '<li><a href="' . $title->getFullURL() . '">' . $pathway->getName() . '</a></li>';
		return $s;
	}
}

class LegacyBrowsePathways extends LegacySpecialPage {
	function __construct() {
		parent::__construct( "BrowsePathwaysPage", "BrowsePathways" );
	}
}

class BrowsePathways extends SpecialPage {

	protected $maxPerPage  = 960;
	protected $topLevelMax = 50;
	protected $name        = 'BrowsePathways';

	# Determines, which message describes the input field 'nsfrom' (->SpecialPrefixindex.php)
	var $nsfromMsg='browsepathwaysfrom';

	function __construct( $empty = null ) {
		SpecialPage::SpecialPage( $this->name );
	}

	static function initMsg( ) {
		# Need this called in hook early on so messages load... maybe a bug in old MW?
		wfLoadExtensionMessages( 'BrowsePathways' );
	}

	private $species;
	private $tag;

	function execute( $par) {
		global $wgOut, $wgRequest;

		$wgOut->setPagetitle( wfmsg( "browsepathways" ) );

		$this->species = $wgRequest->getVal("browse", 'Homo_sapiens');
		$this->tag     = $wgRequest->getVal("tag", CurationTag::defaultTag());
		$nsForm = $this->pathwayForm( );

		$wgOut->addHtml( $nsForm . '<hr />');

		$pager = new PathwaysPager( $this->species, $this->tag );
		$wgOut->addHTML(
			$pager->getNavigationBar() . "<ol>" .
			$pager->getBody() ."</ol>" .
			$pager->getNavigationBar()
		);
		return;

		$arr[] = wfMsg('browsepathways-uncategorized-species');
		$sql = $this->getSQL( );


		foreach( $rows as $title ) {
			$wgOut->addWikiText( "* [[$title]]\n" );
		}
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


	function getSpeciesSelectionList( ) {
		$arr = Pathway::getAvailableSpecies();
		asort($arr);
		$arr[] = wfMsg('browsepathways-all-species');
		$arr[] = wfMsg('browsepathways-uncategorized-species');

		$sel = "\n<select onchange='this.form.submit()' name='browse' class='namespaceselector'>\n";
		foreach ($arr as $index) {
					$sel .= $this->makeSelectionOption( Title::newFromText( $index )->getDBKey(), $this->species, $index );
		}
		$sel .= "</select>\n";
		return $sel;
	}

	function getTagSelectionList( ) {
		$sel = "<select onchange='this.form.submit()' name='tag' class='namespaceselector'>\n";
		foreach( CurationTag::getTagNames() as $tag ) {
			$display = CurationTag::getDisplayName( $tag );
			$sel .= $this->makeSelectionOption( $tag, $this->tag, $display );
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
	function pathwayForm ( ) {
		global $wgScript, $wgContLang, $wgOut;
		$t = SpecialPage::getTitleFor( $this->name );

		/**
		 * Species Selection
		 */
		$speciesSelect = $this->getSpeciesSelectionList( );
		$tagSelect = $this->getTagSelectionList( );
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
