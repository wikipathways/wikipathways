<?php
require_once("wpi.php");
require_once("Pathway.php");
require_once("PathwayData.php");

print "Pathway Name\tOrganism\tCategories\tUrl to WikiPathways\tLast Changed\tAuthor\tCount\tDatanodes\n";

$all_pathways = Pathway::getAllPathways();
foreach (array_keys($all_pathways) as $pathway) {

$species = $all_pathways[$pathway]->species();

//Uncomment to restrict by species
if ($species != "Homo sapiens") continue; 	

$xml = $all_pathways[$pathway]->getPathwayData();  
$gpml = $xml->getGpml();
$name = $all_pathways[$pathway]->getName();
$modTime = $all_pathways[$pathway]->getGpmlModificationTime();
$url = $all_pathways[$pathway]->getFullUrl();
$author = $gpml["Author"];
$categories = "";
$catArray = $xml->getWikiCategories();
foreach ($catArray as $cat){
	$categories .= $cat.",";
}
perlChop($categories);

print $name."\t".$species."\t".$categories."\t".$url."\t".$modTime."\t".$author."\t";

$nodes = $xml->getUniqueElements('DataNode', 'TextLabel');
//print count($nodes)."\t";

$count = 0;
$geneList = "";
foreach ($nodes as $datanode){
	$xref = $datanode->Xref;
     if ($xref['ID']){
	if ($xref[Database] == 'Entrez Gene'){
		$geneList .= $xref[ID].",";
		$count++;
	}	
     }
}
print $count."\t";
perlChop($geneList);
print $geneList."\n";

}

$dom = new DOMDocument('1.0');
$dom->formatOutput = true;
echo $dom->saveXML();

function perlChop(&$string){
        $endchar = substr("$string", strlen("$string") - 1, 1);
 	$string = substr("$string", 0, -1); 
	return $endchar;
 }

?>
