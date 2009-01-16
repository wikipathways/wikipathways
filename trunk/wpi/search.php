<?php
require_once 'wpi.php';
require_once 'includes/Zend/Search/Lucene.php';

//open the index (path defined in pass.php)
Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive());

PathwayIndex::$index = Zend_Search_Lucene::open($wpiIndexDir);

class PathwayIndex {
	static $index;
	
	/**
	 * Get a list of pathways by a datanode xref.
	 * @param $xref The XRef object, if the XRef->code field is empty, the search
	 will not restrict to any database.
	 * @param $indirect also use cross-references from the synonym database
	 * @return An array with the results as PathwayDocument objects
	 **/
	public static function searchByXref($xref, $indirect = true) {
		$results = array();
		
		$field = self::$f_id;
		$word = $xref->id;
		
		if($xref->code) {
			$field = $field . '.database';
			$word = $word . ':' . $xref->code;
		}
		if($indirect) {
			$field = 'x.' . $field;
		}
		
		$term = new Zend_Search_Lucene_Index_Term($word, $field);
		$query = new Zend_Search_Lucene_Search_Query_Term($term);
		$hits = self::$index->find($query);
		return self::hitsToResults($hits);
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
		$results = array();
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
			$usrQuery = Zend_Search_Lucene_Search_QueryParser::parse($query);
			$orgQuery = Zend_Search_Lucene_Search_QueryParser::parse(
				self::$f_organism . ':"' . $organism . '"'
			);

			$query = new Zend_Search_Lucene_Search_Query_Boolean();
			$query->addSubquery($usrQuery, true);
			$query->addSubquery($orgQuery, true);
		}
		
		$hits = self::$index->find($query);
		return self::hitsToResults($hits);
	}
	
	/**
	 * Searches on literature fields:
	 * literature.pubmed, literature.author, literature.title
	 * @parameter $query The query (can be pubmed id, author or title keyword).
	 * @return An array with the results as SearchHit objects
	 **/
	public static function searchByLiterature($query) {
		$results = array();
		$query = self::queryToAllFields(
			$query, 
			array(
				self::$f_literature_author,
				self::$f_literature_title,
				self::$f_literature_pubmed,
			)
		);
		$hits = self::$index->find($query);
		return self::hitsToResults($hits);
	}
	
	public static function searchInteractions($query) {
		$results = array();
		$query = self::queryToAllFields(
			$query,
			array(
				self::$f_right,
				self::$f_left,
				self::$f_mediator
			)
		);
		$hits = self::$index->find($query);
		return self::hitsToResults($hits);
	}
	
	public static function listPathwayXrefs($pathway, $code) {
		$xrefs = array();
	
		$source = $pathway->getTitleObject()->getFullUrl();
		$term = new Zend_Search_Lucene_Index_Term($source, self::$f_source);
		$query = new Zend_Search_Lucene_Search_Query_Term($term);
		
		self::$index->setResultSetLimit(0);
		
		$hits = self::$index->find($query);
		foreach($hits as $h) {
			$doc =& $h->getDocument();
			if(in_array(self::$f_x_id_database, $doc->getFieldNames())) {
				$iddbList = $doc->getFieldValues(self::$f_x_id_database);
				foreach($iddbList as $iddb) {
					if(self::endsWith(":$code", $iddb)) {
						$xrefs[] = substr($iddb, 0, -strlen(":$code"));
					}
				}
			}
		}
		return array_unique($xrefs);
	}
	
	function endsWith($needle, $haystack){
		return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
	}

	private static function hitsToResults($hits) {
		$results = array();
		foreach($hits as $hit) {
			$results[] = new SearchHit($hit);
		}
		return $results;
	}
	
	static function pathwayFromSource($source) {
		return Pathway::newFromTitle($source);
	}
	
	private static function queryToAllFields($queryStr, $fields) {
		$q = '';
		foreach($fields as $f) {
			$q .= "($f:$queryStr) ";
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

class SearchHit {
	private $hit;
	private $pathway;
	private $fields;
	private $score;
	
	function __construct($hit) {
		$this->hit = $hit;
	}
	
	function getPathway() {
		if(!$this->pathway) {
			$this->pathway = PathwayIndex::pathwayFromSource(
				$this->hit->getDocument()->getFieldValue(PathwayIndex::$f_source)
			);
		}
		return $this->pathway;
	}
	
	function getDocument() {
		return $this->hit->getDocument();
	}
	
	function getScore() {
		return $this->hit->score;
	}
}

class XRef {
	public $id;
	public $code;
	
	function __construct($id, $code = null) {
		$this->id = $id;
		$this->code = $code;
	}
}
?>
