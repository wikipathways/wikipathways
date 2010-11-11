<?php
require_once("wpi/wpi.php");
require_once("wpi/search.php");

class SearchPathways extends SpecialPage
{
	private $this_url;

        function SearchPathways() {
                SpecialPage::SpecialPage("SearchPathways");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut, $wpiScriptURL, $wgUser, $wfSearchPagePath;
                
                $this->setHeaders();
		$this->this_url = SITE_URL . '/index.php';
 
		$query = $_GET['query'];
		$species = $_GET['species'];
                $ids = $_GET['ids'];
		$codes = $_GET['codes'];
		$type = $_GET['type'];
		
		// SET DEFAULTS
		if (!$type || $type == '') $type = 'query';
                if ((!$query || $query =='') && $type == 'query') $query = 'glucose';
		if ($species == 'ALL SPECIES') $species = '';
	
		//Add CSS
		//Hack to add a css that's not in the skins directory
		global $wgStylePath;
		$oldStylePath = $wgStylePath;
		$wgStylePath = $wfSearchPagePath;
		$wgOut->addStyle("SearchPathways.css");
		$wgStylePath = $oldStylePath;
	
		if($_GET['doSearch'] == '1') { //Submit button pressed
			$this->showForm($query, $species, $ids, $codes, $type);
			try {
				$this->showResults();
			} catch(Exception $e) {
				$wgOut->addHTML("<b>Error: {$e->getMessage()}</b>");
				$wgOut->addHTML("<pre>$e</pre>");
			}
                } else {
			$this->showForm($query, $species, $ids, $codes, $type);
		}
	}


