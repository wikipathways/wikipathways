<?php
## Manages downloading of the bridgedb datasources file
require_once("globals.php");

//If run from command line, update cache
if(isset($argv[0]) && $argv[0] == "DataSourcesCache.php") {
	echo("Updating datasources cache\n");
	$start = microtime(true);

	DataSourcesCache::update();

	$time = (microtime(true) - $start);
	echo("\tUpdated in $time seconds\n");
}

class DataSourcesCache {
	private static $url = "http://svn.bigcat.unimaas.nl/bridgedb/trunk/org.bridgedb.bio/resources/org/bridgedb/bio/datasources.txt";
	static $file = "datasources.txt";
	static $content = null;

	public static function update() {
		## Download a fresh datasources file
		$txt = file_get_contents(self::$url);
		if($txt) { //Only update if file could be downloaded
			$f = WPI_CACHE_PATH . "/" . self::$file;
			$fh = fopen($f, 'w');
			if( $fh !== false ) {
				fwrite($fh, $txt);
				fclose($fh);
				chmod($f, 0666);
			} else {
				throw new Exception( "Could't open $f for writing!" );
			}
			self::$content = $txt;
		}
	}

	private static function read() {
		$f = WPI_CACHE_PATH . "/" . self::$file;
		if(file_exists($f)) {
			return file_get_contents($f);
		}
	}

	public static function getContent() {
		if(!self::$content) {
			//Try to read from cached file
			$txt = self::read();
			if(!$txt) { //If no cache exists, update it
				self::update();
			} else {
				self::$content = $txt;
			}
		}
		return self::$content;
	}
}
?>
