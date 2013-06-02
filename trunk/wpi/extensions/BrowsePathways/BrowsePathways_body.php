<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 */

/** AP20070419
 * Added wpi.php to access Pathway class and getAvailableSpecies()
 */
require_once('wpi/wpi.php');

abstract class BasePathwaysPager extends AlphabeticPager {
	protected $species;
	protected $tag;
	protected $ns = NS_PATHWAY;
	protected $nsName;

	function __construct( $species, $tag  ) {
		global $wgCanonicalNamespaceNames;

		if ( ! isset( $wgCanonicalNamespaceNames[ $this->ns ] ) ) {
			throw new MWException( "Invalid namespace {$this->ns}" );
		}
		$this->nsName = $wgCanonicalNamespaceNames[ $this->ns ];
		$this->species = $species;
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

	function formatTags( $title ) {
		global $wgRequest;

		$tags = CurationTag::getCurationImagesForTitle( $title );
		ksort( $tags );
		$tagLabel = "";
		foreach( $tags as $label => $attr ) {
			$img = wfLocalFile( $attr['img'] );
			$imgLink = Xml::element('img', array( 'src' => $img->getURL(), "title" => $label ));
			$href = $wgRequest->appendQueryArray( array( "tag" => $attr['tag'] ) );
			$tagLabel .= Xml::element('a', array( 'href' => $href ), null ) . $imgLink . "</a>";
		}
		return $tagLabel;
	}
}

class PathwaysPagerFactory {
	static function get( $type, $species, $tag ) {
		switch( $type ) {
			case 'list':
				return new ListPathwaysPager( $species, $tag );
				break;
			case 'single':
				return new SinglePathwaysPager( $species, $tag );
				break;
			default:
				return new ThumbPathwaysPager( $species, $tag );
		}
	}
}

class ListPathwaysPager extends BasePathwaysPager {
	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		return '<li><a href="' . $title->getFullURL() . '">' . $pathway->getName() . '</a>' .
			$this->formatTags( $title ) . "</li>";
	}
}

class ThumbPathwaysPager extends BasePathwaysPager {
	/* From getDownloadURL in PathwayPage */
	function getGPMLlink( $pathway ) {
		if($pathway->getActiveRevision()) {
			$oldid = "&oldid={$pathway->getActiveRevision()}";
		}
		return XML::Element("a",
			array("href" => WPI_SCRIPT_URL . "?action=downloadFile&type=gpml&pwTitle=".
				$pathway->getTitleObject()->getFullText() . $oldid), " (gpml) ");
	}

	function getThumb( $pathway, $icons ) {
		global $wgStylePath, $wgContLang;

		$label = $pathway->name() . "<br/>(" . $pathway->species() . ")<br/>" . $icons;
		$boxwidth = 180;
		$boxheight=-1;
		$framed=false;
		$href = $pathway->getFullURL();
		$class = "browsePathways";
		$id = $pathway->getTitleObject();
		$textalign = $wgContLang->isRTL() ? ' style="text-align:right"' : '';
		$oboxwidth = $boxwidth + 2;
		$s = "<div id=\"{$id}\" class=\"{$class}\"><div class=\"thumbinner\" style=\"width:{$oboxwidth}px;\">".
			'<a href="'.$href.'" class="internal">';

		$img = new Image($pathway->getFileTitle(FILETYPE_IMG));
		$img->loadFromFile();
		$link = "";

		if ( !$img->exists() ) {
			$s .= "Image does not exist";
		} else {
			$pathway->updateCache(FILETYPE_IMG);
			$imgURL = $img->getURL();

			$thumbUrl = '';
			$error = '';

			$width  = $img->getWidth();
			$height = $img->getHeight();

			$thumb = $img->getThumbnail( $boxwidth, $boxheight );
			if ( $thumb ) {
				$thumbUrl = $thumb->getUrl();
				$boxwidth = $thumb->width;
				$boxheight = $thumb->height;
			} else {
				$error = $img->getLastError();
			}

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
			} else {
				$s .= '<img src="'.$thumbUrl.'" '.
					'width="'.$boxwidth.'" height="'.$boxheight.'" ' .
					'longdesc="'.$href.'" class="thumbimage" />';
				$link = $this->getGPMLlink( $pathway );
			}
		}
		$s .= '</a>'.$link.'<div class="thumbcaption"'.$textalign.'>'.$label."</div></div></div>";
		return str_replace("\n", ' ', $s);
	}

	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		return $this->getThumb( $pathway, $this->formatTags( $title ) );
	}
}

class SinglePathwaysPager extends BasePathwaysPager {
	function __construct( $species, $tag  ) {
		parent::__construct();

		$this->mLimitsShown = array( 5 );
		$this->mDefaultLimit = 5;
	}

	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		return $this->getSingle( $pathway, $this->formatTags( $title ) );
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

		$pager = PathwaysPagerFactory::get( $this->size, $this->species, $this->tag );
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