	function showForm($query, $species = '', $ids = '', $codes = '', $type) {
		global $wgRequest, $wgOut, $wpiScriptURL, $wgJsMimeType, $wfSearchPagePath, $wgScriptPath;
				#For now, hide the form when id search is done (no gui for that yet)
				$hide = "";
				$xrefInfo = "";
				if($type != 'query') {
					$hide = "style='display:none'";
					$xrefs = SearchPathwaysAjax::parToXref($ids, $codes);
					$xrefInfo = "Pathways by idenifier: ";
					$xstr = array();
					foreach($xrefs as $x)	$xstr[] = "{$x->getId()} ({$x->getSystem()})";
					$xrefInfo .= implode(", ", $xstr);
					$xrefInfo = "<P>$xrefInfo</P>";
				}
				
      	$form_method = "get";
      	$form_extra = "";
        $search_form ="$xrefInfo<FORM $hide id='searchForm' action='javascript:SearchPathways.doSearch();' method='get'>
				<table cellspacing='7'><tr valign='middle'><td>"
				//<input type='radio' name='type' value='query' CHECKED>Keywords
				//<input type='radio' name='type' value='xref'>Identifiers
				//<tr><td>
				."Search for:
                                <input type='text' name='query' value='$query' size='25'>
				</td><td><select name='species'>";
                $allSpecies = Pathway::getAvailableSpecies();
		$search_form .= "<option value='ALL SPECIES'" . ($species == '' ? ' SELECTED' : ''). ">ALL SPECIES";
                foreach($allSpecies as $sp) {
                        $search_form .= "<option value='$sp'" . ($sp == $species ? ' SELECTED' : '') . ">$sp";
                }
                $search_form .= '</select>';
                $search_form .= "<input type='hidden' name='title' value='Special:SearchPathways'>
				<input type='hidden' name='doSearch' value='1'>
				</td><td><input type='submit' value='Search'></td></tr> 
				<tr valign='top'><td colspan='3'><font size='-3'><i>&nbsp;&nbsp;&nbsp;Tip: use AND, OR, *, ?, parentheses or quotes</i></font></td></tr>
				</table>";
				
				$search_form .= "<input type='hidden' name='ids' value='$ids'/>";
				$search_form .= "<input type='hidden' name='codes' value='$codes'/>";
				$search_form .= "<input type='hidden' name='type' value='$type'/>";
				
				$search_form .= "</FORM><BR>";

	        $wgOut->addHTML("
                        <DIV id='search' > 
			$search_form
			</DIV>
                        ");
		$wgOut->addScript("<script type=\"{$wgJsMimeType}\" src=\"$wfSearchPagePath/SearchPathways.js\"></script>\n");
		$wgOut->addHTML("<DIV id='searchResults'></DIV>");
		$wgOut->addHTML(
			"<DIV id='loading'><IMG src='$wgScriptPath/skins/common/images/progress.gif'/> Loading...</DIV>"
		);
		$wgOut->addHTML("<DIV id='more'></DIV>");
		$wgOut->addHTML("</DIV><DIV id='error'></DIV>");
	}

	function showResults() {
		global $wgOut, $wgJsMimeType;
		
		$wgOut->addHTML(
			"<script type=\"{$wgJsMimeType}\">" .
			"SearchPathways.doSearch();" .
			"</script>\n"
		);
	}
	
        function showResults_old($query, $species = '', $ids = '', $codes = '', $type) {
                global $wgRequest, $wgOut, $wpiScriptURL;

					if($type == 'query'){
						$results = PathwayIndex::searchByText($query, $species);
					} elseif ($type == 'xref'){
							$xrefs = explode(',', $ids);
							$codes = explode(',', $codes);
							if(count($xrefs) > count($codes)) $singleCode = $codes[0];
							$objects = array();
							for($i = 0; $i < count($ids); $i += 1) {
								if($singleCode) $c = $singleCode;
								else $c = $codes[$i];
								$x = new XRef($ids[$i], $c);
								$xrefs[] = $x;
							}
							$results = PathwayIndex::searchByXref($xrefs, true);
					}
						
			if(count($results) == 0){
				$wgOut->addHTML("<b>No Results</b>");
				return;
			}
		//print_r($results);

		$count = count($results);	
		$wgOut->addHTML("<b>$count pathways found</b>");

		foreach ($results as $resObj){
		   	$pathway = $resObj->getPathway();
			$name = $pathway->name();
			$species = $pathway->getSpecies();
    			$href = $pathway->getFullUrl();
        		$caption = "<a href=\"$href\">$name ($species)</a>";
        		$caption = html_entity_decode($caption);         //This can be quite dangerous (injection)
    			$output = $this->makeThumbNail($pathway, $caption, $href, '', 'left', 'thumb', 200);
			preg_match('/height="(\d+)"/', $output, $matches);
			$height = $matches[1];
			if ($height > 160){
				$output = preg_replace('/height="(\d+)"/', 'height="160px"', $output);
			}
			$pwArray[$href] = strtoupper(substr($name,0,1)) . substr($name,1) . " |-| " . $output;
           	}
                if(count($pwArray)>0)
                {
                        $resultArray = "<table cellspacing='5' cellpadding='5'><tbody><td>";
			$count = 1;
                        foreach($pwArray as $url=>$pwTitle)
                        {
                            $pwTitle = substr($pwTitle, strpos($pwTitle,"|-|")+ 3);
			    $resultArray .= "<div style='float:left; vertical-align:bottom;width:220px;height:220px'>$pwTitle</div>";
                        }
                        $resultArray .= "</td></tbody></table>";
               	}

	  	$wgOut->addHTML("$resultArray");
	}

	static function makeThumbNail( $pathway, $label = '', $href = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 300, $boxheight=false, $framed=false ) {
            global $wgStylePath, $wgContLang;

	try {
            	$pathway->updateCache(FILETYPE_IMG);
	        $img = new Image($pathway->getFileTitle(FILETYPE_IMG));
        	$img->loadFromFile();
              	$imgURL = $img->getURL();
	} catch (Exception $e) {
		$blank = "<div id=\"{$id}\" class=\"thumb t{$align}\"><div class=\"thumbinner\" style=\"width:200px;\">";
		$blank .= "Image does not exist";
		$blank .= '  <div class="thumbcaption" style="text-align:right">'.$label."</div></div></div>";
            	return str_replace("\n", ' ', $blank);
	}	

            $thumbUrl = '';
            $error = '';

            $width = $height = 0;
            if ( $img->exists() ) {
                    $width  = $img->getWidth();
                    $height = $img->getHeight();
            }
            if ( 0 == $width || 0 == $height ) {
                    $width = $height = 220;
            }
            if ( $boxwidth == 0 ) {
                    $boxwidth = 230;
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
    	}


        function loadMessages() {
                static $messagesLoaded = false;
                global $wgMessageCache;
                if ( $messagesLoaded ) return true;
                $messagesLoaded = true;

                require( dirname( __FILE__ ) . '/SearchPathways.i18n.php' );
                foreach ( $allMessages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
                return true;
        }
}
?>
