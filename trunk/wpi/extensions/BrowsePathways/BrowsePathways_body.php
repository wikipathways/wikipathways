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
		$q = array(
			'options' => array( 'DISTINCT' ),
			'tables' => array( 'page', 'tag as t0', 'tag as t1' ),
			'fields' => array( 't1.tag_text', 'page_title' ),
			'conds' => array(
				'page_is_redirect' => '0',
				'page_namespace' => $this->ns,
				't0.tag_name' => $this->tag,
				't1.tag_name' => 'cache-name'
			),
			'join_conds' => array(
				'tag as t0' => array( 'JOIN', 't0.page_id = page.page_id'),
				'tag as t1' => array( 'JOIN', 't1.page_id = page.page_id'),
			)
		);
		if( $this->species ) {
			$q['tables'][] = 'categorylinks';
			$q['join_conds']['categorylinks'] = array( 'JOIN', 'page.page_id=cl_from' );
			$q['conds']['cl_to'] = $this->species;
		}

		return $q;
	}

	function getIndexField() {
		return 't1.tag_text';
	}

	function getGPMLlink( $pathway ) {
		if($pathway->getActiveRevision()) {
			$oldid = "&oldid={$pathway->getActiveRevision()}";
		}
		return XML::Element("a",
			array("href" => WPI_SCRIPT_URL . "?action=downloadFile&type=gpml&pwTitle=".
				$pathway->getTitleObject()->getFullText() . $oldid), " (gpml) ");
	}

	function getThumb( $pathway, $icons, $boxwidth = 180, $withText = true ) {
		global $wgStylePath, $wgContLang;

		$label = $pathway->name();
		if( !$this->species ) {
			$label .= "<br/>(" . $pathway->species() . ")";
		}
		$label .= $icons;

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
				/* No link to download $link = $this->getGPMLlink( $pathway ); */
			}
		}
		$s .= '</a>';
		if( $withText ) {
			$s .= $link.'<div class="thumbcaption"'.$textalign.'>'.$label."</div>";
		}
		$s .= "</div></div>";

		return str_replace("\n", ' ', $s);
	}

	function formatTags( $title ) {
		global $wgRequest;

		$tags = CurationTag::getCurationImagesForTitle( $title );
		ksort( $tags );
		$tagLabel = "<div class='tag-icons'>";
		foreach( $tags as $label => $attr ) {
			$img = wfLocalFile( $attr['img'] );
			$imgLink = Xml::element('img', array( 'src' => $img->getURL(), "title" => $label ));
			$href = $wgRequest->appendQueryArray( array( "tag" => $attr['tag'] ) );
			$tagLabel .= Xml::element('a', array( 'href' => $href ), null ) . $imgLink . "</a>";
		}
		$tagLabel .= "</div>";
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
	function getStartBody() {
		return "<ul>";
	}

	function getEndBody() {
		return "</ul>";
	}

	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		return '<li><a href="' . $title->getFullURL() . '">' . $pathway->getName() . '</a>' .
			$this->formatTags( $title ) . "</li>";
	}
}

class ThumbPathwaysPager extends BasePathwaysPager {
	function getStartBody() {
		return "<br clear='both'>";
	}

	function getEndBody() {
		return "<br clear='both'>";
	}
	/* From getDownloadURL in PathwayPage */
	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		return $this->getThumb( $pathway, $this->formatTags( $title ) );
	}
}

class SinglePathwaysPager extends BasePathwaysPager {
	function __construct( $species, $tag  ) {
		parent::__construct( $species, $tag );

		$this->mLimitsShown = array( 5 );
		$this->mDefaultLimit = 5;
		$this->mLimit = 5;
	}

	function getStartBody() {
		return "<div id='singleMode'>";
	}

	function getEndBody() {
		return "</div><div id='singleModeSlider' style='clear: both'></div>";
	}


	function getNavigationBar() {
		/* Nothing */
	}

	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		return $this->getThumb( $pathway, $this->formatTags( $title ), 100, false );
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
	static private $defaultView = "thumbs";
	//	static private $sizes       = array( "list", "thumbs", "single" );
	static private $views  = array( "list", "thumbs" );

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
		$this->view    = $wgRequest->getVal( "view", self::$defaultView );
		$nsForm = $this->pathwayForm( );

		$wgOut->addHtml( $nsForm . '<hr />');

		$pager = PathwaysPagerFactory::get( $this->view, $this->species, $this->tag );
		$wgOut->addHTML(
			$pager->getNavigationBar() .
			$pager->getBody() .
			$pager->getNavigationBar()
		);
		return;
	}

	protected function getSpeciesSelectionList( ) {
		$arr = Pathway::getAvailableSpecies();
		asort($arr);
		$all = wfMsg('browsepathways-all-species');
		$arr[] = $all;
		/* $arr[] = wfMsg('browsepathways-uncategorized-species'); Don't look for uncategorized species */

		$sel = "\n<select onchange='this.form.submit()' name='browse' class='namespaceselector'>\n";
		foreach ($arr as $label) {
			$value = Title::newFromText( $label )->getDBKey();
			if( $label === $all ) {
				$value = "";
			}
			$sel .= $this->makeSelectionOption( $value, $this->species, $label );
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

	protected function getViewSelectionList( ) {
		$sel = "\n<select onchange='this.form.submit()' name='view' class='namespaceselector'>\n";
		foreach ( self::$views as $s ) {
			$sel .= $this->makeSelectionOption( $s, $this->view, wfMsg("browsepathways-view-".$s) );
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
		$viewSelect    = $this->getViewSelectionList( );
		$submitbutton = '<noscript><input type="submit" value="Go" name="pick" /></noscript>';

		$out = "<form method='get' action='{$wgScript}'>";
		$out .= '<input type="hidden" name="title" value="'.$t->getPrefixedText().'" />';
		$out .= "
<table id='nsselect' class='allpages'>
	<tr>
		<td align='right'>". wfMsg("browsepathways-select-species") ."</td>
		<td align='left'>$speciesSelect</td>
		<td align='right'>". wfMsg("browsepathways-select-collection") ."</td>
		<td align='left'>$tagSelect</td>
		<td align='right'>". wfMsg("browsepathways-select-view") ."</td>
		<td align='left'>$viewSelect</td>
		<td>$submitbutton</td>
	</tr>
</table>
";

		$out .= '</form>';
		return $out;
	}
}
