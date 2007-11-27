<?php

require_once('globals.php');
/**
 * Static class that keeps track of mime-types for
 * file extensions. Allows you to register a custom
 * extension
 */
class MimeTypes {
	private static $types = array(
		FILETYPE_IMG => "image/svg+xml",
		FILETYPE_GPML => "text/xml",
		FILETYPE_PNG => "image/png",
		"pdf" => "application/pdf",
		"pwf" => "text/plain"
	);
	
	public static function registerMimeType($extension, $mime) {
		self::$types[$extension] = $mime;
	}
	
	public static function getMimeType($extension) {
		return self::$types[$extension];
	}
}
?>
