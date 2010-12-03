<?php

try {
	//Initialize MediaWiki
	$wpiDir = dirname(realpath(__FILE__));
	set_include_path(get_include_path() . PATH_SEPARATOR . $wpiDir);
	set_include_path(get_include_path().PATH_SEPARATOR.realpath("$wpiDir/includes"));
	set_include_path(get_include_path().PATH_SEPARATOR.realpath("$wpiDir/../includes").PATH_SEPARATOR.realpath("$dir/../").PATH_SEPARATOR);
	$dir = getcwd();
	chdir($wpiDir . "/../"); //Ugly, but we need to change to the MediaWiki install dir to include these files, otherwise we'll get an error
	require_once ( 'WebStart.php');
	require_once( 'Wiki.php' );
	chdir($dir);

	require_once('MwUtils.php');
	require_once('globals.php');
	require_once( 'Pathway.php' );
	require_once('MimeTypes.php' );
	//Parse HTTP request (only if script is directly called!)
	if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
	$action = $_GET['action'];
	$pwTitle = $_GET['pwTitle'];
	$oldId = $_GET['oldid'];

	switch($action) {
		case 'launchPathVisio':
			$ignore = $_GET['ignoreWarning'];
			launchPathVisio(createPathwayObject($pwTitle, $oldId), $ignore);
			break;
		case 'launchCytoscape':
			launchCytoscape(createPathwayObject($pwTitle, $oldId));
			break;
		case 'launchGenMappConverter':
			launchGenMappConverter(createPathwayObject($pwTitle, $oldId));
			break;
		case 'downloadFile':
			downloadFile($_GET['type'], $pwTitle);
			break;
		case 'revert':
			revert($pwTitle, $oldId);
			break;
		case 'delete':
			delete($pwTitle);
			break;
		}
	}
} catch(Exception $e) {
	//Redirect to special page that reports the error
	ob_clean();
	header("Location: " . SITE_URL . "/index.php?title=Special:ShowError&error=" . urlencode($e->getMessage()));
	exit;
}

/**
 * Utility function to import the required javascript for the xref panel
 */
function wpiAddXrefPanelScripts() {
	global $wpiJavascriptSources, $wpiJavascriptSnippets, $jsJQuery, $jsJQueryUI, 
		$wgScriptPath, $wgStylePath, $wgOut, $wikipathwaysSearchUrl, $wpiXrefPanelDisableAttributes;
	
	//Add CSS
	//Hack to add a css that's not in the skins directory
	$oldStylePath = $wgStylePath;
	$wgStylePath = $wgScriptPath . '/wpi/js/jquery-ui';
	$wgOut->addStyle("jquery-ui-1.7.2.custom.css");
	$wgStylePath = $oldStylePath;
	
	$wpiJavascriptSources[] = $jsJQuery;
	$wpiJavascriptSources[] = $jsJQueryUI;
	$wpiJavascriptSources[] = "$wgScriptPath/wpi/js/xrefpanel.js";
	
	$wpiJavascriptSnippets[] = 'XrefPanel_searchUrl = "' . SITE_URL . '/index.php?title=Special:SearchPathways&doSearch=1&ids=$ID&codes=$DATASOURCE&type=xref";';
	if($wpiXrefPanelDisableAttributes) {
		$wpiJavascriptSnippets[] = 'XrefPanel_lookupAttributes = false;';
	}
}
		
function createPathwayObject($pwTitle, $oldid) {
	$pathway = Pathway::newFromTitle($pwTitle);
	if($oldId) {
		$pathway->setActiveRevision($oldId);
	}
	return $pathway;
}

function delete($title) {
	global $wgUser, $wgOut;
	$pathway = Pathway::newFromTitle($_GET['pwTitle']);
	if($wgUser->isAllowed('delete')) {
		$pathway = Pathway::newFromTitle($_GET['pwTitle']);
		$pathway->delete();
		echo "<h1>Deleted</h1>";
		echo "<p>Pathway $title was deleted, return to <a href='" . SITE_URL . "'>wikipathways</a>";
	} else {
		echo "<h1>Error</h1>";
		echo "<p>Pathway $title is not deleted, you have no delete permissions</a>";
		$wgOut->permissionRequired( 'delete' );
	}
	exit;
}

function revert($pwTitle, $oldId) {
	$pathway = Pathway::newFromTitle($pwTitle);
	$pathway->revert($oldId);
	//Redirect to old page
	$url = $pathway->getTitleObject()->getFullURL();
	header("Location: $url");
	exit;
}

function launchGenMappConverter($pathway) {
	global $wgUser;
		
	$webstart = file_get_contents(WPI_SCRIPT_PATH . "/applet/genmapp.jnlp");
	$pwUrl = $pathway->getFileURL(FILETYPE_GPML);
	$pwName = substr($pathway->getFileName(''), 0, -1);
	$arg = "<argument>" . htmlspecialchars($pwUrl) . "</argument>";
	$arg .= "<argument>" . htmlspecialchars($pwName) . "</argument>";
	$webstart = str_replace("<!--ARG-->", $arg, $webstart);
	$webstart = str_replace("CODE_BASE", WPI_URL . "/applet/", $webstart);
	sendWebstart($webstart, $pathway->name(), "genmapp.jnlp");//This exits script
}

