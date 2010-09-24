<?php
require_once 'wpi.php';

class IndexNotFoundException extends Exception {
	public function __construct() {
		parent::__construct(
			'Unable to locate lucene index service. Please specify the base url for the index service as $indexServiceUrl in pass.php'
		);
	}
}

/**
 * Handles the requests to the REST indexer service.
 * The base url to this service can be specified using the global variable $indexServiceUrl
 */
class IndexClient {
	private static function doQuery($url) {
		$r = new HttpRequest($url, HttpRequest::METH_GET);
		try {
			$r->send();
		} catch(Exception $e) {
			throw new IndexNotFoundException();
		}
		if($r->getResponseCode() == 200) {
			$xml = new SimpleXMLElement($r->getResponseBody());
			$results = array();
			//Convert the response to SearchHit objects
			foreach($xml->SearchResult as $resultNode) {
				$score = $resultNode['Score'];
				$fields = array();
				foreach($resultNode->Field as $fieldNode) {
					$fields[(string)$fieldNode['Name']][] = (string)$fieldNode['Value'];
				}
				//Remove duplicate values
				foreach(array_keys($fields) as $fn) {
					$fields[$fn] = array_unique($fields[$fn]);
				}
				$results[] = new SearchHit($score, $fields);
			}
			return $results;
		} else {
			$txt = $r->getResponseBody();
			if(strpos($txt, '<?xml')) {
				$xml = new SimpleXMLElement($r->getResponseBody());
				throw new Exception($xml->message);
			} else {
				throw new Exception($r->getResponseBody());
			}
		}
	}
	
	private static function postQuery($url, $ids, $codes){
		$r = new HttpRequest($url, HttpRequest::METH_POST);
		$r->addPostFields (
			array (
				'id' => '210',
				'code' => 'L'
			));
		try {
		       $r->send();
		} catch (Exception $e) {
                        throw new IndexNotFoundException();
                }
		if ($r->getResponseCode() == 200) {
                        $xml = new SimpleXMLElement($r->getBody());
                        $results = array();
                        //Convert the response to SearchHit objects
                        foreach($xml->SearchResult as $resultNode) {
                                $score = $resultNode['Score'];
                                $fields = array();
                                foreach($resultNode->Field as $fieldNode) {
                                        $fields[(string)$fieldNode['Name']][] = (string)$fieldNode['Value'];
                                }
                                //Remove duplicate values
                                foreach(array_keys($fields) as $fn) {
                                        $fields[$fn] = array_unique($fields[$fn]);
                                }
                                $results[] = new SearchHit($score, $fields);
                        }
                        return $results;
                } else {
                        $txt = $r->getResponseBody();
                        if(strpos($txt, '<?xml')) {
                                $xml = new SimpleXMLElement($r->getResponseBody());
                                throw new Exception($xml->message);
                        } else {
                                throw new Exception($r->getResponseBody());
                        }
                }
        }

			
	/**
	 * Performs a query on the index service and returns the results
	 * as an array of SearchHit objects.
	 */
	static function query($query, $analyzer = '') {
		$url = self::getServiceUrl() . 'search?query=' . urlencode($query);
		if($analyzer) {
			$url .= "&analyzer=$analyzer";
		}
		return self::doQuery($url);
	}
	
	static function queryXrefs($ids, $codes) {
		$enc_ids = array();
		$enc_codes = array();
		foreach($ids as $i) $enc_ids[] = urlencode($i);
		foreach($codes as $c) $enc_codes[] = urlencode($c);
		
		$url = self::getServiceUrl() . 'searchxrefs?';
		$url .= 'id=' . implode('&id=', $enc_ids);
		if(count($enc_codes) > 0) {
			$url .= '&code=' . implode('&code=', $enc_codes);
		}
		return self::doQuery($url);
		#return self::postQuery($url, $ids, $codes);
	}
	
	/**
	 * Get the xrefs for a pathway, translated to the given system code.
	 * @return an array of strings containing the ids.
	 */
	static function xrefs($pathway, $code) {
		$source = $pathway->getTitleObject()->getFullURL();
		$url = self::getServiceUrl() . "xrefs/" . urlencode($source) . "/" . urlencode($code);
		$r = new HttpRequest($url, HttpRequest::METH_GET);
		try {
			$r->send();
		} catch(Exception $e) {
			throw new IndexNotFoundException();
		}
		if($r->getResponseCode() == 200) {
			$txt = $r->getResponseBody();
			if($txt) {
				return explode("\n", $txt);
			} else {
				return array();
			}
		} else {
			throw new Exception($r->getResponseBody());
		}
	}
	
	static function getServiceUrl() {
		global $indexServiceUrl;
		if(!$indexServiceUrl) {
			throw new IndexNotFoundException();
		}
		return $indexServiceUrl;
	}
}

