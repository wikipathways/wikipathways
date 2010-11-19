<?php
require_once('../../OntologyCache.php');

class OntologyFunctions
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

        for($i=0; $i<count($bp->openControlledVocabulary); $i++)
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

    public static function addOntologyTag($tagId, $tag, $pwTitle)
    {
        $comment = "Ontology Term : '$tag' added !";
        $pathway = Pathway::newFromTitle($pwTitle);
        $ontology = self::getOntologyName($tagId);
        $path = self::getOntologyTagPath($tagId);
		$gpml = $pathway->getGpml();
        $xml = simplexml_load_string($gpml);

        if(!isset($xml->Biopax[0]))
            $xml->addChild("Biopax");

        $entry = $xml->Biopax[0];
        $namespaces = $entry->getNameSpaces(true);
        $bp = $entry->children($namespaces['bp']);

        $ns = "http://www.biopax.org/release/biopax-level3.owl#";

        $gpmlVersion = $xml->getNamespaces(false);
        $gpmlVersion = $gpmlVersion[''];
        if(preg_match("@http://genmapp.org/GPML/([0-9]{4})@", $gpmlVersion, $res)) {
                if($res[1] < 2010) {
                        $ns = "http://www.biopax.org/release/biopax-level2.owl#";
                }
        }
        $node = $xml->Biopax->addChild("bp:openControlledVocabulary","",$ns);

        $node->addChild("TERM",$tag);
        $node->addChild("ID",$tagId);
        $node->addChild("Ontology",$ontology);

        $gpml = $xml->asXML();

        try
        {
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
        catch(Exception $e)
        {
            return "ERROR";
        }
    }

    public static function getOntologyTags($pwId)
    {
        $title = $pwId;
        $resultArray = array();
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
       return $resultJSON ;
    }

    public static function getOntologyTagPath($id)
    {

        global $wgOntologiesBioPortalURL;
        $ontologyId = self::getOntologyVersion($id);
        $URL = self::getBioPortalURL('path', array("ontologyId"=>$ontologyId,"termId"=>$id)) ;
        $xml = simplexml_load_string(OntologyCache::fetchCache("path",$URL));

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
//                $url = "http://rest.bioontology.org/bioportal/search/searchTerm/?ontologyids=ontologyId&maxnumhits=$wgOntologiesBioPortalSearchHits&email=$wgOntologiesBioPortalEmail" ;
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
            $ontologyIdArray[] = $ontology[2];

        $ontologyId = implode(",", $ontologyIdArray);

        $url = self::getBioPortalURL("search", array("ontologyId" => $ontologyId, "searchTerm" => $searchTerm));
        $xml = simplexml_load_string(OntologyCache::fetchCache("search", $url));

        if(isset($xml->data->page->contents->searchResultList->searchBean))
            foreach($xml->data->page->contents->searchResultList->searchBean as $search_result )
            {
                $id = str_replace('"','',(string)$search_result->conceptId);

                $resultArray[$count]->label = str_replace('"','',(string)$search_result->contents);
                $resultArray[$count]->id = (string) $search_result->conceptIdShort;
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
        $ontologyId = self::getOntologyVersion($termId);
        $url = self::getBioPortalURL("tree", array("ontologyId" => $ontologyId, "conceptId" => $termId));
        $xml = simplexml_load_string(OntologyCache::fetchCache("tree",$url));

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