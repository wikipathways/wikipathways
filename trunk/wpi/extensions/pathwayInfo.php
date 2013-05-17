<?php
require_once("Article.php");
require_once("ImagePage.php");
require_once("wpi/DataSources.php");

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
	$wgParser->setFunctionHook( 'pathwayInfo', 'getPathwayInfoText' );
}

function getPathwayInfoText( &$parser, $pathway, $type ) {
	global $wgRequest;
	$parser->disableCache();
	try {
		$pathway = Pathway::newFromTitle($pathway);
		$oldid = $wgRequest->getval('oldid');
		if($oldid) {
			$pathway->setActiveRevision($oldid);
		}
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

require_once("Pathways/Pathway.php");
/* Need autoloader here */
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
		$table = '<table class="wikitable sortable" id="dnTable">';
		$table .= '<tbody><th>Name<th>Type<th>Database reference<th>Comment';
		//style="border:1px #AAA solid;margin:1em 1em 0;background:#F9F9F9"
		$all = $this->getElements('DataNode');

		//Check for uniqueness, based on textlabel and xref
		$nodes = array();
		foreach($all as $elm) {
			$key = $elm['TextLabel'];
			$key .= $elm->Xref['ID'];
			$key .= $elm->Xref['Database'];
			$nodes[(string)$key] = $elm;
		}

		//Create collapse button
		$nrShow = 5;
		$button = "";
		$nrNodes = count($nodes);
		if(count($nodes) > $nrShow) {
			$expand = "<b>View all...</b>";
			$collapse = "<b>View last " . ($nrShow) . "...</b>";
			$button = "<table><td width='51%'> <div onClick='".
				'doToggle("dnTable", this, "'.$expand.'", "'.$collapse.'")'."' style='cursor:pointer;color:#0000FF'>".
				"$expand<td width='45%'></table>";
		}
		//Sort and iterate over all elements
		$species = $this->getOrganism();
		sort($nodes);
		$i = 0;
		foreach($nodes as $datanode) {
			$xref = $datanode->Xref;
			$xid = (string)$xref['ID'];
			$xds = (string)$xref['Database'];
			$link = DataSource::getLinkout($xid, $xds);
			$id = trim( $xref['ID'] );
			if($link) {
				$l = new Linker();
				$link = $l->makeExternalLink( $link, "$id ({$xref['Database']})" );
			} elseif( $id != '' ) {
				$link = $id;
				if($xref['Database'] != '') {
					$link .= ' (' . $xref['Database'] . ')';
				}
			}

			//Add xref info button
			$html = $link;
			if($xid && $xds) {
				$html = XrefPanel::getXrefHTML($xid, $xds, $datanode['TextLabel'], $link, $this->getOrganism());
			}

			//Comment Data
			$comment = array();
			$biopaxRef = array();
			foreach( $datanode->children() as $child ) {
				if( $child->getName() == 'Comment' ) {
					$comment[] = (string)$child;
				} elseif( $child->getName() == 'BiopaxRef' ) {
					$biopaxRef[] = (string)$child;
				}
			}

			$doShow = $i++ < $nrShow ? "" : " class='toggleMe'";
			$table .= "<tr$doShow>";
			$table .= '<td>' . $datanode['TextLabel'];
			$table .= '<td class="path-type">' . $datanode['Type'];
			$table .= '<td class="path-dbref">' . $html;
			$table .= "<td class='path-comment'>";

			$table .= self::displayItem( $comment );
			// http://developers.pathvisio.org/ticket/800#comment:9
			//$table .= self::displayItem( $biopaxRef );
		}
		$table .= '</tbody></table>';
		return array($button . $table, 'isHTML'=>1, 'noparse'=>1);
	}

	protected static function displayItem( $item ) {
		$ret = "";
		if( count( $item ) > 1 ) {
			$ret .= "<ul>";
			foreach( $item as $c ) {
				$ret .= "<li>$c";
			}
			$ret .= "</ul>";
		} elseif( count( $item ) == 1 ) {
			$ret .= $item[0];
		}
		return $ret;
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
