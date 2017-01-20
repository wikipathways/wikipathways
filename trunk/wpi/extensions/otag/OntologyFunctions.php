<?php
$dir = getcwd();
chdir(dirname(realpath(__FILE__)) . "/../../");
require_once('OntologyCache.php');
chdir($dir);

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

		for($i=0; $i<count($bp->openControlledVocabulary); $i++) {
			if($bp->openControlledVocabulary[$i]->ID == $tagId)
				unset($bp->openControlledVocabulary[$i]);
			$i++;
		}
		$dbw->delete( 'ontology', array( 'pw_id' => $pwTitle,'term_id' => $tagId ), $fname );
		$gpml = $xml->asXML();
		$pathway->updatePathway($gpml,$comment);
	}

	public static function addOntologyTag($tagId, $tag, $pwTitle)
	{
		$comment = "Ontology Term : '$tag' added !";
		$pathway = Pathway::newFromTitle($pwTitle);
		$ontology = self::getOntologyName($tagId);
		//$path = self::getOntologyTagPath($tagId);
		$path = "";
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

		try {
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
		catch(Exception $e) {
			return "ERROR";
		}
	}

	public static function getOntologyTags($pwId) {
		$count = 0;
		$title = $pwId;
		$resultArray = array();
		$dbr =& wfGetDB(DB_SLAVE);
		#$query = "SELECT * FROM `ontology` " . "WHERE `pw_id` = '$title' ORDER BY `ontology`";
		#$res = $dbr->query($query);
		## Replacing with parameterized SQL to resolve critical security issue
		$res = $dbr->select('ontology', '*', array('pw_id' => $title), __METHOD__, array('ORDER BY' => 'ontology'));

		$path = "";

		while($row = $dbr->fetchObject($res)) {
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

	public static function getOntologyTagPath($id) {
		global $wgOntologiesBioPortalURL;

		$ontologyId = self::getOntologyVersion($id);
		$URL = self::getBioPortalURL('path', array("ontologyId"=>$ontologyId,"termId"=>$id)) ;
		$xml = simplexml_load_string(OntologyCache::fetchCache("path",$URL));

		//Once getBioPortalURL is updated above, then parsing below needs to also be updated.
		//Note:  For now, this method is being skipped and 'term_path' field in ontology table is left empty!
		if($xml->data->list->classBean->relations->entry) {
			foreach($xml->data->list->classBean->relations->entry as $entry ) {
				if($entry->string == "Path") {
					$path = $entry->string[1];
				}
			}
		}
		return $path;
	}

	public static function getOntologyName($id) {
		global $wgOntologiesArray;
		foreach($wgOntologiesArray as $wgOntology)
			if(substr($id,0,2) ==  substr($wgOntology[1],0,2)) {
				$ontologyName = $wgOntology[0];
				break;
			}
		return $ontologyName;
	}

	public static function getOntologyVersion($id) {
		global $wgOntologiesArray;
		foreach($wgOntologiesArray as $wgOntology)
			if(substr($id,0,2) ==  substr($wgOntology[1],0,2)) {
				$ontologyId = $wgOntology[2];
				break;
			}
		return $ontologyId;
	}
	public static function getOntologyAcronym($id) {
		global $wgOntologiesArray;
		foreach($wgOntologiesArray as $wgOntology)
			if(substr($id,0,2) ==  substr($wgOntology[1],0,2)) {
				$ontologyId = $wgOntology[4];
				break;
			}
		return $ontologyId;
	}

	public static function getBioPortalURL($functionName, $data) {
		global $wgOntologiesBioPortalEmail, $wgOntologiesBioPortalSearchHits, $wpiBioportalKey;
		switch($functionName) {
			case "path":
				$url = "http://rest.bioontology.org/bioportal/virtual/rootpath/ontologyId/termId?email=$wgOntologiesBioPortalEmail";
				// TODO: replace url 
				// Note: conceptId below needs to be a URL-encoded URI, e.g., http%3A%2F%2Fpurl.obolibrary.org%2Fobo%2FPW_0000100 
				//   and ontologyId needs to be an acronym, e.g., PW (added as [4] to $wgOntologiesArray in LocalSettings)
				//$url = "http://data.bioontology.org/ontologies/ontologyId/classes/conceptId/tree?apikey=$wpiBioportalKey";
				break;
			case "search":
		     //$url = "http://rest.bioontology.org/bioportal/search/searchTerm/?ontologyids=ontologyId&maxnumhits=$wgOntologiesBioPortalSearchHits&email=$wgOntologiesBioPortalEmail" ;
				$url = "http://rest.bioontology.org/bioportal/search/searchTerm/?ontologyids=ontologyId&maxnumhits=$wgOntologiesBioPortalSearchHits&email=$wgOntologiesBioPortalEmail" ;
				//TODO: replace url
				// Note: this returns an array of hits in the "collection" key
				//$url = "http://data.bioontology.org/search?q=searchTerm&ontologies=ontologyId&pagesize=$wgOntologiesBioPortalSearchHits&apikey=$wpiBioportalKey" ;
				break;
			case "tree":
				$url = "http://rest.bioontology.org/bioportal/virtual/ontology/ontologyId/conceptId?email=$wgOntologiesBioPortalEmail" ;
				//TODO: replace url
				// Note: this returns an array of children of the given class
				//$url = "http://data.bioontology.org/ontologies/ontologyId/classes/conceptId/children?apikey=$wpiBioportalKey" ;
				break;
		}
		foreach($data as $key=>$value)
			$url = str_replace($key,$value,$url);
		return  $url;
	}

	public static function getBioPortalSearchResults($searchTerm) {
		global $wgOntologiesBioPortalSearchHits, $wpiBioportalKey;
		$count = 0;
		$resultArray = array();		
		$url = "http://data.bioontology.org/search?q=$searchTerm&ontologies=DOID,PW,CL&pagesize=$wgOntologiesBioPortalSearchHits&apikey=$wpiBioportalKey&format=xml";
		$xml = simplexml_load_file($url);

		foreach($xml->collection as $colect ) {

				foreach($colect->class as $entry ) {						
					if (strpos((string)$entry->id, 'DOID_') !== FALSE  
							|| strpos((string)$entry->id, 'PW_') !== FALSE 
							|| strpos((string)$entry->id, 'CL_') !== FALSE  ){
						$resultArray[$count]['label'] = (string)$entry->prefLabel;
						$resultArray[$count]['id'] = (string)$entry->id;
						$resultArray[$count]['ontology'] = (string)$entry->id;
						$count++;	
					}
				}
		}
		if ($count == 0) {
			$resultArray[$count]['label'] = "No results !";
			$resultArray[$count]['id'] = "No results !";
		}
		sort($resultArray);
		$resultArr["ResultSet"]["Result"]=$resultArray;
		$resultJSON = json_encode($resultArr);

		return $resultJSON ;

	}

	public static function getBioPortalTreeResults($termId) {
		global $wpiBioportalKey;

		$ontologyId  = str_replace (":","_",$termId);
		$pos = strpos($ontologyId,"_");
		if ( $pos > 2 ) {
		$ontologyAcronym = substr($termId, 0, 4);
		}
		else {
		$ontologyAcronym = substr($termId, 0, 2);
		}

		$url = "http://data.bioontology.org/ontologies/$ontologyAcronym/classes/http%3A%2F%2Fpurl.obolibrary.org%2Fobo%2F$ontologyId/children?apikey=$wpiBioportalKey&format=xml";
		$xml = simplexml_load_file($url);
		if ( $xml->pageCount == 0) {
			$temp_var .="||";
			$resultArray[] = $temp_var;
		}
		else{
			foreach($xml->collection as $colect ) {
				foreach($colect->class as $entry ) {
					$label = str_replace ("http://purl.obolibrary.org/obo/PW_",'PW:',$entry->id);
					$label = str_replace ("http://purl.obolibrary.org/obo/DOID_",'DOID:',$entry->id);
					$label = str_replace ("http://purl.obolibrary.org/obo/CL_",'CL:',$entry->id);
					$temp_var = $entry->prefLabel. " - " . $label;
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
