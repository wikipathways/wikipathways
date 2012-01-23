<?php
$wgExtensionFunctions[] = 'wfPathwayThumb';
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayThumb_Magic';

function wfPathwayThumb() {
    global $wgParser;
    $wgParser->setFunctionHook( "pwImage", "renderPathwayImage" );
}

function wfPathwayThumb_Magic( &$magicWords, $langCode ) {
        $magicWords['pwImage'] = array( 0, 'pwImage' );
        return true;
}

function renderPathwayImage( &$parser, $pwTitleEncoded, $width = 0, $align = '', $caption = '', $href = '', $tooltip = '', $id='pwthumb') {      
	global $wgUser;
	$pwTitle = urldecode ($pwTitleEncoded);
	$parser->disableCache();
      try {
                $pathway = Pathway::newFromTitle($pwTitle);
                $revision = $_REQUEST['oldid'];
                if($revision) {
                	$pathway->setActiveRevision($revision);
                }
                $img = RepoGroup::singleton()->getLocalRepo()->newFile($pathway->getFileTitle(FILETYPE_IMG));
                //$img = new Image($pathway->getFileTitle(FILETYPE_IMG));
                switch($href) {
                        case 'svg':
                                $href = Image::imageUrl($pathway->getFileTitle(FILETYPE_IMG)->getPartialURL());
                                break;
                        case 'pathway':
                                $href = $pathway->getFullURL();
                                break;
                        default:
                                if(!$href) $href = $pathway->getFullURL();
                }
		
		switch($caption) {
			case 'edit':
				$caption = createEditCaption($pathway);
			break;
			case 'view':
				$caption = $pathway->name() . " (" . $pathway->species() . ")";
			break;
			default:
			$caption = html_entity_decode($caption);        //This can be quite dangerous (injection),
                                                                //we would rather parse wikitext, let me know if
                                                                //you know a way to do that (TK)
		}

                $output = makeThumbLinkObj($pathway, $caption, $href, $tooltip, $align, $id, $width);

        } catch(Exception $e) {
                return "invalid pathway title: $e";
        }
        return array($output, 'isHTML'=>1, 'noparse'=>1);
}

function createEditCaption($pathway) {
	global $wgUser;
	
	//Create edit button
	$pathwayURL = $pathway->getTitleObject()->getPrefixedURL();
	//AP20070918
	if (!$wgUser->isLoggedIn()){
		$hrefbtn = SITE_URL . "/index.php?title=Special:Userlogin&returnto=$pathwayURL";
		$label = "Log in to edit pathway";
	} else {
		if(wfReadOnly()) {
			$hrefbtn = "";
			$label = "Database locked";				
		} else if(!$pathway->getTitleObject()->userCan('edit')) {
			$hrefbtn = "";
			$label = "Editing is disabled";
		} else {
			$hrefbtn = "javascript:;";
			$label = "Edit pathway";
		}
	}
	$helpUrl = Title::newFromText("Help:Known_problems")->getFullUrl();
	$caption = "<a href='$hrefbtn' title='$label' id='edit' ". 
				"class='button'><span>$label</span></a>" .
				"<div style='float:left;'><a href='$helpUrl'> not working?</a></div>";
				
	//Create dropdown action menu
	$pwTitle = $pathway->getTitleObject()->getFullText();
	//disable dropdown for now
	$drop = PathwayPage::editDropDown($pathway);
	$drop = '<div style="float:right;">' . $drop . '</div>';
	return $caption . $drop;
}


    /** MODIFIED FROM Linker.php
        * Make HTML for a thumbnail including image, border and caption
        * $img is an Image object
        */
    function makeThumbLinkObj( $pathway, $label = '', $href = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 180, $boxheight=false, $framed=false ) {
            global $wgStylePath, $wgContLang;

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
