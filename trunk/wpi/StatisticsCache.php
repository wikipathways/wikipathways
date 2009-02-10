<?php

require_once("wpi.php");
require_once("Pathway.php");

if($argv[0] == "StatisticsCache.php") { //Called from commandline, update cache
	ini_set("memory_limit", "256M");
	echo("Updating statistics cache\n");
	$start = microtime(true);
	
	StatisticsCache::updatePathwaysCache();
	foreach(Pathway::getAvailableSpecies() as $species) {
		StatisticsCache::updateUniqueGenesCache($species);
	}
	
	$time = (microtime(true) - $start);
	echo("\tUpdated in $time seconds\n");
}

/**
Since counting unique genes accross all pathways is an expensive operation,
these calculations are cached.

This class is responsible for reading values from and writing values to that cache.
*/
class StatisticsCache
{	
	/**
	 * returns the number of unique genes for a certain species.
	 * re-creates the cache if it doesn't exist.
	 */
	public static function howManyUniqueGenes($species) 
	{
		global $wgScriptPath;
		$count = 0;

		// initialize variable $data with the contents of the cache
		$data = StatisticsCache::readGeneCache();
		// update cache if this species has never been calculated before
		if (!array_key_exists ($species, $data))
		{
			$data[$species] = StatisticsCache::countUniqueGenes ($species);
			StatisticsCache::writeGeneCache ($data);
		}
		
		return $data[$species];
	}

        /**        
	 * returns the number of pathways for a certain species.
	 * given species = 'total', it returns total number of pathways        
	 * re-creates the cache if it doesn't exist.        
 	 */
        public static function howManyPathways($species)        
	{
                global $wgScriptPath;
                $count = 0;

                // initialize variable $data with the contents of the cache                
		$data = StatisticsCache::readPathwayCache();
                
		// update cache if this species has never been calculated before                
		if (!array_key_exists ($species, $data))                
		{
                        $data = StatisticsCache::countPathways();
                        StatisticsCache::writePathwayCache($data); 
               }

               return $data[$species];
        }              	

	/**
	 * Calculates the number of unique genes in all pathways per species.
	 */
	private static function countUniqueGenes($species)
	{
		global $wgScriptPath;

		$geneList = array();
		$taggedIds = CurationTag::getPagesForTag('Curation:Tutorial');
		$all_pathways = Pathway::getAllPathways();
		foreach (array_keys($all_pathways) as $pathway) {
			if(!$all_pathways[$pathway]->isPublic()) continue; //Skip private pathways
			$pathwaySpecies = $all_pathways[$pathway]->species();
			if ($pathwaySpecies != $species) continue;
			$page_id = $all_pathways[$pathway]->getPageIdDB();
			if (in_array($page_id, $taggedIds)) continue; //skip Tutorial pathways
			try
			{
				$xml = $all_pathways[$pathway]->getPathwayData();
				$nodes = $xml->getUniqueElements('DataNode', 'TextLabel');
				foreach ($nodes as $datanode){
					$xref = $datanode->Xref;
					if ($xref[ID] && $xref[ID] != '' && $xref[ID] != ' '){
						if ($xref[Database] == 'HUGO'
						|| $xref[Database] == 'Entrez Gene'
						|| $xref[Database] == 'Ensembl'
						|| $xref[Database] == 'SwissProt'
						|| $xref[Database] == 'UniGene'
						|| $xref[Database] == 'RefSeq'
						|| $xref[Database] == 'MGI'
						|| $xref[Database] == 'RGD'
						|| $xref[Database] == 'ZFIN'
						|| $xref[Database] == 'FlyBase'
						|| $xref[Database] == 'WormBase'
						|| $xref[Database] == 'SGD'
						|| $xref[Database] == 'TAIR'
						){
							array_push($geneList, $xref[ID]);
						}
					}
				}
			}
			catch (Exception $e)
			{
				// we can safely ignore exceptions
				// erroneous pathways simply won't get counted
			}
		}
		$geneList = array_unique($geneList);
		return count ($geneList);
	}
	
