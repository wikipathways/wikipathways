<?php

require_once('../otag/OntologyFunctions.php');

$ontology_id = $_GET['ontology_id'];
$concept_id = $_GET['concept_id'];

$xml = "";
$res_array = array();

switch($_GET['action'])
{
    case 'tree':
        fetch_tree();
        break;
    case 'species':
        fetch_species();
        break;
    case 'list':
        fetchPathwayList(false);
        break;
    case 'image':
        fetchPathwayList(true);
        break;
}


function fetchPathwayList($imageMode)
 { global $wgOut;

    $term = $_GET['term'];
    switch($_GET['filter'])
        {
            case 'All' :
                {
                    $pathways = Pathway::getAllPathways();

                    foreach($pathways as $p) {
                        if($p->isDeleted()) continue;
                        if($p->getSpecies() != $_GET['species']  && $_GET['species'] != "All Species")
                        continue;
                        if($term!="")
                        {
                            $title = $p->getTitleObject()->getDbKey();
                            $check = 0;
                            $dbr =& wfGetDB(DB_SLAVE);
                            $sql = "SELECT * FROM ontology where (`term_id` = '$term' OR `term_path` LIKE '%$term.%')   AND (`pw_id` = '$title')";
                            $res = $dbr->query($sql);
                            while($row = $dbr->fetchObject($res))
                            {
                                $check++;
                            }
                            if($check == 0)
                            continue;
                        }
                        $pwName = $p->name();
                        $pwUrl = "<a href='" . $p->getFullUrl() . "' >" . $pwName . "</a>";
                        $display = ($imageMode)?process($p->getTitleObject()->getDbKey(), $pwUrl):$pwUrl;
			if($imageMode) {
	                        preg_match('/height="(\d+)"/', $display, $matches);
	                        $height = $matches[1];
	                        if ($height > 200){
	                                $display = preg_replace('/height="(\d+)"/', 'height="200px"', $display);
	                        }
			}
                        $pwArray[$p->getFullUrl()] = strtoupper(substr($pwName,0,1)) . substr($pwName,1) . " |-| " . $display;
                    }
                    if(count($pwArray)>0)
                    {
                        asort($pwArray,SORT_STRING);
                        $resultTable = '<table width="100%" ><tbody>';
			if ($imageMode) $resultTable .= '<td>';
                        foreach($pwArray as $url=>$pwTitle)
                        {
                            $pwTitle = substr($pwTitle, strpos($pwTitle,"|-|")+ 3);
			    if ($imageMode){
			    	$resultTable .= "<div style='float:left; vertical-align:bottom;width:220px;height:260px'>$pwTitle</div>";
			    } else {
                            if($count%2 == 0)
                            $resultTable .= "<tr><td width='10%'>&nbsp;</td><td width='35%'>$pwTitle</span></td>";
                        else
                            $resultTable .= "<td width='10%'>&nbsp;</td><td width='35%' align='left'>$pwTitle</td><td width='10%'>&nbsp;</td></tr>";
                            $count++;
			    }
                        }
			if ($imageMode) $resultTable .= '</td>';
                        $resultTable .= "</tbody></table>";
                    }
                    break;
                }
            case 'Edited' :
                {
                    $dbr =& wfGetDB( DB_SLAVE );
                    $sql = "SELECT
                                    'Mostrevisions' as type,
                                    page_namespace as namespace,
                                    page_id as id,
                                    page_title as title,
                                    COUNT(*) as value
                                FROM `revision`
                                JOIN `page` ON page_id = rev_page
                                WHERE page_namespace = 102" . "
                                AND page_is_redirect = 0
                                GROUP BY 1,2,3
                                HAVING COUNT(*) > 1
                                ";
                    $res = $dbr->query($sql);
                    while($row = $dbr->fetchObject($res))
                    {
                        $pathwayArray[$row->title] = $row->value;
                    }

		    arsort($pathwayArray);
                    $resultTable = '<table width="100%" ><tbody>';
                    if ($imageMode) $resultTable .= '<td>';

                    foreach($pathwayArray as $title=>$value )
                    {
                        $p = Pathway::newFromTitle($title);
                        if($p->isDeleted()) continue;
                        if($p->getSpecies() != $_GET['species']  && $_GET['species'] != "All Species")
                        continue;
                        if($term!="")
                        {
                            $check = 0;
                            $sql = "SELECT * FROM ontology where (`term_id` = '$term' OR `term_path` LIKE '%$term.%' OR `term_path` LIKE '%$term') AND (`pw_id` = '$title')";
                            $res = $dbr->query($sql);
                            while($result = $dbr->fetchObject($res))
                            {
                                $check++;
                            }
                            if($check == 0)
                            continue;
                        }
                        $pwUrl = "<a href='{$p->getFullUrl()}'>{$p->name()}</a><br /> (" . $value ." Revisions)";
                        $display = ($imageMode)?process($title, $pwUrl):$pwUrl;
                        if($imageMode) {
	                        preg_match('/height="(\d+)"/', $display, $matches);
	                        $height = $matches[1];
	                        if ($height > 160){
	                                $display = preg_replace('/height="(\d+)"/', 'height="160px"', $display);
	                        }
                        }
                        if ($imageMode){
                            $resultTable .= "<div style='float:left; vertical-align:bottom;width:220px;height:220px'>$display</div>";
                        } else {
                        if($count%2 == 0)
                            $resultTable .= "<tr><td width='10%'>&nbsp;</td><td width='35%'>$display</span></td>";
                        else
                            $resultTable .= "<td width='10%'>&nbsp;</td><td width='35%' align='left'>$display</td><td width='10%'>&nbsp;</td></tr>";
                        $count++;
                        }
		    }
                    if ($imageMode) $resultTable .= '</td>';
                    $resultTable .= "</tbody></table>"; 
                    break;
               }
            case 'Popular':
               {
                    $dbr =& wfGetDB( DB_SLAVE );
            		$page = $dbr->tableName( 'page' );
                    $sql =  "SELECT 'Popularpages' as type,
                            page_namespace as namespace,
                            page_title as title,
                            page_id as id,
                            page_counter as value
                            FROM $page
                            WHERE page_namespace=".NS_PATHWAY."
                            AND page_is_redirect=0";
                            $res = $dbr->query($sql);
                            while($row = $dbr->fetchObject($res))
                            {
                                $pathwayArray[$row->title] = $row->value;
                            }

	                    arsort($pathwayArray);
	                    $resultTable = '<table width="100%" ><tbody>';
	                    if ($imageMode) $resultTable .= '<td>';

                            foreach($pathwayArray as $title=>$value )
                            {
                                $p = Pathway::newFromTitle($title);
                                if($p->isDeleted()) continue;
                                if($p->getSpecies() != $_GET['species']  && $_GET['species'] != "All Species")
                                continue;
                                if($term!="")
                                {
                                    $title = $p->getTitleObject()->getDbKey();
                                    $check = 0;
                                    $sql = "SELECT * FROM ontology where (`term_id` = '$term' OR `term_path` LIKE '%$term.%' OR `term_path` LIKE '%$term') AND (`pw_id` = '$title')";
                                    $res = $dbr->query($sql);
                                    while($result = $dbr->fetchObject($res))
                                    {
                                        $check++;
                                    }
                                    if($check == 0)
                                    continue;
                                }
                                $pwUrl = "<a href='{$p->getFullUrl()}'>{$p->name()}</a><br /> (" . $value ." Views)";
                                $display = ($imageMode)?process($title, $pwUrl):$pwUrl;
                        	if($imageMode) {
                                preg_match('/height="(\d+)"/', $display, $matches);
                                $height = $matches[1];
                                if ($height > 160){
                                        $display = preg_replace('/height="(\d+)"/', 'height="160px"', $display);
                                }
                        }
                        if ($imageMode){
                            $resultTable .= "<div style='float:left; vertical-align:bottom;width:220px;height:220px'>$display</div>";
                        } else {
                        if($count%2 == 0)
                            $resultTable .= "<tr><td width='10%'>&nbsp;</td><td width='35%'>$display</span></td>";
                        else
                            $resultTable .= "<td width='10%'>&nbsp;</td><td width='35%' align='left'>$display</td><td width='10%'>&nbsp;</td></tr>";
                        $count++;
                        }
                    }
                    if ($imageMode) $resultTable .= '</td>';
                    $resultTable .= "</tbody></table>";
                    break;
                }
            case 'last_edited':
               {
                    $dbr =& wfGetDB( DB_SLAVE );
            		$page = $dbr->tableName( 'page' );
                    $sql1 = "SELECT page_title as title, page_touched as timestamp  FROM $page WHERE page_namespace=".NS_PATHWAY." AND page_is_redirect=0 ORDER BY `page_touched` DESC";

                    $sql =  "SELECT 'Popularpages' as type,
                            page_namespace as namespace,
                            page_title as title,
                            page_id as id,
                            page_counter as value
                            FROM $page
                            WHERE page_namespace=".NS_PATHWAY."
                            AND page_is_redirect=0 ORDER BY `page_touched` DESC";
                            $res = $dbr->query($sql1);

                            while($row = $dbr->fetchObject($res))
                            {
                                $timestamp = $row->timestamp;
                                $year = substr($timestamp, 0, 4);
                                $month = substr($timestamp, 4, 2);
                                $day = substr($timestamp, 6, 2);
                                $date = date('M d, Y', mktime(0,0,0,$month, $day, $year));
                                $pathwayArray[$row->title] = $date;
                            }

	                    arsort($pathwayArray);
	                    $resultTable = '<table width="100%" ><tbody>';
	                    if ($imageMode) $resultTable .= '<td>';

                            foreach($pathwayArray as $title=>$value )
                            {
                                $p = Pathway::newFromTitle($title);
                                if($p->isDeleted()) continue;
                                if($p->getSpecies() != $_GET['species']  && $_GET['species'] != "All Species")
                                    continue;
                                if($term!="")
                                {
                                    $title = $p->getTitleObject()->getDbKey();
                                    $check = 0;
                                    $sql = "SELECT * FROM ontology where (`term_id` = '$term' OR `term_path` LIKE '%$term.%' OR `term_path` LIKE '%$term') AND (`pw_id` = '$title')";
                                    $res = $dbr->query($sql);
                                    while($result = $dbr->fetchObject($res))
                                    {
                                        $check++;
                                    }
                                    if($check == 0)
                                        continue;
                                }
                                $pwUrl = "<a href='{$p->getFullUrl()}'>{$p->name()}</a><br /> (Edited on <b>" . $pathwayArray[$title] . "</b>) </li>";
                                $display = ($imageMode)?process($title, $pwUrl):$pwUrl;
                                if($imageMode) {
                                preg_match('/height="(\d+)"/', $display, $matches);
                                $height = $matches[1];
                                if ($height > 160){
                                        $display = preg_replace('/height="(\d+)"/', 'height="160px"', $display);
                                }
                        }
                        if ($imageMode){
                            $resultTable .= "<div style='float:left; vertical-align:bottom;width:220px;height:220px'>$display</div>";
                        } else {
                        if($count%2 == 0)
                            $resultTable .= "<tr><td width='10%'>&nbsp;</td><td width='35%'>$display</span></td>";
                        else
                            $resultTable .= "<td width='10%'>&nbsp;</td><td width='35%' align='left'>$display</td><td width='10%'>&nbsp;</td></tr>";
                        $count++;
                        }
                    }
                    if ($imageMode) $resultTable .= '</td>';
                    $resultTable .= "</tbody></table>";
                    break;
                }
        } 
	echo $resultTable;
}

