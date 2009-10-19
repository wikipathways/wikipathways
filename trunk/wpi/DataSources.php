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
                        $species = $cols[6];
                        self::$species[$name] = $species;
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
                if(!self::$speciess) {
                        self::initSpeciess();
                }
                if($datasource && array_key_exists($datasource, self::$speciess)) {
                        $value = self::$speciess[$datasource];
                        return $value;
                } else {
                        return false;
                }
        }

}
?>
