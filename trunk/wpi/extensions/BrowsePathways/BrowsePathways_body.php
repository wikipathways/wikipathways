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
	protected $all         = 'All Species';
	protected $none        = 'Uncategorized';

	# Determines, which message describes the input field 'nsfrom' (->SpecialPrefixindex.php)
	var $nsfromMsg='allpagesfrom';

	function __construct( $empty = null ) {
		SpecialPage::SpecialPage( $this->name );
	}

	function execute( $par) {

		global $wgOut;

		$wgOut->setPagetitle("browsepathways");

		$nsForm = $this->namespaceForm( );

		$wgOut->addHtml( $nsForm . '<hr />');

		$wgOut->addWikiText(
			"<DPL>
				notnamespace=Image
				namespace=Pathway
				shownamespace=false
				mode=category
				ordermethod=title
			</DPL>");
	}


	/**
	 * HTML for the top form
	 * @param integer $namespace A namespace constant (default NS_PATHWAY).
	 * @param string $from Article name we are starting listing at.
	 */
	function namespaceForm ( $namespace = NS_PATHWAY ) {
		global $wgScript, $wgContLang, $wgOut, $wgRequest;
		$t = SpecialPage::getTitleFor( $this->name );

		/**
		 * Species Selection
		 */
		$arr = Pathway::getAvailableSpecies();
		asort($arr);
		$arr[] = $this->all;
		$arr[] = $this->none;

		$selected = $wgRequest->getVal("browse");
		$speciesselect = "\n<select onchange='this.form.submit()' name='browse' class='namespaceselector'>\n";
		foreach ($arr as $index) {
			if ($index == $selected) {
				$speciesselect .= "\t" . Xml::element("option",
					array("value" => $index, "selected" => "selected"), $index) . "\n";
			} else {
				$speciesselect .= "\t" . Xml::element("option", array("value" => $index), $index) . "\n";
			}
		}
		$speciesselect .= "</select>\n";

		$submitbutton = '<noscript><input type="submit" value="Go" name="pick" /></noscript>';

		$out = "<form method='get' action='{$wgScript}'>";
		$out .= '<input type="hidden" name="title" value="'.$t->getPrefixedText().'" />';
		$out .= "
<table id='nsselect' class='allpages'>
	<tr>
		<td align='right'>Display pathways from species:</td>
		<td align='left'>$speciesselect</td>$submitbutton</td>
	</tr>
</table>
";

		$out .= '</form>';
		return $out;
	}
}
