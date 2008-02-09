<?php

define('COMMENT_WP_CATEGORY', 'WikiPathways-category');
define('COMMENT_WP_DESCRIPTION', 'WikiPathways-description');

/**
 * Object that holds the actual data from a pathway (as stored in GPML)
 */
class PathwayData {
	private $pathway;
	private $gpml;
	private $interactions;
	private $byGraphId;
	private $revision;
	
	/**
	 * Creates an instance of PathwayData, containing
	 * the GPML code parsed as SimpleXml object
	 * \param pathway	The pathway to get the data for
	 * \param revision	The revision of the pathway (optional,
	 * if not specified, the newest revision will be used)
	 **/
	function __construct($pathway, $revision = 0) {
		$this->pathway = $pathway;
		$this->loadGpml();
	}
	
	/**
	 * Gets the SimpleXML representation of the GPML code
	 */
	function getGpml() {
		return $this->gpml;
	}
	
	/**
	 * Gets the interactions
	 * \return an array of instances of the Interaction class
	 */
	function getInteractions() {
		if(!$this->interactions) {
			$this->interactions = array();
			foreach($this->gpml->Line as $line) {
				$startRef = (string)$line->Graphics->Point[0]['GraphRef'];
				$endRef = (string)$line->Graphics->Point[1]['GraphRef'];
				if($startRef && $endRef) {
					$source = $this->byGraphId[$startRef];
					$target = $this->byGraphId[$endRef];
					if($source && $target) {
						$interaction =  new Interaction($source, $target, $line);
						$this->interactions[] = $interaction;
					}
				}
			}
		}
		return $this->interactions;
	}
	 
	/**
	 * Gets the WikiPathways categories that are stored in GPML
	 * Categories are stored as Comments with Source attribute COMMENT_WP_CATEGORY
	 */
	function getWikiCategories() {
		$categories = array();
		foreach($this->gpml->Comment as $comment) {
			if($comment['Source'] == COMMENT_WP_CATEGORY) {
				array_push($categories, (string)$comment);
			}
		}
		return $categories;
	}
	
	/**
	 * Gets the WikiPathways description that is stored in GPML
	 * The description is stored as Comment with Source attribute COMMENT_WP_DESCRIPTION
	 */
	function getWikiDescription() {
		foreach($this->gpml->Comment as $comment) {
			if($comment['Source'] == COMMENT_WP_DESCRIPTION) {
				return (string)$comment;
			}
		}
	}
	
	/**
	 * Get a list of unique elements
	 * \param name The name of the elements to include
	 * \param uniqueAttribute The attribute of which the value has to be unique
	 */
	function getUniqueElements($name, $uniqueAttribute) {
		$unique = array();
		foreach($this->gpml->$name as $elm) {
			$key = $elm[$uniqueAttribute];
			$unique[(string)$key] = $elm;
		}
		return $unique;
	}
	
	function getElementsForPublication($xrefId) {
		$gpml = $this->getGpml();
		$elements = array();
		foreach($gpml->children() as $elm) {
			foreach($elm->BiopaxRef as $ref) {
				$ref = (string)$ref;
				if($xrefId == $ref) {
					array_push($elements, $elm);
				}
			}
		}
		return $elements;
	}
	
	private $pubXRefs;
	
	public function getPublicationXRefs() {
		return $this->pubXRefs;
	}
	
	private function findPublicationXRefs() {
		$this->pubXRefs = array();
		
		$gpml = $this->gpml;

		//Format literature references
		if(!$gpml->Biopax) return;

		$bpChildren = $gpml->Biopax[0]->children("http://www.biopax.org/release/biopax-level2.owl#");
		//$bpChildren = $gpml->Biopax[0]->children('bp', true); //only for version >=5.2
		$xrefs = $bpChildren->PublicationXRef;

		foreach($xrefs as $xref) {
			//Get the rdf:id attribute
			$attr = $xref->attributes("http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			//$attr = $xref->attributes('rdf', true); //only for version >=5.2
			$id = $attr['id'] ? $attr['id'] : $i++;
			//QUICK HACK for preventing duplicate articles
			foreach($this->pubXRefs as $r) {
				if((string)$r->ID == (string)$xref->ID) {
					$found = true;
				}
			}
			if($found) continue;
			//END QUICK HACK
			$this->pubXRefs[(string)$id] = $xref;
		}
	}
	
	private function loadGpml($revision = 0) {
		if(!$this->gpml) {
			$gpml = $this->pathway->getGpml($revision);

			try {
			$this->gpml = new SimpleXMLElement($gpml);
			}
			catch(Exception $e){
			}

			//Pre-parse some data
			$this->findPublicationXRefs();
			//Fill byGraphId array
			foreach($this->gpml->children() as $elm) {
				$id = (string)$elm['GraphId'];
				if($id) {
					$this->byGraphId[$id] = $elm;
				}
			}
		}
	}
}

class Interaction {
	//The interaction elements (all SimpleXML elements)
	private $source;
	private $target;
	private $edge;
	
	function __construct($source, $target, $edge) {
		$this->source = $source;
		$this->target = $target;
		$this->edge = $edge;
	}
	
	function getSource() { return $this->source; }
	function getTarget() { return $this->target; }
	function getEdge() { return $this->edge; }
	
	function getName() {
		$source = $this->source['TextLabel'];
		if(!$source) $source = $this->source->getName() . $this->source['GraphId'];
		$target = $this->target['TextLabel'];
		if(!$target) $target = $this->target->getName() . $this->target['GraphId'];
		return $source . " -> " . $target;
	}
	
	function getPublicationXRefs($pathwayData) {
		$xrefs = $pathwayData->getPublicationXRefs();
		foreach($this->edge->BiopaxRef as $bpref) {
			$myrefs[] = $xrefs[(string)$bpref];
		}
		return $myrefs;
	}
}

?>
