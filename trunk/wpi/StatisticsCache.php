<?php

require_once("wpi.php");
require_once("Pathway.php");

/**
Since counting unique genes accross all pathways is an expensive operation,
these calculations are cached.

This class is responsible for reading values from and writing values to that cache.
*/
class StatisticsCache
{
	/**
	calculates the number of unique genes for a certain species.
	re-creates the cache if it doesn't exist.
	*/
	public static function howManyUniqueGenes($species) 
	{
		global $wgScriptPath;
		$count = 0;

		// initialize variable $data with the contents of the cache
		$data = StatisticsCache::readCache();

		// update cache if this species has never been calculated before
		if (!array_key_exists ($species, $data))
		{
			$data[$species] = StatisticsCache::countUniqueGenes ($species);
			StatisticsCache::writeCache ($data);
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
		$all_pathways = Pathway::getAllPathways();
		foreach (array_keys($all_pathways) as $pathway) {
			$pathwaySpecies = $all_pathways[$pathway]->species();
			if ($pathwaySpecies != $species) continue;
			$name = $all_pathways[$pathway]->getName();
			if ($name == 'Sandbox') continue;
			//echo "[" . $name . "]";
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
	re-calculate the value for a particular species.
	This should be called when a pathway has been updated.
	*/
	public static function updateUniqueGenesCache ($species)
	{
		try
		{
			$data = StatisticsCache::readCache();			
			$data[$species] = StatisticsCache::countUniqueGenes ($species);
			StatisticsCache::writeCache ($data);
			
			return $data;
		} 
		catch(Exception $e) 
		{
			// likely having trouble opening files, perhaps due to permissions
			// files should have 664 permissions
		}
	}
		
	private static function writeCache ($data)
	{
		global $wgScriptPath;
		
		// write all data in $data back to the file again
		$filename = $_SERVER['DOCUMENT_ROOT'].$wgScriptPath.'wpi/tmp/UniqueGeneCounts.data';
		$file = fopen($filename, 'w+');
		foreach ($data as $key => $c)
		{
			fwrite ($file, "$key\t$c\n");
		}
		fclose ($file);		
	}
	
	/**
	read the contents of the cache
	and return this as a set of $species => $count pairs
	*/
	private static function readCache()
	{
		global $wgScriptPath;
		
		// read contents of the cache into variable $data
		$data = array();
		
		$filename = $_SERVER['DOCUMENT_ROOT'].$wgScriptPath.'wpi/tmp/UniqueGeneCounts.data';
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
