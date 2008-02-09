<?php
require_once("wpi.php");
require_once("Pathway.php");
require_once("PathwayData.php");

error_reporting(0);

//Get species restriction
$restrictSpecies = $_REQUEST['species'];

//Get output format
$outputFormat = $_REQUEST['output'];
if(!$outputFormat){
	$outputFormat = 'tab'; //set default
}


// Print header
if ($outputFormat =='html'){
print "<html><table border=1 cellpadding=3>
<tr bgcolor=\"#CCCCFF\" font><td>Pathway Name<td>Organism<td>Categories<td>Url to WikiPathways<td>Last Changed<td>Last Revision<td>Author<td>Count<td>Datanodes</tr>\n";
}
elseif ($outputFormat == 'excel'){
	//TODO (see Pear module for spreadsheet writer)
	print "Not available yet...\n";
}
else {
print "Pathway Name\tOrganism\tCategories\tUrl to WikiPathways\tLast Changed\tLast Revision\tAuthor\tCount\tDatanodes\n";

} 

$all_pathways = Pathway::getAllPathways();
foreach (array_keys($all_pathways) as $pathway) {

$species = $all_pathways[$pathway]->species();

//Apply species restriction if necessary
if($restrictSpecies) {
	if ($species != $restrictSpecies) continue; 	
}

$xml = $all_pathways[$pathway]->getPathwayData();  
$gpml = $xml->getGpml();
$name = $all_pathways[$pathway]->getName();
$modTime = $all_pathways[$pathway]->getGpmlModificationTime();
$url = $all_pathways[$pathway]->getFullUrl();
$author = $gpml["Author"];
$categories = "";
$lastRevision = $all_pathways[$pathway]->getLatestRevision();
$catArray = $xml->getWikiCategories();
foreach ($catArray as $cat){
	$categories .= $cat.",";
}
perlChop($categories);

// Print pathways data
if ($outputFormat =='html'){
print "<tr><td>".$name."<td>".$species."<td>".$categories."<td>".$url."<td>".$modTime."<td>".$lastRevision."<td>".$author."<td>";
}
elseif ($outputFormat == 'excel'){
	//TODO
}
else {
	print $name."\t".$species."\t".$categories."\t".$url."\t".$modTime."\t".$lastRevision."\t".$author."\t";
}

$count = 0;
$geneList = "";
$nodes = $xml->getUniqueElements('DataNode', 'TextLabel');
foreach ($nodes as $datanode){
	$xref = $datanode->Xref;
     if ($xref['ID']){
	if ($xref[Database] == 'Entrez Gene'){
		$geneList .= $xref[ID].",";
		$count++;
	}	
     }
}
perlChop($geneList);

//Print gene content data
if ($outputFormat =='html'){
print $count."<td>".$geneList."</tr>";
}
elseif ($outputFormat == 'excel'){
	//TODO
}
else {
	print $count."\t".$geneList."\n";
}

} // end foreach pathway

//Print footer
if ($outputFormat =='html'){
print "</table></html>";
}
elseif ($outputFormat == 'excel'){
	//TODO
}
else {
}

function perlChop(&$string){
        $endchar = substr("$string", strlen("$string") - 1, 1);
 	$string = substr("$string", 0, -1); 
	return $endchar;
 }

?>
