<?php
require_once('wpi/wpi.php');
require_once('Pager.php');
require_once('PageHistory.php');

$wgExtensionFunctions[] = "wfPathwayBibliography";

function wfPathwayBibliography() {
	global $wgParser;
	$wgParser->setHook( "pathwayBibliography", "PathwayBibliography::output" );
}

class PathwayBibliography {
	public static function output($input, $argv, &$parser) {
		$parser->disableCache();
		try {
			$pathway = Pathway::newFromTitle($parser->mTitle);
			return self::getHTML($pathway, $parser);
		} catch(Exception $e) {
			return "Error: $e";
		}
	}

	private static function getHTML($pathway, $parser) {
		global $wgUser;

		$data = $pathway->getPathwayData();
		$gpml = $pathway->getGpml();

		$i = 0;
		$nrShow = 4;

		if(!$data) return "";

		//Format literature references
		$pubXRefs = $data->getPublicationXRefs();
		foreach(array_keys($pubXRefs) as $id) {
			$doShow = $i++ < $nrShow ? "" : "style='display:none'";

			$xref = $pubXRefs[$id];

			$authors = $title = $source = $year = '';

			//Format the citation ourselves
			//Authors, title, source, year
			foreach($xref->AUTHORS as $a) {
				$authors .= "$a, ";
			}

			if($authors) $authors = substr($authors, 0, -2) . "; ";
			if($xref->TITLE) $title = $xref->TITLE . "; ";
			if($xref->SOURCE) $source = $xref->SOURCE;
			if($xref->YEAR) $year = ", " . $xref->YEAR;
			$out .= "<LI $doShow>$authors''$title''$source$year";

			if((string)$xref->ID && (strtolower($xref->DB) == 'pubmed')) {
				$l = new Linker();
				$out .= ' '. $l->makeExternalLink( 'http://www.ncbi.nlm.nih.gov/pubmed/' . $xref->ID, "PubMed" );
			}
		}

		$id = 'biblist';
		$hasRefs = (boolean)$out;
		if($hasRefs) {
			$out = "<OL id='$id'>$out</OL>";
			$nrNodes = count($pubXRefs);
			if($nrNodes > $nrShow) {
				$expand = "<B>View all $nrNodes references</B>";
				$collapse = "<B>View first " . ($nrShow) . " references</B>";
				$button = "<table><td width='51%'> <div onClick='toggleList(\"$id\", this, \"$expand\",
					\"$collapse\", " . ($nrShow) . ", true)' style='cursor:pointer;color:#0000FF'>$expand</div><td width='45%'></table>";
				$out = $button . $out;
			}
		} else {
			$out = "<I>No bibliography</i>\n";
		}
		//Handle hook template, may be used to add custom info after bibliography section
		$hookTmp = "{{#ifexist: Template:PathwayPage:BibliographyBottom | {{Template:PathwayPage:BibliographyBottom|hasRefs=$hasRefs}} | }}";
		$hookTmp = $parser->recursiveTagParse( $hookTmp );
		return $out . $hookTmp;
	}
}
