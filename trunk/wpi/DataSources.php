<?php
require_once('DataSourcesCache.php');

## Manages parsing of the bridgedb datasources file

class DataSource {
	private static $linkouts; //Map of urls keyed by name 
	private static $codes; //Map of system codes keyed by name
	private static $types; //Map of system types keyed by name
	private static $species; //Map of system species keyed by name

	private static function initLinkouts() {
		self::$linkouts = array();
		$txt = DataSourcesCache::getContent();
		foreach(explode("\n", $txt) as $line) {
			$cols = explode("\t", $line);
			$name = $cols[0];
			$url = $cols[3];
			self::$linkouts[$name] = $url;
		}
	}
	
	private static function initCodes() {
                self::$codes = array();
                $txt = DataSourcesCache::getContent();
                foreach(explode("\n", $txt) as $line) {
                        $cols = explode("\t", $line);
                        $name = $cols[0];
                        $code = $cols[1];
                        self::$codes[$name] = $code;
                }
        }

        private static function initTypes() {
                self::$types = array();
                $txt = DataSourcesCache::getContent();
                foreach(explode("\n", $txt) as $line) {
                        $cols = explode("\t", $line);
                        $name = $cols[0];
                        $type = $cols[5];
                        self::$types[$name] = $type;
                }
        }

        private static function initSpecies() {
                self::$species = array();
                $txt = DataSourcesCache::getContent();
                foreach(explode("\n", $txt) as $line) {
                        $cols = explode("\t", $line);
                        $name = $cols[0];
                        $s = $cols[6];
                        self::$species[$name] = $s;
                }
        }

	/**
	 * returns the url template for linkouts. "$ID" in the template is replaced with
	 * the $id parameter provided.
	 */
	public static function getLinkout($id, $datasource) {
		if(!self::$linkouts) {
			self::initLinkouts();
		}
		if($datasource && array_key_exists($datasource, self::$linkouts)) {
			$value = self::$linkouts[$datasource];
			return str_replace('$ID', $id, $value);
		} else {
			return false;
		}
	}

	/**
	 * returns the system code
	 */
        public static function getCode($datasource) {
                if(!self::$codes) {
                        self::initCodes();
                }
                if($datasource && array_key_exists($datasource, self::$codes)) {
                        $value = self::$codes[$datasource];
                        return $value;
                } else {
                        return false;
                }
        }

	/**
	 * returns the datasource type, e.g., gene, probe, metabolite
	 */
        public static function getType($datasource) {
                if(!self::$types) {
                        self::initTypes();
                }
                if($datasource && array_key_exists($datasource, self::$types)) {
                        $value = self::$types[$datasource];
                        return $value;
                } else {
                        return false;
                }
        }

	/**
	 * returns "Genus species" associated with given datasource. If not a
	 * species-specific datasource, then a blank "" is returned.
	 */ 
        public static function getSpecies($datasource) {
                if(!self::$species) {
                        self::initSpecies();
                }
                if($datasource && array_key_exists($datasource, self::$species)) {
                        $value = self::$species[$datasource];
                        return $value;
                } else {
                        return false;
                }
        }

	/**
	 * returns the species-specific Ensembl datasource name, e.g., "Ensembl Mouse". 
	 * If there isn't one, it just returns "Ensembl".
	 */
	public static function getEnsemblDatasource($s) {
                if(!self::$species) {
                        self::initSpecies();
                }
		$datasource = "Ensembl"; //default return
		$match = "Ensembl"; //string match
	 	foreach(array_keys(self::$species) as $name){
			if(self::$species[$name] === $s && strncmp($name, $match, strlen($match)) == 0){
				$datasource = $name;
			}
		}
		return $datasource;
	}

	/**
	 * returns the list of species-specific datasources other than Ensembl, usually 
	 * model organism databases (MODs).
	 */
        public static function getModDatasources($s) {
                if(!self::$species) {
                        self::initSpecies();
                }
                $dsList = array();
                $match = "Ensembl"; //string match
                foreach(array_keys(self::$species) as $name){
                        if(self::$species[$name] === $s && strncmp($name, $match, strlen($match)) != 0){
                                $dsList[] = $name;
                        }
                }
                return $dsList;
        }
	
	/**
	 * returns the list of datasources per type, e.g. 'gene', 'probe', or 'metabolite' types.
	 */
	public static function getDatasourcesByType($type){
		if(!self::$types) {
			self::initTypes();
		}	
		$dsList = array();
		foreach(array_keys(self::$types) as $name){
			if(self::$types[$name] === $type){
				$dsList[] = $name;
			}
		}
		return $dsList;
	}
}
?>
