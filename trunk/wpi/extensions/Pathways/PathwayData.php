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

	/**
	 * Creates an instance of PathwayData, containing
	 * the GPML code parsed as SimpleXml object
	 * \param pathway	The pathway to get the data for
	 **/
	function __construct($pathway) {
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
	 * Gets the name of the pathway, as stored in GPML
	 */
	function getName() {
		return (string)$this->gpml["Name"];
	}

	/**
	 * Gets the organism of the pathway, as stored in GPML
	 */
	function getOrganism() {
		return (string)$this->gpml["Organism"];
	}

	/**
	 * Gets the interactions
	 * \return an array of instances of the Interaction class
	 */
	function getInteractions() {
		if(!$this->interactions) {
			$this->interactions = array();
			foreach($this->gpml->Interaction as $line) {
				$startRef = (string)$line->Graphics->Point[0]['GraphRef'];
				$endRef = (string)$line->Graphics->Point[1]['GraphRef'];
				$typeRef = (string)$line->Graphics->Point[1]['ArrowHead'];
				if($startRef && $endRef && $typeRef) {
					$source = $this->byGraphId[$startRef];
					$target = $this->byGraphId[$endRef];
					//$type = $this->byGraphId[$typeRef];
					if($source && $target ) {
						$interaction =  new Interaction($source, $target, $line, $typeRef);
						$this->interactions[] = $interaction;
					}
				}
			}
		}
		return $this->interactions;
	}

	/**
	 * Gets the interactions
	 * \return an array of instances of the Interaction class
	 */
	function getAllAnnotatedInteractions() {
		if(!$this->interactions) {
			$this->interactions = array();
			foreach($this->gpml->Interaction as $line) {
				$startRef = (string)$line->Graphics->Point[0]['GraphRef'];
				$points = $line->Graphics->Point;
				$nb = count($points)-1;
				$endRef = (string)$line->Graphics->Point[1]['GraphRef'];
				$typeRef = (string)$line->Graphics->Point[$nb]['ArrowHead'];
				$source = $this->byGraphId[$startRef];
				$target = $this->byGraphId[$endRef];
				$interaction =  new Interaction($source, $target, $line, $typeRef);
				$this->interactions[] = $interaction;
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
				$cat = trim((string)$comment);
				if($cat) { //Ignore empty category comments
					array_push($categories, $cat);
				}
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
	 * Get a list of elements of the given type
	 * @param name the name of the elements to include
	 */
	function getElements($name) {
		return $this->getGpml()->$name;
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

	function getUniqueXrefs() {
		$elements = $this->getElements('DataNode');

		$xrefs = array();

		foreach($elements as $elm) {
			$id = $elm->Xref['ID'];
			$system = $elm->Xref['Database'];
			$ref = new Xref($id, $system);
			$xrefs[$ref->asText()] = $ref;
		}

		return $xrefs;
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

		//$bpChildren = $gpml->Biopax[0]->children("http://www.biopax.org/release/biopax-level2.owl#");
		$bpChildren = $gpml->Biopax[0]->children('bp', true); //only for version >=5.2
		$xrefs2 = $bpChildren->PublicationXRef; //BioPAX 2 version of publication xref
		$xrefs3 = $bpChildren->PublicationXref; //BioPAX 3 uses different case
		$this->processXrefs($xrefs2);
		$this->processXrefs($xrefs3);
	}

	private function processXrefs($xrefs) {
		foreach($xrefs as $xref) {
			//Get the rdf:id attribute
			$attr = $xref->attributes("http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			//$attr = $xref->attributes('rdf', true); //only for version >=5.2
			$id = (string)$attr['id'];
			$this->pubXRefs[$id] = $xref;
		}
	}

	private function loadGpml() {
		if(!$this->gpml) {
			$gpml = $this->pathway->getGpml();

			$this->gpml = new SimpleXMLElement($gpml);

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
	private $type;
	
	function __construct($source, $target, $edge, $type) {
		$this->source = $source;
		$this->target = $target;
		$this->edge = $edge;
		$this->type = $type;
	}
	function getSource() { return $this->source; }
	function getTarget() { return $this->target; }
	function getEdge() { return $this->edge; }
	function getType() { return $this->type; }

	function getName() {
		$source = $this->source['TextLabel'];
		if(!$source) $source = $this->source->getName() . $this->source['GraphId'];
		$target = $this->target['TextLabel'];
		if(!$target) $target = $this->target->getName() . $this->target['GraphId'];
		$type = $this->type;
		return $source. " -> " . $type . " -> " . $target;
	}
	function getNameSoft() {
		$source = $this->source['TextLabel'];
		if(!$source) $source = "";
		$target = $this->target['TextLabel'];
		if(!$target) $target = "";
		$type = $this->type;
		return $source. " -> " . $type . " -> " . $target;
	}
	function getPublicationXRefs($pathwayData) {
		$xrefs = $pathwayData->getPublicationXRefs();
		foreach($this->edge->BiopaxRef as $bpref) {
			$myrefs[] = $xrefs[(string)$bpref];
		}
		return $myrefs;
	}
}

class Xref {
	private $id;
	private $system;

	public function __construct($id, $system) {
		$this->id = $id;
		$this->system = $system;
	}

	public function getId() { return $this->id; }

	public function getSystem() { return $this->system; }

	public static function fromText($txt) {
		$data = explode(':', $txt);
		if( count( $data ) !== 2 ) {
			throw new Exception( "Tried to create an Xref from incomplete text: '$txt'" );
		}
		return new Xref($data[0], $data[1]);
	}

	public function asText() {
		return "{$this->id}:{$this->system}";
	}

	public function __toString() {
		return $this->asText();
	}
}
