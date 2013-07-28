<?php

abstract class BasePathwaysPager extends AlphabeticPager {
	protected $species;
	protected $tag;
	protected $sortOrder;
	protected $ns = NS_PATHWAY;
	protected $nsName;

	public function thumbToData( $thumb ) {
		$data = "";
		/* FIXME: magic nums for file size and width */
		$suffix = $thumb->thumbName( array( "width" => 180 ) );
		$thumbnail = $thumb->getThumbPath( $suffix );

		if( $thumb->isLocal() && file_exists( $thumbnail )
			&& filesize( $thumbnail ) < 20480 ) { /* 20k is probably too much */
			$c = file_get_contents( $thumbnail );
			list( $thumbExt, $thumbMime ) = $thumb->handler->getThumbType( $thumb->getExtension(), $thumb->getMimeType() );
			return "data:" . $thumbMime . ";base64," . base64_encode( $c );
		}
		return $thumb->getThumbUrl( $suffix );
	}

	public function imgToData( $img ) {
		$data = "";
		/* FIXME: magic nums for file size */
		$path = $img->getPath( );

		if( $img->isLocal() && file_exists( $path )
			&& filesize( $path ) < 20480 ) { /* 20k is probably too much */
			$c = file_get_contents( $path );
			return "data:" . $img->getMimeType() . ";base64," . base64_encode( $c );
		}
		return $thumb->getThumbUrl( $suffix );
	}

	public function hasRecentEdit( $title ) {
		global $wgPathwayRecentSinceDays;
		$article = new Article( $title );

		$ts = wfTimeStamp( TS_UNIX, $article->getTimestamp() );
		$prev = date_create( "now" );
		$prev->modify( "-$wgPathwayRecentSinceDays days" );
		$date = date_create( "@$ts" ); /* @ indicates we have a unix timestmp */

		return $date > $prev;
	}

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

	public function __construct( $species = "---", $tag = "---", $sortOrder = 0 ) {
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
			$species = preg_replace( "/_/", " ", $this->species );
			$q['tables'][] = 'tag as t2';
			$q['join_conds']['tag as t2'] = array( 'JOIN', 't2.page_id = page.page_id' );
			$q['conds']['t2.tag_text'] = $species;
		}

		return $q;
	}

	function getIndexField() {
		return 't1.tag_text';
		# This should look at $this->sortOrder for the field to sort on.
	}

	function getTopNavigationBar() {
		return $this->getNavigationBar();
	}

	function getBottomNavigationBar() {
		return $this->getNavigationBar();
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

		$label = $pathway->name() . '<br/>';
		if( $this->species === '---' ) {
			$label .= "(" . $pathway->species() . ")<br/>";
		}
		$label .= $icons;

		$boxheight=-1;
		$framed=false;
		$href = $pathway->getFullURL();
		$class = "browsePathways infinite-item";
		$id = $pathway->getTitleObject();
		$textalign = $wgContLang->isRTL() ? ' style="text-align:right"' : '';
		$oboxwidth = $boxwidth + 2;
		$s = "<div id=\"{$id}\" class=\"{$class}\"><div class=\"thumbinner\" style=\"width:{$oboxwidth}px;\">".
			'<a href="'.$href.'" class="internal">';

		$link = "";
		$img = $pathway->getImage();

		if ( !$img->exists() ) {
			$s .= "Image does not exist";
		} else {
			$imgURL = $img->getURL();

			$thumbUrl = '';
			$error = '';

			$width  = $img->getWidth();
			$height = $img->getHeight();

			$thumb = $img->getThumbnail( $boxwidth, $boxheight );
			if ( $thumb ) {
				$thumbUrl = $this->thumbToData($img);
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
			$imgLink = Xml::element('img', array( 'src' => $this->imgToData( $img ), "title" => $label ));
			$href = $wgRequest->appendQueryArray( array( "tag" => $attr['tag'] ) );
			$tagLabel .= Xml::element('a', array( 'href' => $href ), null ) . $imgLink . "</a>";
		}
		$tagLabel .= "</span>";
		return $tagLabel;
	}
}

class PathwaysPagerFactory {
	static function get( $type, $species, $tag, $sortOrder ) {
		switch( $type ) {
			case 'list':
				return new ListPathwaysPager( $species, $tag, $sortOrder );
				break;
			case 'single':
				return new SinglePathwaysPager( $species, $tag, $sortOrder );
				break;
			default:
				return new ThumbPathwaysPager( $species, $tag, $sortOrder );
		}
	}
}

class ListPathwaysPager extends BasePathwaysPager {
	protected $columnItemCount;
	protected $columnIndex;
	protected $columnSize = 100;
	const columnCount = 3;

