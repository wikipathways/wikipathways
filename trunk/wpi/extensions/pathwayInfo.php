<?php

require_once("wpi/wpi.php");
require_once("Article.php");
require_once("ImagePage.php");
require_once("wpi/Pathway.php");

/*
Generates info text for pathway page
- datanode
	> generate table of datanodes
*/

#### DEFINE EXTENSION
# Define a setup function
$wgExtensionFunctions[] = 'wfPathwayInfo';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayInfo_Magic';

function wfPathwayInfo() {
        global $wgParser;
        $wgParser->setFunctionHook( 'pathwayInfo', 'getPathwayInfo' );
}

function getPathwayInfo( &$parser, $pathway, $type ) {
	$parser->disableCache();
	try {
		$pathway = Pathway::newFromTitle($pathway);
		$info = new PathwayInfo($parser, $pathway);
		if(method_exists($info, $type)) {
			return $info->$type();
		} else {
			throw new Exception("method PathwayInfo->$type doesn't exist");
		}
	} catch(Exception $e) {
		return "Error: $e";
	}
}

function wfPathwayInfo_Magic( &$magicWords, $langCode ) {
        $magicWords['pathwayInfo'] = array( 0, 'pathwayInfo' );
        return true;
}

class PathwayInfo extends PathwayData {
	private $parser;

	function __construct($parser, $pathway) {
		parent::__construct($pathway);
		$this->parser = $parser;
	}
	
	/**
	 * Creates a table of all datanodes and their info
	 */
	function datanodes() {
					$table = <<<TABLE
<table class="wikitable" id="dnTable">
<tbody>
<th>Name
<th>Type
<th>Backpage Header
<th>Database reference

TABLE;
//style="border:1px #AAA solid;margin:1em 1em 0;background:#F9F9F9"
		$nodes = $this->getUniqueElements('DataNode', 'TextLabel');
			
		//Create collapse button
		$nrShow = 6;
		$nrNodes = count($nodes);
		if(count($nodes) > $nrShow) {
			$expand = "<B>View all $nrNodes DataNodes</B>";
			$collapse = "<B>View last " . ($nrShow - 1) . " DataNodes</B>";
			$button = "<p onClick='toggleRows(\"dnTable\", this, \"$expand\", 
				\"$collapse\", {$nrShow}, true)' style='cursor:pointer;color:#0000FF'>$expand</p>";
		}
		//Sort and iterate over all elements
		sort($nodes);
		foreach($nodes as $datanode) {
			$doShow = $i++ < $nrShow - 1 ? "" : "style='display:none'";
			$table .= "<tr $doShow>";
			$table .= '<td>' . $datanode['TextLabel'];
			$table .= '<td>' . $datanode['Type'];
			$table .= '<td>' . $datanode['BackpageHead'];
			$xref = $datanode->Xref;
			if($xref['ID']) {
				$table .= '<td><a href="' . getXrefLink($xref) . '">' . " $xref[ID] ($xref[Database])</a>";
			}
		}
		$table .= '</tbody></table>';
		return array($button . $table, 'isHTML'=>1, 'noparse'=>1);
	}
	
	function interactions() {
		$interactions = $this->getInteractions();
		foreach($interactions as $ia) {
			$table .= "\n|-\n";
			$table .= "| {$ia->getName()}\n";
			$table .= "|";
			$xrefs = $ia->getPublicationXRefs($this);
			if(!$xrefs) $xrefs = array();
			foreach($xrefs as $ref) {
				$attr = $ref->attributes('rdf', true);
				$table .= "<cite>" . $attr['id'] . "</cite>";
			}
		}
		if($table) {
			$table = "=== Interactions ===\n{|class='wikitable'\n" . $table . "\n|}";
		} else {
			$table = "";
		}
		return $table;
	}
}

function getXrefLink($xref) {
	$db = $xref['Database'];
	$id = $xref['ID'];

	switch($db) {
	case 'Ensembl':
		return "http://www.ensembl.org/Homo_sapiens/searchview?species=all&idx=Gene&q=" . $id;
	case 'Entrez Gene':
		return "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=gene&cmd=Retrieve&dopt=full_report&list_uids=" . $id;
	case 'SwissProt':
		return "http://www.expasy.org/uniprot/" . $id;
	case 'GenBank':
		return "http://www.ebi.ac.uk/cgi-bin/emblfetch?style=html&id=" . $id;
	case 'RefSeq':
		$ret = "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?";
		if(substr($id,0,2) == 'NM') return $ret . "db=Nucleotide&cmd=Search&term=" . $id;
		else return $ret . "db=Protein&cmd=search&term=" . $id;		
	default:
		return $id;
	}
}

