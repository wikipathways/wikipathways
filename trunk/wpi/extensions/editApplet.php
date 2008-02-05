<?php
require_once('wpi/wpi.php');

$wgExtensionFunctions[] = 'wfEditApplet';
$wgHooks['LanguageGetMagic'][]  = 'wfEditApplet_Magic';

$loaderAdded = false; //Set to true if loader is added in a previous call
$scriptsAdded = false; //Set to true if script dependencies are added

function wfEditApplet() {
	global $wgParser;
	$wgParser->setFunctionHook( "editApplet", "createApplet" );
}

function wfEditApplet_Magic( &$magicWords, $langCode ) {
	$magicWords['editApplet'] = array( 0, 'editApplet' );
	return true;
}

/**
 * Creates the applet
 * @paramater type, type of the applet to start (editor, bibliography, ...)
 * @parameter $idClick Id of the element to attach an 'onclick' event 
 * to that will trigger the applet to start. If this argument equals 'direct', 
 * the applet will be activated directly.
 * @parameter $idReplace Id of the element that will be replaced by the applet
 * @parameter $new Whether the pathway is yet to be created (will be passed on to the applet)
 * @parameter $pwTitle The title of the pathway to be edited (Species:Pathwayname)
*/
function createApplet( &$parser, $idClick = 'direct', $idReplace = 'pwThumb', $new = false, $pwTitle = '', $type = 'editor', $width = 0, $height = '500px' ) {
	global $wgUser, $wgScriptPath, $loaderAdded, $scriptsAdded;
	
	//Check user rights
	if( !$wgUser->isLoggedIn() || wfReadOnly()) {
		return ""; //Don't return any applet code
	}
	
	$parser->disableCache();
	
	$param = array(); //Extra parameters
	$main = 'org.pathvisio.gui.wikipathways.';
	$noresize = 'false';
	switch($type) {
		case 'bibliography': 
		$main .= 'BibliographyApplet';
		$noresize = 'true';
		break;
		case 'description':
		$main .= 'DescriptionApplet';
		$noresize = 'true';
		break;
		case 'categories':
		$main .= 'CategoryApplet';
		$cats = implode(',', Pathway::getAvailableCategories());
		$param = array('categories' => $cats);
		$noresize = 'true';
		break;
		default: $main .= 'AppletMain';
	}
	
	try {
		if(!$pwTitle) {
			$pathway = Pathway::newFromTitle($parser->mTitle);
		} else {
			$pathway = Pathway::newFromTitle($pwTitle);
		}
		$editApplet = new EditApplet($pathway, $main, $idReplace, $idClick, $new, $width, $height, $noresize, $param);
		$appletCode = $editApplet->makeAppletFunctionCall();
		$jardir = $wgScriptPath . '/wpi/applet';
		
		/** Don't use jar preloading for now
		if(!$loaderAdded) {
			$cache = $editApplet->getCacheParameters();
			$archive_string = $cache["archive"];
			$version_string = $cache["version"];
			$appletCode .= <<<PRELOAD

<applet code="org.pathvisio.wikipathways.Preloader.class" width="1" height="1" archive="{$jardir}/preloader.jar" codebase="{$jardir}">
	<param name="cache_archive" value="{$archive_string}"/>
	<param name="cache_version" value="{$version_string}"/>
</applet>
PRELOAD;
			$loaderAdded = true;
		}
		**/
		if(!$scriptsAdded) {
			$scripts = scriptTag('', JS_SRC_PROTOTYPE) . scriptTag('', JS_SRC_RESIZE) . scriptTag('', JS_SRC_EDITAPPLET);
			$scriptsAdded = true;
		}
		$output = $scripts . $appletCode;
	} catch(Exception $e) {
		return "Error: $e";
	}

	return array($output, 'isHTML'=>1, 'noparse'=>1);
}

function scriptTag($code, $src = '') {
	$src = $src ? 'src="' . $src . '"' : '';
	return '<script type="text/javascript" ' . $src . '>' . $code . '</script>';
}

function createJsArray($array) {
	$jsa = "new Array(";      
	foreach($array as $elm) {
		$jsa .= "'{$elm}', ";
	}
	return substr($jsa, 0, strlen($jsa) - 2) . ')';
}

function increase_version($old) {
	//echo("increasing version: $old\n");
	$numbers = explode('.', $old);
	$last = hexdec($numbers[count($numbers) - 1]);
	$numbers[count($numbers) - 1] = dechex(++$last);
	//echo("increased to: " . implode('.', $numbers));
	return implode('.', $numbers);
}


class EditApplet {
	private $pathway;
	private $mainClass;
	private $idReplace;
	private $idClick;
	private $isNew;
	private $width, $height;
	private $param;
	private $noresize;

