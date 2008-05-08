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
 * Insert arbitrary images as thumbnail links to any SPECIAL, PATHWAY or MAIN page.
 * Parameters: image filename, display width, horizonal alignment, caption, namespace (special, pathway or main (default)), species (used for pathways), page title, tooltip.
 * Usage: Special page example: {{#imgLink:Wishlist_thumb_200.jpg|200|center|Wish list page|special||SpecialWishList|Wish list}}
 *        Pathway page example: {{#imgLink:Sandbox_thumb_200.jpg|200|center|Sandbox page|pathway|Human|Sandbox|Sandbox}}
 *        Main page example: {{#imgLink:Download_all_thumb_200.jpg|200|center|Download page|||Download_Pathways|Download pathways}}
 */
function renderImageLink( &$parser, $img, $width = 200, $align = '', $caption = '', $namespace = '', $species = '', $pagetitle = '', $tooltip = '', $id='imglink') {      
	global $wgUser;
	$parser->disableCache();
      try {
		
		$caption = html_entity_decode($caption);        //This can be quite dangerous (injection),
                                                                //we would rather parse wikitext, let me know if
                                                                //you know a way to do that (TK)

                $output = makeImageLinkObj($img, $caption, $namespace, $species, $pagetitle, $tooltip, $align, $id, $width);

        } catch(Exception $e) {
                return "invalid image link: $e";
        }
        return array($output, 'isHTML'=>1, 'noparse'=>1);
}


    /** MODIFIED FROM Linker.php
        * Make HTML for a thumbnail including image, border and caption
        * $img is an Image object
        */
    function makeImageLinkObj( $img, $label = '', $namespace = '', $species = '', $pagetitle = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 180, $boxheight=false, $framed=false ) {
            global $wgStylePath, $wgContLang;

            $img = new Image(Title::makeTitleSafe( NS_IMAGE, $img ));
            $imgURL = $img->getURL();
		
            $href = '';

	    switch($namespace){
		case 'special':
			$href = Title::newFromText($pagetitle, NS_SPECIAL)->getFullUrl();
			break;
		case 'pathway':
			$href = Title::newFromText($species . ':' . $pagetitle, NS_PATHWAY)->getFullUrl();
			break;
		default:
			$href = Title::newFromText($pagetitle, NS_MAIN)->getFullUrl();
			break;
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

?>