        /**
         * Calculates the number of pathways for each species. Unlike countUniqueGenes(),
	 * this methods counts for all species every time. It's basically just as fast with the
	 * current logic below.
         */
        private static function countPathways()
        {
	        $dbr = wfGetDB( DB_SLAVE );
       	 	$res = $dbr->query("SELECT page_title FROM page WHERE page_namespace=" . NS_PATHWAY . " AND page_is_redirect = 0");
		$total = 0;
        	$pathwaysPerSpecies = array();
			$taggedIds = CurationTag::getPagesForTag('Curation:Tutorial');
        	while ($row = $dbr->fetchRow($res)){
                	$pathway = Pathway::newFromTitle($row["page_title"]);
			if ($pathway->isDeleted()) continue; //skip deleted pathways
			if(!$pathway->isPublic()) continue; //Skip private pathways
			$page_id = $pathway->getPageIdDB();
			if (in_array($page_id, $taggedIds)) continue; // skip Tutorial pathways
                	$species = $pathway->getSpecies();
                	if ($species == '') continue; //skip pathways without a species category
			$pathwaysPerSpecies{$species} += 1;
			$total += 1;
        	}
        	$pathwaysPerSpecies{'total'} = $total;
		return $pathwaysPerSpecies;
	}

	/**
	re-calculate the gene count for a particular species.
	This should be called when a pathway has been updated.
	*/
	public static function updateUniqueGenesCache ($species)
	{
		try
		{
			$data = StatisticsCache::readGeneCache();			
			$data[$species] = StatisticsCache::countUniqueGenes ($species);
			StatisticsCache::writeGeneCache ($data);
			
			return $data;
		} 
		catch(Exception $e) 
		{
			// likely having trouble opening files, perhaps due to permissions
			// files should have 664 permissions
		}
	}
		
        /**
        re-calculate the pathway count for all species.
        This should be called when a pathway has been created or deleted.
        */
        public static function updatePathwaysCache()
        {
                try
                {
                        $data = StatisticsCache::countPathways();
                        StatisticsCache::writePathwayCache($data);

                        return $data;
                }
                catch(Exception $e)
                {
                        // likely having trouble opening files, perhaps due to permissions
                        // files should have 664 permissions
                }
        }


	private static function writeGeneCache ($data)
	{
		global $wgScriptPath;
		// write all data in $data back to the file again
		$filename = WPI_CACHE_PATH . '/UniqueGeneCounts.data';
		$file = fopen($filename, 'w+');
		foreach ($data as $key => $c)
		{
			fwrite ($file, "$key\t$c\n");
		}
		fclose ($file);
		chmod($filename, 0666); 	
	}
	
	/**
	read the contents of the gene cache
	and return this as a set of $species => $count pairs
	*/
	private static function readGeneCache()
	{
		global $wgScriptPath;
		
		// read contents of the cache into variable $data
		$data = array();
		
		$filename = WPI_CACHE_PATH . '/UniqueGeneCounts.data';
		$file = fopen($filename, 'r');
		if ($file) 
		{
			while (!feof($file)) 
			{
				if($line = trim(fgets($file))) {
					$explodedLine = explode("\t", $line);
					$data[$explodedLine[0]] = $explodedLine[1];
				}
			}
		}
		return $data;
	}
                        
        private static function writePathwayCache ($data)
        {       
                global $wgScriptPath;
                
                // write all data in $data back to the file again
                $filename = WPI_CACHE_PATH . '/PathwayCounts.data';
                $file = fopen($filename, 'w+');
                foreach ($data as $key => $c)
                {
                        fwrite ($file, "$key\t$c\n");
                }
                fclose ($file);
                chmod($filename, 0666);   
        }
                
        /**     
        read the contents of the pathway cache
        and return this as a set of $species => $count pairs
        */              
        private static function readPathwayCache()
        {               
                global $wgScriptPath;
                        
                // read contents of the cache into variable $data
                $data = array();
                                
                $filename = WPI_CACHE_PATH . '/PathwayCounts.data';
                $file = @fopen($filename, 'r'); 
                if ($file)
                {
                        while (!feof($file))
                        {
                                if($line = trim(fgets($file))) {
                                        $explodedLine = explode("\t", $line);
                                        $data[$explodedLine[0]] = $explodedLine[1];
                                }
                        }
                }
                return $data;
        }


}

?>