function launchCytoscape($pathway) {
	global $wgUser;
		
	$webstart = file_get_contents(WPI_SCRIPT_PATH . "/bin/cytoscape/cy1.jnlp");
	$arg = createJnlpArg("-N", $pathway->getFileURL(FILETYPE_GPML));
	$webstart = str_replace(" <!--ARG-->", $arg, $webstart);
	$webstart = str_replace("CODE_BASE", WPI_URL . "/bin/cytoscape/", $webstart);
	sendWebstart($webstart, $pathway->name(), "cytoscape.jnlp");//This exits script
}

function sendWebstart($webstart, $tmpname, $filename = "wikipathways.jnlp") {
	ob_start();
	ob_clean();
	//return webstart file directly
	header("Content-type: application/x-java-jnlp-file");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Disposition: attachment; filename=\"{$filename}\"");
	echo $webstart;
	exit;
}

function getJnlpURL($webstart, $tmpname) {
	$wsFile = tempnam(getcwd() . "/tmp",$tmpname);
	writeFile($wsFile, $webstart);
	return 'http://' . $_SERVER['HTTP_HOST'] . '/wpi/tmp/' . basename($wsFile);
}

function createJnlpArg($flag, $value) {
	//return "<argument>" . $flag . ' "' . $value . '"' . "</argument>\n";
	if(!$flag || !$value) return '';
	return "<argument>" . htmlspecialchars($flag) . "</argument>\n<argument>" . htmlspecialchars($value) . "</argument>\n";
}

function downloadFile($fileType, $pwTitle) {
	$pathway = Pathway::newFromTitle($pwTitle);
	if(!$pathway->isReadable()) {
		throw new Exception("You don't have permissions to view this pathway");
	}
	
	if($fileType === 'mapp') {
		launchGenMappConverter($pathway);
	}
	ob_start();
	if($oldid = $_REQUEST['oldid']) {
		$pathway->setActiveRevision($oldid);
	}
	//Register file type for caching
	Pathway::registerFileType($fileType);
	
	$file = $pathway->getFileLocation($fileType);
	$fn = $pathway->getFileName($fileType);
	
	$mime = MimeTypes::getMimeType($fileType);
	if(!$mime) $mime = "text/plain";
	
	ob_clean();
	header("Content-type: $mime");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Disposition: attachment; filename=\"$fn\"");
	//header("Content-Length: " . filesize($file));
	set_time_limit(0);
	@readfile($file);
	exit();
}

function getClientOs() {
	$regex = array(
		'windows' => '([^dar]win[dows]*)[\s]?([0-9a-z]*)[\w\s]?([a-z0-9.]*)',
		'mac' => '(68[k0]{1,3})|(ppc mac os x)|([p\S]{1,5}pc)|(darwin)',
		'linux' => 'x11|inux');
	$ua = $_SERVER['HTTP_USER_AGENT'];
	foreach (array_keys($regex) as $os) {
		if(eregi($regex[$os], $ua)) return $os;
	}	
}
 
$spName2Code = array('Human' => 'Hs', 'Rat' => 'Rn', 'Mouse' => 'Mm');//TODO: complete

function toGlobalLink($localLink) {
	if($wgScriptPath && $wgScriptPath != '') {
		$wgScriptPath = "$wgScriptPath/";
	}
	return urlencode("http://" . $_SERVER['HTTP_HOST'] . "$wgScriptPath$localLink");
}

function writeFile($filename, $data) {
	$dir = dirname($filename);
	if(!file_exists($dir)) {
		mkdir(dirname($filename), 0777, true); //Make sure the directory exists
	}
	$handle = fopen($filename, 'w');
	if(!$handle) {
		throw new Exception ("Couldn't open file $filename");
	}
	if(fwrite($handle, $data) === FALSE) {
		throw new Exception ("Couldn't write file $filename");
	}
	if(fclose($handle) === FALSE) {
		throw new Exception ("Couln't close file $filename");
	}
}

function tag($name, $text, $attributes = array()) {
	foreach(array_keys($attributes) as $key) {
		if($value = $attributes[$key])$attr .= $key . '="' . $value . '" ';
	}
	return "<$name $attr>$text</$name>";
}

/**
 * Modified wfShellExec for java calls. This does not include the memory limit
 * on the ulimit call, since this doesn't work well with java.
 * @param $cmd Command line, properly escaped for shell.
 * @param &$retval optional, will receive the program's exit code.
 *                 (non-zero is usually failure)
 * @return collected stdout as a string (trailing newlines stripped)
 */
function wfJavaExec( $cmd, &$retval=null ) {
	global $IP, $wgMaxShellMemory, $wgMaxShellFileSize;

	if( wfIniGetBool( 'safe_mode' ) ) {
		wfDebug( "wfShellExec can't run in safe_mode, PHP's exec functions are too broken.\n" );
		$retval = 1;
		return "Unable to run external programs in safe mode.";
	}

	if ( php_uname( 's' ) == 'Linux' ) {
		$time = intval( ini_get( 'max_execution_time' ) );
		$filesize = intval( $wgMaxShellFileSize );

		if ( $time > 0) {
			$script = "$IP/bin/ulimit4-nomemory.sh";
			if ( is_executable( $script ) ) {
				$cmd = escapeshellarg( $script ) . " $time $filesize " . escapeshellarg( $cmd );
			}
		}
	} elseif ( php_uname( 's' ) == 'Windows NT' ) {
		# This is a hack to work around PHP's flawed invocation of cmd.exe
		# http://news.php.net/php.internals/21796
		$cmd = '"' . $cmd . '"';
	}
	wfDebug( "wfJavaExec: $cmd\n" );

	$retval = 1; // error by default?
	ob_start();
	passthru( $cmd, $retval );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;

}
?>