	function __construct($pathway, $mainClass, $idReplace, $idClick, $isNew, $width, $height, $noresize, $param = array()) {
		$this->pathway = $pathway;
		$this->mainClass = $mainClass;
		$this->idReplace = $idReplace;
		$this->idClick = $idClick;
		$this->isNew = $isNew;
		$this->width = $width;
		$this->height = $height;
		$this->param = $param;
		$this->noresize = $noresize;
	}

	private static $version_string = false;
	private	static $archive_string = false;
	
	static function getCacheParameters() {
		if(self::$version_string && self::$archive_string) {
			return array("version"=>self::$version_string, "archive"=>self::$archive_string);
		}
		//Read cache jars and update version
		$jardir = WPI_SCRIPT_PATH . '/applet';
		if(!file_exists("$jardir/cache_version")) {
			touch("$jardir/cache_version");
		}
		$cache_archive = explode(' ', file_get_contents("$jardir/cache_archive"));
		$version_file = explode("\n", file_get_contents("$jardir/cache_version"));
		$cache_version = array();
		if($version_file) {
			foreach($version_file as $ver) {
				$jarver = explode("|", $ver);
				if($jarver && count($jarver) == 3) {
					$cache_version[$jarver[0]] = array('ver'=>$jarver[1], 'mod'=>$jarver[2]);
				}
			}
		}
		self::$archive_string = "";
		self::$version_string = "";
		foreach($cache_archive as $jar) {
			$mod = filemtime("$jardir/$jar");
			if($ver = $cache_version[$jar]) {
				if($ver['mod'] < $mod) {
					$realversion = increase_version($ver['ver']);
				} else {
					$realversion = $ver['ver'];
				}
			} else {
				$realversion = '0.0.0.0';
			}
			$cache_version[$jar] = array('ver'=>$realversion, 'mod'=>$mod);
			self::$archive_string .= $jar . ', ';
			self::$version_string .= $realversion . ', ';
		}
		self::$version_string = substr(self::$version_string, 0, -2);
		self::$archive_string = substr(self::$archive_string, 0, -2);

		//Write new cache version file
		$out = "";
		foreach(array_keys($cache_version) as $jar) {
			$out .= $jar . '|' . $cache_version[$jar]['ver'] . '|' . $cache_version[$jar]['mod'] . "\n";
		}
		writefile("$jardir/cache_version", $out);
		return array("archive"=>self::$archive_string, "version"=>self::$version_string);
	}
	
	static function getParameterArray($pathway, $new = 0, $param = array()) {
		global $wgUser;
		if($new) {
			$pwUrl = $pathway->getTitleObject()->getFullURL();
		} else {
			$pwUrl = $pathway->getFileURL(FILETYPE_GPML);
		}

		$cache = self::getCacheParameters();
		$archive_string = $cache["archive"];
		$version_string = $cache["version"];
				
		$args = array(
			'rpcUrl' => WPI_URL . "/wpi_rpc.php",
			'pwName' =>     $pathway->name(),
			'pwSpecies' => $pathway->species(),
			'pwUrl' => $pwUrl,
			'cache_archive' => $archive_string,
			'cache_version' => $version_string,
			'gdb_server' => $_SERVER['HTTP_HOST'],
			'revision' => $new ? '0' : $pathway->getLatestRevision()
		);

		if($wgUser && $wgUser->isLoggedIn()) {
			$args = array_merge($args, array('user' => $wgUser->getRealName()));
		}
		if($new) {
			$args = array_merge($args, array('new' => true));
		}
		$args = array_merge($args, $param);
		return $args;
	}
	
	function getJsParameters() {
		$args = self::getParameterArray($this->pathway, $this->isNew, $this->param);
		$keys = createJsArray(array_keys($args));
		$values = createJsArray(array_values($args));
		return array('keys' => $keys, 'values' => $values); 
	}
	
	function makeAppletObjectCall() {
		$param = $this->getJsParameters();
		$base = self::getAppletBase();
		$keys = $param['keys'];
		$values = $param['values'];
		return "doApplet('{$this->idReplace}', 'applet', '$base', '{$this->mainClass}', '{$this->width}', '{$this->height}', {$keys}, {$values}, {$this->noresize});";
	}

	static function getAppletBase() {
		global $wgScriptPath;
		return "$wgScriptPath/wpi/applet";
	}
	
	function makeAppletFunctionCall() {
		$base = self::getAppletBase();
		$param = $this->getJsParameters();
		$keys = $param['keys'];
		$values = $param['values'];
		
		$function = $this->makeAppletObjectCall();
		if($this->idClick == 'direct') {
			return scriptTag($function);
		} else {
			return scriptTag(
				"var elm = document.getElementById('{$this->idClick}');" . 
				"var listener = function() { $function };" .
				"if(elm.attachEvent) { elm.attachEvent('onclick',listener); }" .
				"else { elm.addEventListener('click',listener, false); }" .
				"registerAppletButton('{$this->idClick}', '$base', $keys, $values);"
			);
		}
	}
}
?>