	function __construct( $species, $tag, $sortOrder ) {
		parent::__construct( $species, $tag, $sortOrder );

		# We know we have 300, so we'll put 100 in each column
		$this->mLimitsShown = array( $this->columnSize * self::columnCount );
		$this->mDefaultLimit = $this->columnSize * self::columnCount;
		$this->mLimit = $this->columnSize * self::columnCount;
		$this->columnItemCount = 0;
		$this->columnIndex = 0;
	}

	function preprocessResults( $result ) {
		$rows = $result->db->numRows( $result );

		if( $rows < $this->mLimit ) {
			$this->columnSize = (int)( $rows / self::columnCount );
		}
	}

	function getStartBody() {
		return "<ul id='browseListBody'>";
	}

	function getEndBody() {
		return "</ul></li> <!-- end of column --></ul> <!-- getEndBody -->";
	}

	function getNavigationBar() {
		global $wgLang;

		$link = "";
		$queries = $this->getPagingQueries();
		$opts = array( 'parsemag', 'escapenoentities' );

		if( isset( $queries['prev'] ) && $queries['prev'] ) {
			$link .= $this->getSkin()->makeKnownLinkObj( $this->getTitle(),
				wfMsgExt( 'prevn', $opts, $wgLang->formatNum( $this->mLimit ) ),
				wfArrayToCGI( $queries['prev'], $this->getDefaultQuery() ), '', '',
				"style='float: left;'" );
		}

		if( isset( $queries['next'] ) && $queries['next'] ) {
			$link .= $this->getSkin()->makeKnownLinkObj( $this->getTitle(),
				wfMsgExt( 'nextn', $opts, $wgLang->formatNum( $this->mLimit ) ),
				wfArrayToCGI( $queries['next'], $this->getDefaultQuery() ), '', '',
				"style='float: right;'" );
		}

		return $link;
	}

	function getTopNavigationBar() {
		$bar = $this->getNavigationBar();

		return "<div class='listNavBar top'>$bar</div>";
	}

	function getBottomNavigationBar() {
		$bar = $this->getNavigationBar();

		return "<div class='listNavBar bottom'>$bar</div>";
	}

	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		if( $this->columnItemCount === $this->columnSize ) {
			$row = '</ul></li> <!-- end of column -->';
			$this->columnItemCount = 0;
			$this->columnIndex++;
		} else {
			$row = "";
		}

		if( $this->columnItemCount === 0 ) {
			$row .= '<li><ul> <!-- start of column -->';
		}
		$this->columnItemCount++;

		$endRow = "</li>";
		$row .= "<li>";
		if( $this->hasRecentEdit( $title ) ) {
			$row .= "<b>";
			$endRow = "</b></li>";
		}

		$row .= '<a href="' . $title->getFullURL() . '">' . $pathway->getName();

		if( $this->species === '---' ) {
			$row .= " (". $pathway->getSpeciesAbbr() . ")";
		}

		return  "$row</a>" . $this->formatTags( $title ) . $endRow;
	}
}

class ThumbPathwaysPager extends BasePathwaysPager {

	function __construct( $species, $tag, $sortOrder ) {
		parent::__construct( $species, $tag, $sortOrder );

		$this->mLimit = 10;
	}

	function getStartBody() {
		return "<div class='infinite-container'>";
	}

	function getEndBody() {
		return "</div>";
	}

	function getNavigationBar() {
		global $wgLang;

		/* Link to nowhere by default */
		$link = "<a class='infinite-more-link' href='data:'></a>";

		$queries = $this->getPagingQueries();
		$opts = array( 'parsemag', 'escapenoentities' );

		if( isset( $queries['next'] ) && $queries['next'] ) {
			$link = $this->getSkin()->makeKnownLinkObj( $this->getTitle(),
				wfMsgExt( 'nextn', $opts, $wgLang->formatNum( $this->mLimit ) ),
				wfArrayToCGI( $queries['next'], $this->getDefaultQuery() ), '', '',
				"class='infinite-more-link'" );
		}

		return $link;;
	}

	function getTopNavigationBar() {
		return "";
	}

	function getBottomNavigationBar() {
		return $this->getNavigationBar();
	}

	/* From getDownloadURL in PathwayPage */
	function formatRow( $row ) {
		$title = Title::newFromDBkey( $this->nsName .":". $row->page_title );
		$pathway = Pathway::newFromTitle( $title );

		$endRow = "";
		$row = "";
		if( $this->hasRecentEdit( $title ) ) {
			$row = "<b>";
			$endRow = "</b>";
		}

		return $row.$this->getThumb( $pathway, $this->formatTags( $title ) ).$endRow;
	}
}

class SinglePathwaysPager extends BasePathwaysPager {
	function __construct( $species, $tag, $sortOrder ) {
		parent::__construct( $species, $tag, $sortOrder );

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