/* TODO: put in switch
if(c.equalsIgnoreCase("En"))
			return "http://www.ensembl.org/Homo_sapiens/searchview?species=all&idx=Gene&q=" + id;
		if(c.equalsIgnoreCase("P"))
			return "http://www.expasy.org/uniprot/" + id;
		if(c.equalsIgnoreCase("Q")) {
			String pre = "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?";
			if(id.startsWith("NM")) {
				return pre + "db=Nucleotide&cmd=Search&term=" + id;
			} else {
				return pre + "db=Protein&cmd=search&term=" + id;
			}
		}
		if(c.equalsIgnoreCase("T"))
			return "http://godatabase.org/cgi-bin/go.cgi?view=details&search_constraint=terms&depth=0&query=" + id;
		if(c.equalsIgnoreCase("I"))
			return "http://www.ebi.ac.uk/interpro/IEntry?ac=" + id;
		if(c.equalsIgnoreCase("Pd"))
			return "http://bip.weizmann.ac.il/oca-bin/ocashort?id=" + id;
		if(c.equalsIgnoreCase("X"))
			return "http://www.ensembl.org/Homo_sapiens/featureview?type=OligoProbe;id=" + id;
		if(c.equalsIgnoreCase("Em"))
			return "http://www.ebi.ac.uk/cgi-bin/emblfetch?style=html&id=" + id;
		if(c.equalsIgnoreCase("L"))
			return "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=gene&cmd=Retrieve&dopt=full_report&list_uids=" + id;
		if(c.equalsIgnoreCase("H"))
			return "http://www.gene.ucl.ac.uk/cgi-bin/nomenclature/get_data.pl?hgnc_id=" + id;
		if(c.equalsIgnoreCase("I"))
			return "http://www.ebi.ac.uk/interpro/IEntry?ac=" + id;
		if(c.equalsIgnoreCase("M"))
			return "http://www.informatics.jax.org/searches/accession_report.cgi?id=" + id;
		if(c.equalsIgnoreCase("Om"))
			return "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=OMIM&cmd=Search&doptcmdl=Detailed&term=?" + id;
		if(c.equalsIgnoreCase("Pf"))
			return "http://www.sanger.ac.uk//cgi-bin/Pfam/getacc?" + id;
		if(c.equalsIgnoreCase("R"))
			return "http://rgd.mcw.edu/generalSearch/RgdSearch.jsp?quickSearch=1&searchKeyword=" + id;
		if(c.equalsIgnoreCase("D"))
			return "http://db.yeastgenome.org/cgi-bin/locus.pl?locus=" + id;
		if(c.equalsIgnoreCase("S"))
			return "http://www.expasy.org/uniprot/" + id;
		if(c.equalsIgnoreCase("U")) {
			String [] org_nr = id.split("\\.");
			if(org_nr.length == 2) {
				return "http://www.ncbi.nlm.nih.gov/UniGene/clust.cgi?ORG=" + 
				org_nr[0] + "&CID=" + org_nr[1];
			}
			else {
				return null;
			}
		}
		if (c.equalsIgnoreCase("Nw"))
		{
			return "http://nugowiki.org/index.php/" + id;
		}
		if (c.equalsIgnoreCase("Ca"))
		{
			return "http://chem.sis.nlm.nih.gov/chemidplus/direct.jsp?regno=" + id;
		}
		if (c.equalsIgnoreCase("Cp"))
		{
			return "http://pubchem.ncbi.nlm.nih.gov/summary/summary.cgi?cid=" + id;
		}
		if (c.equalsIgnoreCase("Ce"))
		{
			return "http://www.ebi.ac.uk/chebi/searchId=CHEBI:" + id;
		}
		if (c.equalsIgnoreCase("Ch"))
		{
			return "http://www.hmdb.ca/scripts/show_card.cgi?METABOCARD=" + id + ".txt";
		}
		if (c.equalsIgnoreCase("Ck"))
		{
			return "http://www.genome.jp/dbget-bin/www_bget?cpd:" + id;
		}
*/
?>
