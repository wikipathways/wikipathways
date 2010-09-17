<?php
require_once('../ontologyindex/ontologycache.php');

class ontologyfunctions
{
    public static function removeOntologyTag($tagId, $pwTitle)
    {
        $dbw =& wfGetDB( DB_MASTER );
        $comment = "Ontology Term : '$tagId' removed !";
        $pathway = Pathway::newFromTitle($pwTitle);
		$gpml = $pathway->getGpml();
        $xml = simplexml_load_string($gpml);

        $entry = $xml->Biopax[0];
        $namespaces = $entry->getNameSpaces(true);
        $bp = $entry->children($namespaces['bp']);

				$level = self::getBiopaxLevel($xml);
				$voc = self::getControlledVocabularyElement($level);
				
        for($i=0; $i<count($bp->$voc); $i++)
            {
                if($bp->openControlledVocabulary[$i]->ID == $tagId)
                    unset($bp->openControlledVocabulary[$i]);
                $i++;
            }
//        $dbw->immediateBegin();
        $dbw->delete( 'ontology', array( 'pw_id' => $pwTitle,'term_id' => $tagId ), $fname );
//        $dbw->immediateCommit();
        $gpml = $xml->asXML();
        $pathway->updatePathway($gpml,$comment);
        echo "SUCCESS";
    }

		private static function getBiopaxLevel($xml) {
			$level = 3;
			
			$gpmlVersion = $xml->getNamespaces(false);
			$gpmlVersion = $gpmlVersion[''];
			if(preg_match("@http://genmapp.org/GPML/([0-9]{4})@", $gpmlVersion, $res)) {
				if($res[1] < 2010) {
					$level = 2;
				}				
			}
			return $level;
		}
		
		private static function getControlledVocabularyElement($level) {
			switch($level) {
			case '2':
				return "bp:openControlledVocabulary";
			case '3':
			default:
				return "bp:ControlledVocabulary";
			}
		}

		private static function getBiopaxNS($level) {
			switch($level) {
			case '2':
				return "http://www.biopax.org/release/biopax-level2.owl#";
			case '3':
			default:
				return "http://www.biopax.org/release/biopax-level3.owl#";
			}
		}
		
    public static function addOntologyTag($tagId, $tag, $pwTitle)
    {
        $comment = "Ontology Term : '$tag' added !";
        $pathway = Pathway::newFromTitle($pwTitle);
        $ontology = ontologyfunctions::getOntologyName($tagId);
        $path = ontologyfunctions::getOntologyTagPath($tagId);
				$gpml = $pathway->getGpml();
        $xml = simplexml_load_string($gpml);

        if(!isset($xml->Biopax[0]))
            $xml->addChild("Biopax");

        $entry = $xml->Biopax[0];
        $namespaces = $entry->getNameSpaces(true);
        $bp = $entry->children($namespaces['bp']);

				$level = self::getBiopaxLevel($xml);
				$ns = self::getBiopaxNS($level);
				$voc = self::getControlledVocabularyElement($level);
				
        $node = $xml->Biopax->addChild($voc,"",$ns);
        $node->addChild("TERM",$tag);
        $node->addChild("ID",$tagId);
        $node->addChild("Ontology",$ontology);

        $gpml = $xml->asXML();
        $pathway->updatePathway($gpml,$comment);

        $dbw =& wfGetDB( DB_MASTER );
        $dbw->immediateBegin();
        $dbw->insert( 'ontology', array(
                                        'term_id' => $tagId,
                                        'term'    => $tag,
                                        'ontology'=> $ontology,
                                        'pw_id'   => $pwTitle,
                                        'term_path'  => $path ),
                                        $fname,
                                        'IGNORE' );
        $dbw->immediateCommit();
        return "SUCCESS";
    }

    public static function getOntologyTags($pwId)
    {
        $title = $pwId;
        $dbr =& wfGetDB(DB_SLAVE);
        $query = "SELECT * FROM `ontology` " . "WHERE `pw_id` = '$title' ORDER BY `ontology`";
        $res = $dbr->query($query);
        while($row = $dbr->fetchObject($res))
        {
            $term['term_id'] = $row->term_id;
            $term['term'] = $row->term;
            $term['ontology'] = $row->ontology;
            $resultArray['Resultset'][]=$term;
            $count++;
        }
       $dbr->freeResult( $res );
       $resultJSON = json_encode($resultArray);
        if($count > 0)
            return $resultJSON ;
        else
            return "No Tags";
    }

