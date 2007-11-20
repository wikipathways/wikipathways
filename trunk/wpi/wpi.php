<?php
require_once('globals.php');
//Initialize MediaWiki
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../includes').PATH_SEPARATOR.realpath('../').PATH_SEPARATOR);
$dir = getcwd();
chdir("../"); //Ugly, but we need to change to the MediaWiki install dir to include these files, otherwise we'll get an error
require_once ( 'WebStart.php');
require_once( 'Wiki.php' );
chdir($dir);

require_once( 'Pathway.php' );

//Parse HTTP request (only if script is directly called!)
if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
$action = $_GET['action'];
switch($action) {
	case 'launchPathVisio':
		$pathway = Pathway::newFromTitle($_GET['pwTitle']);
		$ignore = $_GET['ignoreWarning'];
		launchPathVisio($pathway, $ignore);
		break;
	case 'downloadFile':
		downloadFile($_GET['type'], $_GET['pwTitle']);
		break;
	case 'revert':
		revert($_GET['pwTitle'], $_GET['oldId']);
		break;
	case 'new':
		$pathway = new Pathway($_GET['pwName'], $_GET['pwSpecies'], false);
		$ignore = $_GET['ignoreWarning'];
		launchPathVisio($pathway, $ignore, true);
		break;
	case 'delete':
		delete($_GET['pwTitle']);
		break;
	}
}

function delete($title) {
	global $wgUser;
	$pathway = Pathway::newFromTitle($_GET['pwTitle']);
	if($wgUser->isAllowed('delete')) {
		$pathway = Pathway::newFromTitle($_GET['pwTitle']);
		$pathway->delete();
		echo "<h1>Deleted</h1>";
		echo "<p>Pathway $title was deleted, go back to <a href=http://{$_SERVER['HTTP_HOST']}>wikipathways</a>";
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

function launchPathVisio($pathway, $ignore = null, $new = false) {
	global $wgUser;
		
	$webstart = file_get_contents(WPI_SCRIPT_PATH . "/applet/wikipathways.jnlp");
	$arg .= createJnlpArg("-RPC_URL", "http://" . $_SERVER['HTTP_HOST'] . "/wpi/wpi_rpc.php");
	$arg .= createJnlpArg("-PW_NAME", $pathway->name());
	$arg .= createJnlpArg("-PW_SPECIES", $pathway->species());
	if($new) {
		$arg .= createJnlpArg("-PW_URL", $pathway->getTitleObject()->getFullURL());
	} else {
		$arg .= createJnlpArg("-PW_URL", $pathway->getFileURL(FILETYPE_GPML));
	}
	if($wgUser && $wgUser->isLoggedIn()) {
		$arg .= createJnlpArg("-USER", $wgUser->getRealName());
	}
	if($new) {
		$arg .= createJnlpArg("-PW_NEW", "1");
	}
	$webstart = str_replace("<!--ARG-->", $arg, $webstart);

	$msg = null;
	if( $wgUser->isLoggedIn() ) {
		if( $wgUser->isBlocked() ) {
			$msg = "Warning: your user account is blocked!";
		}
	} else {
		$msg = "Warning: you are not logged in! You will not be able to save modifications to WikiPathways.org.";
	}
	if($msg && !$ignore) { //If $msg is not null, then we have an error
		$name = $pathway->name();
		$url = $pathway->getFullURL();
		$title = $pathway->getTitleObject()->getPartialURL();
		$jnlp = $wpiScript . "?action=launchPathVisio&pwTitle=$title&ignoreWarning=1";
		$script = 
<<<JS
<html>
<body>
<p>Back to <a href={$url}>{$name}</a></p>
<script type="text/javascript">
var view = confirm("{$msg} You will not be able to save modifications to WikiPathways.org.\\n\\nDo you still want to open the pathway?");
if(view) {
window.location="{$jnlp}";
} else {
history.go(-1);
}
</script>
</body>
</html>
JS;
		echo($script);
		exit;
	}
	sendWebstart($webstart, $pathway->name());//This exits script
}

function sendWebstart($webstart, $tmpname) {
	ob_start();
	ob_clean();
	//return webstart file directly
	header("Content-type: application/x-java-jnlp-file");
	header("Content-Disposition: attachment; filename=\"WikiPathways.jnlp\"");
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
	ob_start();
	$pathway = Pathway::newFromTitle($pwTitle);
	if($oldid = $_REQUEST['oldid']) {
		$pathway->setActiveRevision($oldid);
	}
	$file = $pathway->getFileLocation($fileType);
	$fn = $pathway->getFileName($fileType);
	
	ob_clean();
	switch($fileType) {
		case FILETYPE_GPML:
			header("Content-type: text/xml");
			break;
		case FILETYPE_IMG:
			header("Content-type: image/svg+xml");
			break;
		case FILETYPE_PNG:
			header("Content-type: image/png");
			break;
	}
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Disposition: attachment; filename=\"$fn\"");
	header("Content-Length: " . filesize($file));
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
?>
