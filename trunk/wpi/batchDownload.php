<?php
require_once('includes/zip.lib.php');
require_once('wpi.php');

//As mediawiki extension
$wgExtensionFunctions[] = "wfBatchDownload";

function wfBatchDownload() {
    global $wgParser;
    $wgParser->setHook( "batchDownload", "createDownloadLinks" );
}

//To be called directly
if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
	wfDebug("PROCESSING BATCH DOWNLOAD\n");
	$species = $_GET['species'];
	$fileType = $_GET['fileType'];
	$listPage = $_GET['listPage'];
	$onlyCategorized = $_GET['onlyCategorized'];
	$tag = $_GET['tag'];
	
	if($species) {
		batchDownload($species, $fileType, $listPage, $onlyCategorized, $tag);
	}
}

function createDownloadLinks($input, $argv, &$parser) {
	$fileType = $argv['filetype'];
	$listPage = $argv['listpage'];
	$onlyCategorized = $argv['onlycategorized'];
	$tag = $argv['tag'];
	
	if($listPage) {
		$listParam = '&listPage=' . $listPage;
	}
	if($onlyCategorized) {
		$onlyCategorizedParam = '&onlyCategorized=true';
	}
	if($tag) {
		$tagParam = "&tag=$tag";
	}
	
	foreach(Pathway::getAvailableSpecies() as $species) {
		$html .= tag('li', 
					tag('a',$species,array('href'=> WPI_URL . '/' . "batchDownload.php?species=$species&fileType=$fileType$listParam$onlyCategorizedParam$tagParam", 'target'=>'_new')));
	}
	$html = tag('ul', $html);
	return $html;
}

function batchDownload($species, $fileType, $listPage = '', $onlyCategorized = false, $tag = '') {
/*
	if(!(
		$fileType == FILETYPE_GPML ||
		$fileType == FILETYPE_IMG ||
		$fileType == FILETYPE_PNG)) {
		throw new Exception("Invalid file type: $fileType");
	}
*/
	if($listPage) {
		$allpws = getPathwaysByList($listPage);
		$pathways = array();
		//Apply additional filter by species
		foreach($allpws as $p) {
			$pspecies = str_replace(' ', '_', $p->species());
			if($pspecies == $species) {
				$pathways[] = $p;
			}
		}
	} else {
		$pathways = Pathway::getAllPathways();
		$filtered = array();
		foreach($pathways as $p) {  //Filter by species
			if($p->getSpecies() == $species) {
				$filtered[] = $p;
			}
		}
		$pathways = $filtered;
	}
	//Filter out non categorized pathways
	if($onlyCategorized) {
		$allCats = CategoryHandler::getAvailableCategories();
		$allCats = str_replace(' ', '_', $allCats);
		$filtered = array();
		foreach($pathways as $p) {
			$ch = $p->getCategoryHandler();
			$cats = $ch->getCategories();
			foreach($cats as $c) {
				if(in_array($c, $allCats)) {
					$filtered[] = $p;
					break;
				}
			}
		}
		$pathways = $filtered;
	}
	if($tag) {
		$filtered = array();
		$pages = MetaTag::getPagesForTag($tag);
		foreach($pathways as $p) {
			$id = $p->getTitleObject()->getArticleId();
			if(in_array($id, $pages)) {
				$tag = new MetaTag($tag, $id);
				$rev = $tag->getPageRevision();
				if($rev) {
					$p->setActiveRevision($rev);
				}
				$filtered[] = $p;
			}
		}
		$pathways = $filtered;
	}
	doDownload($pathways, $fileType); //Exits script
}

function getPathwaysByList($listPage) {
	$pathways = Pathway::parsePathwayListPage($listPage);
	return $pathways;
}

function getPathways($conditions = array()) {
	$conditions = array_merge($conditions,
		array(
			'page_namespace' => NS_PATHWAY,
			'page_is_redirect' => 0,
			"page_title LIKE 'WP%'"
		)
	);
	$dbr =& wfGetDB( DB_SLAVE );
	$res = $dbr->select( 'page',
		array( 'page_title' ),
		$conditions
	);

	$pathways = array();
	while($s = $dbr->fetchObject( $res ) ) {
			$t = $s->page_title;
			try {
				$pw = Pathway::newFromTitle($t);
				$pw->registerFileType($fileType);
				array_unshift($pathways, $pw);
			} catch(Exception $e) {
				wfDebug("Unable to create pathway object", $e);
			}
	}
	return $pathways;
}

function doDownload($pathways, $fileType) {
	ob_start();
	
	if(is_null($pathways) || count($pathways) == 0) {
		$e = "'''Unable process download:''' No pathways matching your criteria";
		header("Location: " . SITE_URL . "index.php?title=Special:ShowError&error=" . urlencode($e));
		exit();
	}
	
/*	$zip = new zipfile();
	
	//Fill zip file
	foreach($pathways as $pw) {
		$file = $pw->getFileLocation($fileType);
		$zip->addFile(file_get_contents($file), basename($file));
	}
	$zipData = $zip->file();
*/	
	$zipFile = tempnam(WPI_TMP_PATH, 'batchDownload') . '.zip';
	foreach($pathways as $pw) {
		$files .= $pw->getFileLocation($fileType) . ' ';
	}
	$cmd = "zip -j $zipFile $files 2>&1";
	exec($cmd, $output, $status);
	foreach($output as $line) {
		$msg .= $line . "\n";
	}
	if($status != 0) {
		exit("<H1>Unable process download</H1><P>$msg</P>");
	}

	$time = time();
	ob_clean();
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Content-Disposition: attachment; filename=wikipathways_$time.zip");
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: binary");
	//header("Content-Length: ".filesize($zipFile));
	set_time_limit(0); //In case reading file takes a long time
	readfile_chunked($zipFile);
}

function readfile_chunked($filename,$retbytes=true) { 
   $chunksize = 1*(1024*1024); // how many bytes per chunk 
   $buffer = ''; 
   $cnt =0; 
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
   } 
   while (!feof($handle)) { 
       $buffer = fread($handle, $chunksize); 
       echo $buffer; 
       ob_flush(); 
       flush(); 
       if ($retbytes) { 
           $cnt += strlen($buffer); 
       } 
   } 
       $status = fclose($handle); 
   if ($retbytes && $status) { 
       return $cnt; // return num. bytes delivered like readfile() does. 
   } 
   return $status; 

} 

?>