class SearchHit {
	private $pathway;
	private $fields;
	private $score;
	
	function __construct($score, $fields) {
		$this->score = $score;
		$this->fields = $fields;
	}
	
	function getPathway() {
		if(!$this->pathway) {
			$this->pathway = PathwayIndex::pathwayFromSource(
				$this->fields[PathwayIndex::$f_source][0]
			);
		}
		return $this->pathway;
	}
	
	function getScore() {
		return $this->score;
	}
	
	function getFieldValues($name) {
		return $this->fields[$name];
	}
	
	function setFieldValues($name, $values) {
		$this->fields[$name] = $values;
	}
	function getFieldNames() {
		return array_keys($this->fields);	
	}
	
	function getFieldValue($name) {
		$values = $this->fields[$name];
		if($values) return $values[0];
	}
}

class PathwayIndex {
	/**
	 * Get a list of pathways by a datanode xref.
	 * @param $xref The XRef object or an array of XRef objects. If the XRef->getSystem() field is empty, the search
	 will not restrict to any database.
	 * @return An array with the results as PathwayDocument objects
	 **/
	public static function searchByXref($xrefs) {
		if(!is_array($xrefs)) {
			$xrefs = array( $xrefs );
		}
		
		$ids = array();
		$codes = array();
		
		foreach($xrefs as $xref) {
			$ids[] = $xref->getId();
			if($xref->getSystem()) {
				$codes[] = $xref->getSystem();
			}
		}
		return IndexClient::queryXrefs($ids, $codes);
	}
	
	/**
	 * Searches on all text fields:
	 * name, organism, textlabel, category, description
	 * @parameter $query The query (e.g. 'apoptosis')
	 * @parameter $organism Optional, specify organism name to limit search by organism.
	 * Leave empty to search on all organisms.
	 * @return An array with the results as PathwayDocument objects
	 **/
	public static function searchByText($query, $organism = false) {
		$query = self::queryToAllFields(
			$query, 
			array(
				self::$f_name,
				self::$f_textlabel,
				self::$f_category,
				self::$f_description
			)
		);
		
		if($organism) {
			$query = "($query) AND " . self::$f_organism . ":\"$organism\"";
		}
		return IndexClient::query($query);
	}

	/**
	 * Searches Pathway title
	 * @parameter $query The query (e.g. 'apoptosis')
	 * @parameter $organism Optional, specify organism name to limit search by organism.
	 * Leave empty to search on all organisms.
	 * @return An array with the results as PathwayDocument objects
	 **/
	public static function searchByTitle($query, $organism = false) {
		$query = self::queryToAllFields(
			$query,
			array(
				self::$f_name,
			)
		);

		if($organism) {
			$query = "($query) AND " . self::$f_organism . ":\"$organism\"";
		}
		return IndexClient::query($query);
	}
        
	/**
	 * Searches on literature fields:
	 * literature.pubmed, literature.author, literature.title
	 * @parameter $query The query (can be pubmed id, author or title keyword).
	 * @return An array with the results as SearchHit objects
	 **/
	public static function searchByLiterature($query) {
		$query = self::queryToAllFields(
			$query, 
			array(
				self::$f_literature_author,
				self::$f_literature_title,
				self::$f_literature_pubmed,
			)
		);
		return IndexClient::query($query);
	}
	
	public static function searchInteractions($query) {
		$query = self::queryToAllFields(
			$query,
			array(
				self::$f_right,
				self::$f_left,
				self::$f_mediator
			)
		);
		return IndexClient::query($query);
	}
	
	public static function listPathwayXrefs($pathway, $code) {
		return IndexClient::xrefs($pathway, $code);
	}

	static function pathwayFromSource($source) {
		return Pathway::newFromTitle($source);
	}
	
	private static function queryToAllFields($queryStr, $fields) {
		$q = '';
		foreach($fields as $f) {
			$q .= "$f:($queryStr) ";
		}
		return $q;
	}
	
	//Field names
	static $f_source = 'source';
	static $f_name = 'name';
	static $f_organism = 'organism';
	static $f_textlabel = 'textlabel';
	static $f_category = 'category';
	static $f_description = 'description';
	static $f_id = 'id';
	static $f_id_database = 'id.database';
	static $f_x_id = 'x.id';
	static $f_x_id_database = 'x.id.database';
	static $f_graphId = 'graphId';
	static $f_left = 'left';
	static $f_right = 'right';
	static $f_mediator = 'mediator';
	static $f_literature_author = 'literature.author';
	static $f_literature_title = 'literature.title';
	static $f_literature_pubmed = 'literature.pubmed';
}
?>
