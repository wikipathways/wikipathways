<?php

abstract class BasePathwaysPager extends AlphabeticPager {
	protected $species;
	protected $tag;
	protected $sortOrder;
	protected $ns = NS_PATHWAY;
	protected $nsName;

	public function getOffset( ) {
		global $wgRequest;
		return $wgRequest->getText( 'offset' );
	}

	public function getLimit( ) {
		global $wgRequest;
		$result = $wgRequest->getLimitOffset();
		return $result[0];
	}

	public function isBackwards( ) {
		global $wgRequest;
		return ( $wgRequest->getVal( 'dir' ) == 'prev' );
	}

	public function getOrder( ) {
		global $wgRequest;
		return $wgRequest->getVal( 'order' );
	}

	public function __construct($species, $tag, $sortOrder) {
		global $wgCanonicalNamespaceNames;

		if ( ! isset( $wgCanonicalNamespaceNames[ $this->ns ] ) ) {
			throw new MWException( "Invalid namespace {$this->ns}" );
		}
		$this->nsName = $wgCanonicalNamespaceNames[ $this->ns ];
		$this->species = $species;
		$this->sortOrder = $sortOrder;
		if( $tag !== "---" ) {
			$this->tag = $tag;
		} else {
			$label = CurationTag::getUserVisibleTagNames();
			$this->tag = $label[ wfMsg('browsepathways-all-tags') ];
		}

		// Follwing bit copy-pasta from Pager's IndexPager with some bits replace
		// so we don't rely on $wgRequest in the constructor
		global $wgUser;

		# NB: the offset is quoted, not validated. It is treated as an
		# arbitrary string to support the widest variety of index types. Be
		# careful outputting it into HTML!
		$this->mOffset = $this->getOffset();

		# Use consistent behavior for the limit options
		$this->mDefaultLimit = intval( $wgUser->getOption( 'rclimit' ) );
		$this->mLimit = $this->getLimit();

		$this->mIsBackwards = $this->isBackwards();
		$this->mDb = wfGetDB( DB_SLAVE );

		$index = $this->getIndexField();
		$order = $this->getOrder();
		if( is_array( $index ) && isset( $index[$order] ) ) {
			$this->mOrderType = $order;
			$this->mIndexField = $index[$order];
		} elseif( is_array( $index ) ) {
			# First element is the default
			reset( $index );
			list( $this->mOrderType, $this->mIndexField ) = each( $index );
		} else {
			# $index is not an array
			$this->mOrderType = null;
			$this->mIndexField = $index;
		}

		if( !isset( $this->mDefaultDirection ) ) {
			$dir = $this->getDefaultDirections();
			$this->mDefaultDirection = is_array( $dir )
				? $dir[$this->mOrderType]
				: $dir;
		}
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
		if( $this->species !== '---' ) {
			$q['tables'][] = 'categorylinks';
			$q['join_conds']['categorylinks'] = array( 'JOIN', 'page.page_id=cl_from' );
			$q['conds']['cl_to'] = $this->species;
		}

		return $q;
	}

	function getIndexField() {
		return 't1.tag_text';
		# This should look at $this->sortOrder for the field to sort on.
	}

	function getTopNavigationBar() {
		return parent::getNavigationBar();
	}

	function getBottomNavigationBar() {
		return parent::getNavigationBar();
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

		$label = $pathway->name() . '<br>';
		if( $this->species === '---' ) {
			$label .= "(" . $pathway->species() . ")<br>";
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
		$tagLabel = "<span class='tag-icons'>";
		foreach( $tags as $label => $attr ) {
			$img = wfLocalFile( $attr['img'] );
			$imgLink = Xml::element('img', array( 'src' => $img->getURL(), "title" => $label ));
			$href = $wgRequest->appendQueryArray( array( "tag" => $attr['tag'] ) );
			$tagLabel .= Xml::element('a', array( 'href' => $href ), null ) . $imgLink . "</a>";
		}
		$tagLabel .= "</span>";
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
	protected $columnItemCount;
	protected $columnIndex;
	const columnSize = 100;
	const columnCount = 3;

	function __construct( $species, $tag ) {
		parent::__construct( $species, $tag );

		# We know we have 75, so we'll put 25 in each column
		$this->mLimitsShown = array( self::columnSize * self::columnCount );
		$this->mDefaultLimit = self::columnSize * self::columnCount;
		$this->mLimit = self::columnSize * self::columnCount;
		$this->columnItemCount = 0;
		$this->columnIndex = 0;
	}

	function getStartBody() {
		return "<ul id='browseListBody'>";
	}

	function getEndBody() {
		return "</ul>";
	}

	function getTopNavigationBar() {
		return "";
	}

	function getBottomNavigationBar() {
		global $wgLang;

		/* Using http://imakewebthings.com/jquery-waypoints/shortcuts/infinite-scroll/ */
		$link = "";
		$queries = $this->getPagingQueries();
		$opts = array( 'parsemag', 'escapenoentities' );

		if( isset( $queries['next'] ) && $queries['next'] ) {
			$link = $this->getSkin()->makeKnownLinkObj( $this->getTitle(),
				wfMsgExt( 'nextn', $opts, $wgLang->formatNum( $this->mLimit ) ),
				wfArrayToCGI( $queries['next'], $this->getDefaultQuery() ), '', '',
				"class='infinite-more-link'" );
		}
		return $link;
	}


	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		if( $this->columnItemCount === self::columnSize ) {
			$row = '</ul></li>';
			$this->columnItemCount = 0;
			$this->columnIndex++;
		} else {
			$row = "";
		}

		if( $this->columnItemCount === 0 ) {
			$row .= '<li class="infinite-item"><ul>';
		}
		$this->columnItemCount++;

		$row .= '<li><a href="' . $title->getFullURL() . '">' . $pathway->getName();

		if( $this->species === '---' ) {
			$row .= " (". $pathway->getSpeciesAbbr() . ")";
		}

		return  "$row</a>" . $this->formatTags( $title ) . "</li>";
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
