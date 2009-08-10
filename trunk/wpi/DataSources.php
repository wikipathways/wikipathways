<?php
require_once('DataSourcesCache.php');

## Manages parsing of the bridgedb datasources file
## and provides hyperlinks for xrefs

class DataSource {
	private static $urlTemplates; //Map with names as key
	
	private static function init() {
		self::$urlTemplates = array();
		$txt = DataSourcesCache::getContent();
		foreach(explode("\n", $txt) as $line) {
			$cols = explode("\t", $line);
			$name = $cols[0];
			$url = $cols[3];
			self::$urlTemplates[$name] = $url;
		}
	}
	
	public static function getUrl($id, $datasource) {
		if(!self::$urlTemplates) {
			self::init();
		}
		if($datasource && array_key_exists($datasource, self::$urlTemplates)) {
			$urlTmp = self::$urlTemplates[$datasource];
			return str_replace('$ID', $id, $urlTmp);
		} else {
			return false;
		}
	}
}
?>
