<?php
$wgExtensionFunctions[] = 'wfImageLink';
$wgHooks['LanguageGetMagic'][]  = 'wfImageLink_Magic';

function wfImageLink() {
	global $wgParser;
	$wgParser->setFunctionHook( "imgLink", "renderImageLink" );
}

function wfImageLink_Magic( &$magicWords, $langCode ) {
	$magicWords['imgLink'] = array( 0, 'imgLink' );
	return true;
}

/** Modifies from pathwayThumb.php
 * Insert arbitrary images as thumbnail links to any SPECIAL, PATHWAY, HELP or MAIN page, or external link.
 * Parameters: image filename, display width, horizonal alignment, caption, namespace (special, pathway, main (default), or external), page title (stable id for pathways, e.g., WP274), tooltip.
 * Usage: Special page example: {{#imgLink:Wishlist_thumb_200.jpg|200|center|Wish list page|special|SpecialWishList|Wish list}}
 *        Pathway page example: {{#imgLink:Sandbox_thumb_200.jpg|200|center|Sandbox page|pathway|WP274|Sandbox}}
 *        Main page example: {{#imgLink:Download_all_thumb_200.jpg|200|center|Download page||Download_Pathways|Download pathways}}
 * 	  External link example: {{#imgLink:WikiPathwaysSearch2.png|200|center||Help|{{FULLPAGENAME}}/WikiPathwaysSearch|Search}}
 */
function renderImageLink( &$parser, $img, $width = 200, $align = '', $caption = '', $namespace = '', $pagetitle = '', $tooltip = '', $id='imglink') {
	global $wgUser;
	$parser->disableCache();
	try {

		$caption = html_entity_decode($caption);        //This can be quite dangerous (injection),
		//we would rather parse wikitext, let me know if
		//you know a way to do that (TK)

		$output = makeImageLinkObj($img, $caption, $namespace, $pagetitle, $tooltip, $align, $id, $width);

	} catch(Exception $e) {
		return "invalid image link: $e";
	}
	return array($output, 'isHTML'=>1, 'noparse'=>1);
}


/** MODIFIED FROM Linker.php
 * Make HTML for a thumbnail including image, border and caption
 * $img is an Image object
 */
function makeImageLinkObj( $img, $label = '', $namespace = '', $pagetitle = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 180, $boxheight=false, $framed=false ) {
	global $wgStylePath, $wgContLang;

	$img = new LocalFile(Title::makeTitleSafe( NS_IMAGE, $img ), RepoGroup::singleton()->getLocalRepo());
	$imgURL = $img->getURL();

	$href = '';

	switch($namespace){
		case 'special':
			$title = Title::newFromText($pagetitle, NS_SPECIAL);
			if( $title ) {
				$href = $title->getFullUrl();
			}
			break;
		case 'pathway':
			$title = Title::newFromText($pagetitle, NS_PATHWAY);
			if( $title ) {
				$href = $title->getFullUrl();
			}
			break;
		case 'help':
			$title = Title::newFromText($pagetitle, NS_HELP);
			if( $title ) {
				$href = $title->getFullUrl();
			}
			break;
		case 'external':
			$href = $pagetitle;
			break;
		default:
			$title = Title::newFromText($pagetitle, NS_MAIN);
			if( $title ) {
				$href = $title->getFullUrl();
			}
	}

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
	if ( $boxwidth == 0 ) {
		$boxwidth = 180;
	}
	if ( $framed ) {
		// Use image dimensions, don't scale
		$boxwidth  = $width;
		$boxheight = $height;
		$thumbUrl  = $img->getViewURL();
	} else {
		if ( $boxheight === false ) $boxheight = -1;
		$thumb = $img->getThumbnail( $boxwidth, $boxheight );
		if ( $thumb ) {
			$thumbUrl = $thumb->getUrl();
			$boxwidth = $thumb->width;
			$boxheight = $thumb->height;
		} else {
			$error = $img->getLastError();
		}
	}
	$oboxwidth = $boxwidth + 2;

	$more = htmlspecialchars( wfMsg( 'thumbnail-more' ) );
	$magnifyalign = $wgContLang->isRTL() ? 'left' : 'right';
	$textalign = $wgContLang->isRTL() ? ' style="text-align:right"' : '';

	$s = "<div id=\"{$id}\" class=\"thumb t{$align}\"><div class=\"thumbinner\" style=\"width:{$oboxwidth}px;\">";
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
	} elseif( $href === "" ) {
		$s .= "Title error";
	} else {
		$s .= '<a href="'.$href.'" class="internal" title="'.$alt.'">'.
			'<img src="'.$thumbUrl.'" alt="'.$alt.'" ' .
			'width="'.$boxwidth.'" height="'.$boxheight.'" ' .
			'longdesc="'.$href.'" class="thumbimage" /></a>';
	}
	$s .= '  <div class="thumbcaption"'.$textalign.'>'.$label."</div></div></div>";
	return str_replace("\n", ' ', $s);
	//return $s;
}
