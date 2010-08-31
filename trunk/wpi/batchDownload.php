<?php
require_once('includes/zip.lib.php');
require_once('wpi.php');

//As mediawiki extension
$wgExtensionFunctions[] = "wfBatchDownload";

//Register the supported file types
Pathway::registerFileType(FILETYPE_PDF);
Pathway::registerFileType(FILETYPE_PWF);
Pathway::registerFileType(FILETYPE_TXT);
Pathway::registerFileType(FILETYPE_BIOPAX);

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
	$excludeTags = $_GET['tag_excl'];
	$displayStats = $_GET['stats'];
	
	if($species) {
		try {
		$batch = new BatchDownloader(
			$species, $fileType, $listPage, $onlyCategorized, $tag, split(';', $excludeTags), $displayStats
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
	private $excludeTags;
	private $displayStats;
	
	function __construct($species, $fileType, $listPage = '', $onlyCategorized = false, $includeTag = '', $excludeTags = NULL, $displayStats = false) {
		$this->species = $species;
		$this->fileType = $fileType;
		$this->listPage = $listPage;
		$this->onlyCategorized = $onlyCategorized;
		$this->tag = $includeTag;
		if($excludeTags && count($excludeTags) > 0) {
			$this->excludeTags = $excludeTags;
		}
		$this->stats = $displayStats;
	}
	
	static function createDownloadLinks($input, $argv, &$parser) {
		$fileType = $argv['filetype'];
		$listPage = $argv['listpage'];
		$onlyCategorized = $argv['onlycategorized'];
		$tag = $argv['tag'];
		$excludeTags = $argv['excludetags'];
		$displayStats = $argv['stats'];
	
		if($listPage) {
			$listParam = '&listPage=' . $listPage;
			$listedPathways = Pathway::parsePathwayListPage($listPage);
			foreach ($listedPathways as $pw) {
                        	$countPerSpecies[$pw->getSpecies()] += 1;
                	}
		}
		if($onlyCategorized) {
			$onlyCategorizedParam = '&onlyCategorized=true';
		}
		if($tag) {
			$tagParam = "&tag=$tag";
			$taggedPageIds = CurationTag::getPagesForTag("$tag");
                	foreach ($taggedPageIds as $pageId) {
                        	$countPerSpecies[Pathway::newFromTitle(Title::newFromId($pageId))->getSpecies()] += 1;
                	}
		}
		if($excludeTags) {
			$excludeParam = "&tag_excl=$excludeTags";
		}
		foreach(Pathway::getAvailableSpecies() as $species) {
			$nrPathways =  $countPerSpecies[$species]; 
                	if($displayStats) {
                        	$stats = "\t\t($nrPathways)";
                	} else if(!$listPage && !$tag) {
				$nrPathways = 1;  // list all if not filtering and counting
			}
			if ($nrPathways > 0) {  // skip listing species with 0 pathways
				$html .= tag('li', 
						tag('a',$species . $stats,array('href'=> WPI_URL . '/' . "batchDownload.php?species=$species&fileType=$fileType$listParam$onlyCategorizedParam$tagParam$excludeParam", 'target'=>'_new')));
			}
		}
		$html = tag('ul', $html);
		return $html;
	}
	
	private function createZipName() {
		$cat = $this->onlyCategorized ? "_categorized" : '';
		$list = $this->listPage ? "_{$this->listPage}" : '';
		$t = $this->tag ? "_{$this->tag}" : '';
		$et = '';
		if($this->excludeTags) {
			$str = implode('.', $this->excludeTags);
			$et = "_$str";
		}
		$fileName = "wikipathways_" . $this->species . 
					$cat . $list . $t . $et . "_{$this->fileType}.zip";
		$fileName = str_replace(' ', '_', $fileName);
		//Filter out illegal chars
		$fileName = preg_replace( "/[\/\?\<\>\\\:\*\|\[\]]/", '-', $fileName);
		
		return WPI_CACHE_PATH . "/" . $fileName;
	}
	
	private function getCached() {
		$zipFile = $this->createZipName();
		if(file_exists($zipFile)) {
			$tsZip = filemtime($zipFile);
			
			//Check if file is still valid (based on the latest pathway edit)
			$latest = wfTimestamp(TS_UNIX, MwUtils::getLatestTimestamp(NS_PATHWAY));
			
			//If the download is based on curation tags, also check the last modification
			//on the used tags
			if($this->tag || $this->excludeTags) {
				$checkTags = array();
				if($this->tag) $checkTags[] = $this->tag;
				if($this->excludeTags) {
					foreach($this->excludeTags as $t) $checkTags[] = $t;
				}
				$hist = CurationTag::getAllHistory(wfTimestamp(TS_MW, $tsZip));
				foreach($hist as $h) {
					if(in_array($h->getTagName(), $checkTags)) {
						$action = $h->getAction();
						if($action == MetaTag::$ACTION_CREATE || $action == MetaTag::$ACTION_REMOVE) {
							$latestTag = wfTimestamp(TS_UNIX, $h->getTime());
							break;
						}
					}
				}
			}
			if($latestTag > $latest) $latest = $latestTag;
			
			if($latest > $tsZip) {
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
		//Delete old file if exists
		if(file_exists($zipFile)) unlink($zipFile);
		
		//Create symlinks to the cached gpml files,
		//with a custom file name (containing the pathway title)
		$files = "";
		$tmpLinks = array();
		$tmpDir = WPI_TMP_PATH . "/" . wfTimestamp(TS_UNIX);
		mkdir($tmpDir);
		foreach($pathways as $pw) {
			$link = $tmpDir . "/" . $pw->getFilePrefix() . "_" . $pw->getIdentifier() . "_"
					 . $pw->getActiveRevision() . "." . $this->fileType;
			$cache = $pw->getFileLocation($this->fileType);
			link($cache, $link);
			$tmpLinks[] = $link;
			$files .= '"' . $link . '" ';
		}
		$cmd = "zip -j \"$zipFile\" $files 2>&1";
		$output = wfShellExec($cmd, $status);
		
		//Remove the tmp files
		foreach($tmpLinks as $l) unlink($l);
		rmdir($tmpDir);
		
		if($status != 0) {
			throw new Exception("'''Unable process download:''' $output");
		}
		return $zipFile;
	}
	
	function listPathways() {
		if($this->listPage) {
			$allpws = Pathway::parsePathwayListPage($this->listPage);
			$pathways = array();
			//Apply additional filter by species
			foreach($allpws as $p) {
				if($p->getSpecies() == $this->species) {
					$pathways[$p->getIdentifier()] = $p;
				}
			}
		} else {
			$pathways = Pathway::getAllPathways();
			$filtered = array();
			foreach($pathways as $p) {  //Filter by species
				if($p->getSpecies() == $this->species) {
					$filtered[$p->getIdentifier()] = $p;
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
						$filtered[$p->getIdentifier()] = $p;
						break;
					}
				}
			}
			$pathways = $filtered;
		}
		
		//Include only pathways with a given tag
		if($this->tag) {
			$filtered = array();
			$pages = MetaTag::getPagesForTag($this->tag);
			foreach($pathways as $p) {
				$id = $p->getTitleObject()->getArticleId();
				if(in_array($id, $pages)) {
					$tag = new MetaTag($this->tag, $id);
					$rev = $tag->getPageRevision();
					if($rev) {
						$p->setActiveRevision($rev, false);
					}
					$filtered[$p->getIdentifier()] = $p;
				}
			}
			$pathways = $filtered;
		}
		//Filter out certain tags
		$filtered = array();
		if($this->excludeTags) {
			$pages = array();
			foreach($this->excludeTags as $t) {
				$pages = array_merge($pages, MetaTag::getPagesForTag($t));
			}
			foreach($pathways as $p) {
				$id = $p->getTitleObject()->getArticleId();
				if(!in_array($id, $pages)) {
					$filtered[$p->getIdentifier()] = $p;
				}
			}
			$pathways = $filtered;
		}
		//Filter for private pathways
		$filtered = array();
		foreach($pathways as $p) {
			if($p->isPublic()) { //Filter out all private pathways
				$filtered[$p->getIdentifier()] = $p;
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