    public static function getOntologyTagPath($id)
    {

        global $wgOntologiesBioPortalURL;
        $ontologyId = ontologyfunctions::getOntologyVersion($id);
        $URL = ontologyfunctions::getBioPortalURL('path', array("ontologyId"=>$ontologyId,"termId"=>$id)) ;
        $xml = simplexml_load_string(ontologycache::fetchCache("path",$URL));

        if($xml->data->list->classBean->relations->entry)
        {
            foreach($xml->data->list->classBean->relations->entry as $entry )
            {
                if($entry->string == "Path")
                {
                    $path = $entry->string[1];
                }
            }
        }
        return $path;
    }

    public static function getOntologyName($id)
    {
        global $wgOntologiesArray;
        foreach($wgOntologiesArray as $wgOntology)
            if(substr($id,0,2) ==  substr($wgOntology[1],0,2))
            {
                $ontologyName = $wgOntology[0];
                break;
            }
        return $ontologyName;
    }
    public static function getOntologyVersion($id)
    {
        global $wgOntologiesArray;
        foreach($wgOntologiesArray as $wgOntology)
            if(substr($id,0,2) ==  substr($wgOntology[1],0,2))
            {
                $ontologyId = $wgOntology[2];
                break;
            }
        return $ontologyId;
    }


    public static function getBioPortalURL($functionName, $data)
    {
        global $wgOntologiesBioPortalEmail, $wgOntologiesBioPortalSearchHits;
        switch($functionName)
        {
            case "path":
                $url = "http://rest.bioontology.org/bioportal/virtual/rootpath/ontologyId/termId?email=$wgOntologiesBioPortalEmail";
                break;
            case "search":
                $url = "http://rest.bioontology.org/bioportal/search/searchTerm/?ontologyids=ontologyId&maxnumhits=$wgOntologiesBioPortalSearchHits&email=$wgOntologiesBioPortalEmail" ;
                break;
            case "tree":
                $url = "http://rest.bioontology.org/bioportal/virtual/ontology/ontologyId/conceptId?email=$wgOntologiesBioPortalEmail" ;
                break;
        }
        foreach($data as $key=>$value)
            $url = str_replace($key,$value,$url);
        return  $url;
    }

    public static function getBioPortalSearchResults($searchTerm)
    {
        global $wgOntologiesArray;
        $count = 0;
        foreach($wgOntologiesArray as $ontology)
        {
            $ontologyIdArray[] = $ontology[2];
        }
        $ontologyId = implode(",", $ontologyIdArray);
//        $ontologyId = "1006,1035,1009";

        $url = ontologyfunctions::getBioPortalURL("search", array("ontologyId" => $ontologyId, "searchTerm" => $searchTerm));
        $xml = simplexml_load_string(ontologycache::fetchCache("search",$url));

        if(isset($xml->data->page->contents->searchResultList->searchBean))
            foreach($xml->data->page->contents->searchResultList->searchBean as $search_result )
            {
                $resultArray[$count]->label = str_replace('"','',(string)$search_result->contents);
                $resultArray[$count]->id = str_replace('"','',(string)$search_result->conceptId);
                $resultArray[$count]->ontology = (string)$search_result->ontologyDisplayLabel;
                $count++;
            }
        if ($count == 0)
        {
            $resultArray[$count]->label = "No results !";
            $resultArray[$count]->id = "No results !";
        }
        sort($resultArray);
        $resultArr["ResultSet"]["Result"]=$resultArray;
        $resultJSON = json_encode($resultArr);

        return $resultJSON ;

    }

    public static function getBioPortalTreeResults($termId)
    {
        $ontologyId = ontologyfunctions::getOntologyVersion($termId);
        $url = ontologyfunctions::getBioPortalURL("tree", array("ontologyId" => $ontologyId, "conceptId" => $termId));
        $xml = simplexml_load_string(ontologycache::fetchCache("tree",$url));

        foreach($xml->data->classBean->relations->entry as $entry )
        {
            if($entry->string == "SubClass")
            {
               foreach($entry->list->classBean as $sub_concepts)
                {
                    $temp_var = $sub_concepts->label . " - " . $sub_concepts->id;
                    if($sub_concepts->relations->entry->int == "0")
                    $temp_var .="||";
                    $resultArray[] = $temp_var;
                }
            }
        }

        sort($resultArray);
        $resultArr["ResultSet"]["Result"]=$resultArray;
        $resultJSON = json_encode($resultArr);
        return $resultJSON ;
    }
}
?>