function fetch_tree()
{
    global $xml, $res_array, $ontology_id, $concept_id;
    $xml = simplexml_load_string(ontologycache::fetchCache("tree", ontologyfunctions::getBioPortalURL('tree', array("ontologyId" => $ontology_id, "conceptId" => $concept_id))));


    fetch_terms();

    $res_arr["ResultSet"]["Result"]=$res_array;
    $res_json = json_encode($res_arr);
    echo $res_json ;

}


function fetch_terms()
{
    global $ontology_id ;
    global $concept_id ;
    global $xml;
	global $res_array;

    if($_GET['tree_pw'] == "yes")
    {
    $dbr =& wfGetDB(DB_SLAVE);
    $sql = "SELECT * FROM ontology where `term_id` = '$concept_id'";
    $res = $dbr->query($sql);
    while($row = $dbr->fetchObject($res))
        {
            $p = Pathway::newFromTitle($row->pw_id);
            if($p->isDeleted()) continue;
            if($_GET['species'] != "All Species")
            {
            if($p->getSpecies() == $_GET['species'])
            {
                
                $p_id = $row->pw_id;
                $pw = "<font face='Verdana'><i><b><a href='{$p->getFullUrl()}'>{$p->name()}</a></b></i></font>";
                $res_array[] =  ">> " . $pw . " - " . $p_id . "0000a||";
            }
            }
            else
            {
                $p_id = $row->pw_id;
                $pw = "<font face='Verdana'><i><b><a href='{$p->getFullUrl()}'>{$p->name()}</a></b></i></font>";
                $res_array[] =  ">> " . $pw . " - " . $p_id . "0000a||";
                }
        }

    }
$arr = $xml->data;

foreach($xml->data->classBean->relations->entry as $entry )
{
    if($entry->string == "SubClass")
    {

       foreach($entry->list->classBean as $sub_concepts)
        {

            if($_GET['mode'] != "")
            {
            if($_GET['mode'] == "tree")
            {

   			$exact_match = no_paths("exact",$ontology_id,$sub_concepts->id);
            $path_match = no_paths("path",$ontology_id,$sub_concepts->id);

            if($path_match + $exact_match > 0)
                $total_match = " (" . $exact_match . "/" . ( $path_match + $exact_match ) . ")";
            else
                continue;
            }
            else
            if($_GET['mode'] == "sidebar")
                $total_match = " (" . no_paths("all",$ontology_id,$sub_concepts->id) . ") ";
            }
            
            $temp_var = $sub_concepts->label . $total_match ." - " . $sub_concepts->id;
            if($sub_concepts->relations->entry->int == "0" && $exact_match ==0 )
            $temp_var .="||";
            $res_array[] = $temp_var;

        }

    }
}

}
function no_paths($match,$ontology_id,$concept_id)
{
    $count = 0;
    $pwIdArray = array();
    $dbr =& wfGetDB(DB_SLAVE);
    if($match == "exact")
        $sql = "SELECT * FROM ontology where `term_id` = '$concept_id'";
    elseif($match == "path")
        $sql = "SELECT * FROM ontology where `term_path` LIKE '%$concept_id%' ";
    else
        $sql = "SELECT * FROM ontology where (`term_id` = '$concept_id' OR `term_path` LIKE '%$concept_id%') ";

    $res = $dbr->query($sql);

    while($row = $dbr->fetchObject($res))
    {
        array_push($pwIdArray,$row->pw_id);
    }
   $dbr->freeResult( $res );
   $pwIdArray = array_unique($pwIdArray);
   
    foreach($pwIdArray as $pwId)
    {
        $p = Pathway::newFromTitle($pwId);
        if($p->isDeleted()) continue;
        if($_GET['species'] != "All Species")
        {
            if($p->getSpecies() == $_GET['species'])
                $count++;
        }
        else
        $count++;
    }
    return $count;
}


function fetch_species()
{
    $result = Pathway::getAvailableSpecies();
    $result = json_encode($result);
    echo($result);
}
function makeThumbNail( $pathway, $label = '', $href = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 300, $boxheight=false, $framed=false ) {
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
function process($title,$caption)
    {
    $pathway = Pathway::newFromTitle($title);
    $img = new Image($pathway->getFileTitle(FILETYPE_IMG));
    $href = $pathway->getFullUrl();
    if($caption == "")
    {
        $caption = "<a href=\"$href\">" . $pathway->name() . "</a>";
        $caption = html_entity_decode($caption);         //This can be quite dangerous (injection),
                                                            //we would rather parse wikitext, let me know if
                                                            //you know a way to do that (TK)
    }
    $output = makeThumbNail($pathway, $caption, $href, $tooltip, $align, $id, 175);
    return $output;
    }
?> 
