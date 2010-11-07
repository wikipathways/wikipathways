<?php
require_once("wpi/wpi.php");

class SearchPathways extends SpecialPage
{
	private $this_url;

        function SearchPathways() {
                SpecialPage::SpecialPage("SearchPathways");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut, $wpiScriptURL, $wgUser;
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
	
		if($_GET['doSearch'] == '1') { //Submit button pressed
			$this->showForm($query, $species, $ids, $codes, $type);
			$this->showResults($query, $species, $ids, $codes, $type);
                } else {
			$this->showForm($query, $species, $ids, $codes, $type);
		}
	}


	function showForm($query, $species = '', $ids = '', $codes = '', $type) {
		global $wgRequest, $wgOut, $wpiScriptURL;

                	$form_method = "get";
                	$form_extra = "";
                $search_form =" <FORM action='$this->this_url' method='get'>
				<table cellspacing='7'><tr valign='middle'><td>"
				//<input type='radio' name='type' value='query' CHECKED>Keywords
				//<input type='radio' name='type' value='xref'>Identifiers
				//<tr><td>
				."Search for:
                                <input type='text' name='$type' value='$query' size='25'>
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
				</table></FORM><BR>";

	        $wgOut->addHTML("
                        <DIV id='search' > 
			$search_form
			</DIV>
                        ");
	}

        function showResults($query, $species = '', $ids = '', $codes = '', $type) {
                global $wgRequest, $wgOut, $wpiScriptURL;

                $client = new SoapClient('http://www.wikipathways.org/wpi/webservice/webservice.php?wsdl');
		$results;

		if($type == 'query'){
			$results = $client->findPathwaysByText(array('query'=>$query, 'species'=>$species));
		} elseif ($type == 'xref'){
			$results = $client->findPathwaysByXref(array('ids'=>$ids, 'codes'=>$codes));
		}

                if(!$results->result){
                        $wgOut->addHTML("<b>No Results</b>");
                        return;
                }
		//print_r($results);

		$count = count($results->result);	
		$wgOut->addHTML("<b>$count pathways found</b>");

		$maxheight = 0;
		foreach ($results->result as $resObj){
			if (!is_array($results->result))
				$pwid = $results->result->id;
			else 
				$pwid = $resObj->id;
		   	$pathway = new Pathway($pwid);
			$name = $pathway->name();
			$species = $pathway->getSpecies();
    			$href = $pathway->getFullUrl();
        		$caption = "<a href=\"$href\">$name ($species)</a>";
        		$caption = html_entity_decode($caption);         //This can be quite dangerous (injection)
    			$output = $this->makeThumbNail($pathway, $caption, $href, '', 'left', 'thumb', 200);
                        $pwArray[$href] = strtoupper(substr($name,0,1)) . substr($name,1) . " |-| " . $output;
			preg_match('/height="(\d+)"/', $output, $matches);
			$height = $matches[1] + 60;
			if ($height > $maxheight) $maxheight = $height;
           	}
                if(count($pwArray)>0)
                {
                        $resultArray = "<table cellspacing='5' cellpadding='5'><tbody><td>";
			$count = 1;
                        foreach($pwArray as $url=>$pwTitle)
                        {
                            $pwTitle = substr($pwTitle, strpos($pwTitle,"|-|")+ 3);
			    $resultArray .= "<div style='float:left; vertical-align:bottom;width:220px;height:".$maxheight."px'>$pwTitle</div>";
                        }
                        $resultArray .= "</td></tbody></table>";
               	}

	  	$wgOut->addHTML("$resultArray");
	}

	function makeThumbNail( $pathway, $label = '', $href = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 300, $boxheight=false, $framed=false ) {
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
