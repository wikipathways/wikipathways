<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 */

/** AP20070419
 * Added wpi.php to access Pathway class and getAvailableSpecies()
 */
require_once('wpi/wpi.php');

/**
 * Entry point : initialise variables and call subfunctions.
 * @param $par String: becomes "FOO" when called like Special:BrowsePathways/FOO (default NULL)
 * @param $specialPage @see SpecialPage object.
 */
function wfSpecialBrowsePathways( $par=NULL, $specialPage ) {
	global $wgRequest, $wgOut, $wgContLang, $from;

	# GET values

	//	$pick = $_POST["from"];
	//	if (isset($_POST['browse'])){
	//	echo "Pick: ".$pick."<br/>";
	//	}

	/** AP20070419
	 * Parse species header from 'from' so that prev/next links can work
	 */
	$from = $wgRequest->getVal( 'from' );
	$from_pathway = null;
	if(preg_match('/\:/', $from)){
		$from_pathway = $from;
		$parts = explode(':', $from);
		if(count($parts) < 1) {
			throw new Exception("Invalid pathway article title: $from");
		}
		$from = array_shift($parts);
	}


	/** AP20070419
	 *	$namespace = $wgRequest->getInt( 'namespace' );
	 *
	 *	Set $namespace to NS_PATHWAY
	 */
	$namespace = NS_PATHWAY;

	$namespaces = $wgContLang->getNamespaces();

	$indexPage = new BrowsePathways();

	if( !in_array($namespace, array_keys($namespaces)) )
		$namespace = 0;

	echo $from, $namespace;

	/** AP20070419
	 *	$wgOut->setPagetitle( $namespace > 0 ?
	 *		wfMsg( 'allinnamespace', str_replace( '_', ' ', $namespaces[$namespace] ) ) :
	 *		wfMsg( 'allarticles' )
	 *		);
	 *
	 *	Set Pagetitle to "Browse Pathways"
	 */
	// $wgOut->setPagetitle("Browse Pathways");

	/** AP20070419
	 *	Set default $indexPage to show Human
	 */
}


class LegacyBrowsePathways extends LegacySpecialPage {
	function __construct() {
		parent::__construct( "BrowsePathwaysPage", "BrowsePathways" );
	}
}

class BrowsePathways extends SpecialPage {

	function BrowsePathways() {
		SpecialPage::SpecialPage("BrowsePathways");
		self::loadMessages();
	}

	function execute( $par) {

		global $wgOut, $from;

		$wgOut->setPagetitle("Browse Pathways");

		$nsForm = $this->namespaceForm( $namespace );

		$wgOut->addHtml( $nsForm . '<hr />');

		$wgOut->addWikiText("<DPL>
				notnamespace=Image
						namespace=Pathway
						shownamespace=false
						mode=category
						ordermethod=title
								</DPL>");
	}


	function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/BrowsePathways.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}

	var $maxPerPage=960;
	var $topLevelMax=50;
	var $name='BrowsePathways';
	# Determines, which message describes the input field 'nsfrom' (->SpecialPrefixindex.php)
	var $nsfromMsg='allpagesfrom';


	/**
	 * HTML for the top form
	 * @param integer $namespace A namespace constant (default NS_PATHWAY).
	 * @param string $from Article name we are starting listing at.
	 */
	function namespaceForm ( $namespace = NS_PATHWAY ) {
		global $wgScript, $wgContLang, $wgOut;
		$t = SpecialPage::getTitleFor( $this->name );

		/** AP20070419
		 *	$namespaceselect = HTMLnamespaceselector($namespace, null);
		 *
		 *	$frombox = "<input type='text' size='20' name='from' id='nsfrom' value=\""
		 *	            . htmlspecialchars ( $from ) . '"/>';
		 */
		/**
		 * Species Selection
		 */
		$speciesselect = "\n<select onchange='this.form.submit()' name='browse' class='namespaceselector'>\n";

		$arr = Pathway::getAvailableSpecies();
		asort($arr);
		$selected = $pick;
		$all = 'All Species';
		$none = 'Uncategorized';

		foreach ($arr as $index) {
			if ($index == $selected) {
				$speciesselect .= "\t" . Xml::element("option",
					array("value" => $index, "selected" => "selected"), $index) . "\n";
			} else {
				$speciesselect .= "\t" . Xml::element("option", array("value" => $index), $index) . "\n";
			}
		}
		if ($selected == $all){
			$speciesselect .= "\t" . Xml::element("option",
				array("value" => $all, "selected" => "selected"), $all) . "\n";
		} else {
			$speciesselect .= "\t" . Xml::element("option", array("value" => $all), $all) . "\n";
		}

		if ($selected == $none){
			$speciesselect .= "\t" . Xml::element("option",
				array("value" => $none, "selected" => "selected"), $none) . "\n";
		} else {
			$speciesselect .= "\t" . Xml::element("option", array("value" => $none), $none) . "\n";
		}

		$speciesselect .= "</select>\n";

		$submitbutton = '<noscript><input type="submit" value="Go" name="pick" /></noscript>';

		$out = "<form method='get' action='{$wgScript}'>";
		$out .= '<input type="hidden" name="title" value="'.$t->getPrefixedText().'" />';
		$out .= "
<table id='nsselect' class='allpages'>
	<tr>
		<td align='right'>Display pathways from species:</td>
		<td align='left'>$speciesselect</td>
		<td align='right'> in category:</td>
		<td align='left'>
		$catselect
		$submitbutton
		</td>
	</tr>
</table>
";

		$out .= '</form>';
		return $out;
	}
}
