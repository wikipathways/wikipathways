<?php
require_once('includes/zip.lib.php');
require_once('wpi.php');

//As mediawiki extension
$wgExtensionFunctions[] = "wfBatchDownload";

//Register the supported file types
Pathway::registerFileType(FILETYPE_PDF);
Pathway::registerFileType(FILETYPE_PWF);
Pathway::registerFileType(FILETYPE_TXT);

function wfBatchDownload() {
    global $wgParser;
    $wgParser->setHook( "batchDownload", "BatchDownloader::createDownloadLinks" );
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
		try {
		$batch = new BatchDownloader(
			$species, $fileType, $listPage, $onlyCategorized, $tag
		);
		$batch->download();
		} catch(Exception $e) {
			ob_clean();
			header("Location: " . SITE_URL . "/index.php?title=Special:ShowError&error=" . urlencode($e->getMessage()));
			exit;
		}
	}
}

class BatchDownloader {
	private $species;
	private $fileType;
	private $listPage;
	private $onlyCategorized;
	private $tag;
	
	function __construct($species, $fileType, $listPage = '', $onlyCategorized = false, $tag = '') {
		$this->species = $species;
		$this->fileType = $fileType;
		$this->listPage = $listPage;
		$this->onlyCategorized = $onlyCategorized;
		$this->tag = $tag;
	}
	
	static function createDownloadLinks($input, $argv, &$parser) {
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
	
	private function createZipName() {
		$cat = $this->onlyCategorized ? "_categorized" : '';
		$list = $this->listPage ? "_$listPage" : '';
		$t = $this->tag ? "_$tag" : '';
		$fileName = "wikipathways_" . $this->species . 
					$cat . $list . $t . "_{$this->fileType}.zip";
		$fileName = str_replace(' ', '_', $fileName);
		return WPI_CACHE_PATH . "/" . $fileName;
	}
	
	private function getCached() {
		$zipFile = $this->createZipName();
		if(file_exists($zipFile)) {
			//Check if file is still valid (based on the latest pathway edit)
			$latest = wfTimestamp(TS_UNIX, MwUtils::getLatestTimestamp(NS_PATHWAY));
			if($latest > filemtime($zipFile)) {
				return null;
			} else {
				return $zipFile;
			}
		} else {
			return null;
		}
	}
	
	public function download() {
		if(!Pathway::isValidFileType($this->fileType)) {
			throw new Exception("Invalid file type: {$this->fileType}");
		}
		
		//Try to find a cached download file and validate
		$zipFile = '';
		if($zipFile = $this->getCached()) {
			wfDebug(__METHOD__ . ": using cached file $zipFile\n");
			$this->doDownload($zipFile);
		} else {
			wfDebug(__METHOD__ . ": no cached file, creating new batch download file\n");
			$this->doDownload($this->createZipFile($this->listPathways()));
		}
	}
	
	private function createZipFile($pathways) {
		if(is_null($pathways) || count($pathways) == 0) {
			throw new Exception("'''Unable process download:''' No pathways matching your criteria");
		}
	
		$zipFile = $this->createZipName();
		foreach($pathways as $pw) {
			$files .= $pw->getFileLocation($this->fileType) . ' ';
		}
		$cmd = "zip -j '$zipFile' $files 2>&1";
		$output = wfShellExec($cmd, $status);
		if($status != 0) {
			throw new Exception("'''Unable process download:''' $output");
		}
		return $zipFile;
	}
	
	function listPathways() {
		if($this->listPage) {
			$allpws = getPathwaysByList($this->listPage);
			$pathways = array();
			//Apply additional filter by species
			foreach($allpws as $p) {
				$pspecies = str_replace(' ', '_', $p->species());
				if($pspecies == $species && $this->species) {
					$pathways[] = $p;
				}
			}
		} else {
			$pathways = Pathway::getAllPathways();
			$filtered = array();
			foreach($pathways as $p) {  //Filter by species
				if($p->getSpecies() == $this->species) {
					$filtered[] = $p;
				}
			}
			$pathways = $filtered;
		}
		//Filter out non categorized pathways
		if($this->onlyCategorized) {
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
		if($this->tag) {
			$filtered = array();
			$pages = MetaTag::getPagesForTag($this->tag);
			foreach($pathways as $p) {
				$id = $p->getTitleObject()->getArticleId();
				if(in_array($id, $pages)) {
					$tag = new MetaTag($this->tag, $id);
					$rev = $tag->getPageRevision();
					if($rev) {
						$p->setActiveRevision($rev);
					}
					$filtered[] = $p;
				}
			}
			$pathways = $filtered;
		}
		//Filter for private pathways
		$filtered = array();
		foreach($pathways as $p) {
			if($p->isPublic()) { //Filter out all private pathways
				$filtered[] = $p;
			}
		}
		return $filtered;	
	}
	
	function getPathwaysByList($listPage) {
		$pathways = Pathway::parsePathwayListPage($listPage);
		return $pathways;
	}

	function doDownload($file) {
		//redirect to the cached file
		$url = WPI_CACHE_URL . '/' . basename($file);
		ob_start();
		ob_clean();
		header("Location: $url");
		exit();
	}
}

?>
