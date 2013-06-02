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
	protected $species;
	protected $tag;
	protected $size;
	protected $ns = NS_PATHWAY;
	protected $nsName;

	function __construct( $species, $tag, $size ) {
		global $wgCanonicalNamespaceNames;

		if ( ! isset( $wgCanonicalNamespaceNames[ $this->ns ] ) ) {
			throw new MWException( "Invalid namespace {$this->ns}" );
		}
		$this->nsName = $wgCanonicalNamespaceNames[ $this->ns ];
		$this->species = $species;
		$this->size = $size;
		if( strstr( $tag, "|" ) === false ) {
			$this->tag = $tag;
		} else {
			$this->tag = explode( "|", $tag );
		}


		parent::__construct();
	}

	function getQueryInfo() {
		return array(
			'options' => array( 'DISTINCT' ),
			'tables' => array( 'page', 'tag as t0', 'tag as t1', 'categorylinks' ),
			'fields' => array( 't1.tag_text', 'page_title' ),
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

	function getThumb( $pathway, $icons ) {
		global $wgStylePath, $wgContLang;

		$label = $pathway->name() . "<br/>(" . $pathway->species() . ")<br/>" . $icons;
		$boxwidth = 180;
		$boxheight=false;
		$framed=false;
		$href = $pathway->getFullURL();
		$alt = "";
		$class = "browsePathways";
		$id = $pathway->getTitleObject();

		$pathway->updateCache(FILETYPE_IMG);
		$img = new Image($pathway->getFileTitle(FILETYPE_IMG));
		$img->loadFromFile();

		$imgURL = $img->getURL();

		$thumbUrl = '';
		$error = '';

		$width = $height = 0;
		if ( $img->exists() ) {
			$width  = $img->getWidth();
			$height = $img->getHeight();
		}
		if ( 0 == $width || 0 == $height ) {
			$width = $height = 180;
		}

		if ( $boxheight === false ) $boxheight = -1;
		$thumb = $img->getThumbnail( $boxwidth, $boxheight );
		if ( $thumb ) {
			$thumbUrl = $thumb->getUrl();
			$boxwidth = $thumb->width;
			$boxheight = $thumb->height;
		} else {
			$error = $img->getLastError();
		}
		$oboxwidth = $boxwidth + 2;

		$more = htmlspecialchars( wfMsg( 'thumbnail-more' ) );
		$magnifyalign = $wgContLang->isRTL() ? 'left' : 'right';
		$textalign = $wgContLang->isRTL() ? ' style="text-align:right"' : '';

		$s = "<div id=\"{$id}\" class=\"{$class}\"><div class=\"thumbinner\" style=\"width:{$oboxwidth}px;\">";
		if( $thumbUrl == '' ) {
			// Couldn't generate thumbnail? Scale the image client-side.
			$thumbUrl = $img->getViewURL();
			if( $boxheight == -1 ) {
				// Approximate...
				$boxheight = intval( $height * $boxwidth / $width );
			}
		}
		if ( $error ) {
			$s .= htmlspecialchars( $error );
		} elseif( !$img->exists() ) {
			$s .= "Image does not exist";
		} else {
			$s .= '<a href="'.$href.'" class="internal" title="'.$alt.'">'.
				'<img src="'.$thumbUrl.'" alt="'.$alt.'" ' .
				'width="'.$boxwidth.'" height="'.$boxheight.'" ' .
				'longdesc="'.$href.'" class="thumbimage" /></a>';
		}
		$s .= '  <div class="thumbcaption"'.$textalign.'>'.$label."</div></div></div>";
		return str_replace("\n", ' ', $s);
	}

	function formatRow( $row ) {
		global $wgRequest, $wgOut, $wgParser, $wgTitle;

		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );
		$endRow = "";
		$tags = CurationTag::getCurationImagesForTitle( $title );
		ksort( $tags );
		$tagLabel = "";
		foreach( $tags as $label => $attr ) {
			$img = wfLocalFile( $attr['img'] );
			$imgLink = Xml::element('img', array( 'src' => $img->getURL(), "title" => $label ));
			$href = $wgRequest->appendQueryArray( array( "tag" => $attr['tag'] ) );
			$tagLabel .= Xml::element('a', array( 'href' => $href ), null ) . $imgLink . "</a>";
		}

		if( $this->size == "thumbs" ) {
			$s = $this->getThumb( $pathway, $tagLabel );
		} else {
			$s = '<li><a href="' . $title->getFullURL() . '">' . $pathway->getName() . '</a>';
			$endRow = "$tagLabel</li>";
		}
		return $s . $endRow;
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
	static private $defaultSize = "thumbs";
	static private $sizes       = array( "list", "thumbs", "single" );

	# Determines, which message describes the input field 'nsfrom' (->SpecialPrefixindex.php)
	var $nsfromMsg='browsepathwaysfrom';

	function __construct( $empty = null ) {
		SpecialPage::SpecialPage( $this->name );
	}

	static function initMsg( ) {
		# Need this called in hook early on so messages load... maybe a bug in old MW?
		wfLoadExtensionMessages( 'BrowsePathways' );
	}

	protected $species;
	protected $tag;
	protected $size;

	function execute( $par) {
		global $wgOut, $wgRequest;

		$wgOut->setPagetitle( wfmsg( "browsepathways" ) );

		$this->species = $wgRequest->getVal( "browse", 'Homo_sapiens' );
		$this->tag     = $wgRequest->getVal( "tag", CurationTag::defaultTag() );
		$this->size    = $wgRequest->getVal( "size", self::$defaultSize );
		$nsForm = $this->pathwayForm( );

		$wgOut->addHtml( $nsForm . '<hr />');

		$beginList = "<ol>";
		$endList = "</ol>";
		if( $this->size == "thumbs" ) {
			$beginList = "<div class='browsePathwaysBlock'>";
			$endList = "</div><br clear='both'>";
		}

		$pager = new PathwaysPager( $this->species, $this->tag, $this->size );
		$wgOut->addHTML(
			$pager->getNavigationBar() . $beginList .
			$pager->getBody() .$endList .
			$pager->getNavigationBar()
		);
		return;
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

	protected function getSpeciesSelectionList( ) {
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

	protected function getTagSelectionList( ) {
		$sel = "<select onchange='this.form.submit()' name='tag' class='namespaceselector'>\n";
								foreach( CurationTag::getUserVisibleTagNames() as $display => $tag ) {
			if( is_array( $tag ) ) {
				$tag = implode( "|", $tag );
			}
			$sel .= $this->makeSelectionOption( $tag, $this->tag, $display );
		}
		$sel .= "</select>\n";
		return $sel;
	}

	protected function getSizeSelectionList( ) {
		$sel = "\n<select onchange='this.form.submit()' name='size' class='namespaceselector'>\n";
		foreach ( self::$sizes as $s ) {
			$sel .= $this->makeSelectionOption( $s, $this->size );
		}
		$sel .= "</select>\n";
		return $sel;
	}

	protected function makeSelectionOption( $item, $selected, $display = null ) {
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
		$tagSelect     = $this->getTagSelectionList( );
		$sizeSelect    = $this->getSizeSelectionList( );
		$submitbutton = '<noscript><input type="submit" value="Go" name="pick" /></noscript>';

		$out = "<form method='get' action='{$wgScript}'>";
		$out .= '<input type="hidden" name="title" value="'.$t->getPrefixedText().'" />';
		$out .= "
<table id='nsselect' class='allpages'>
	<tr>
		<td align='right'>". wfMsg("browsepathways-selectspecies") ."</td>
		<td align='left'>$speciesSelect</td>
		<td align='left'>$tagSelect</td>
		<td align='left'>$sizeSelect</td>
		<td>$submitbutton</td>
	</tr>
</table>
";

		$out .= '</form>';
		return $out;
	}
}
