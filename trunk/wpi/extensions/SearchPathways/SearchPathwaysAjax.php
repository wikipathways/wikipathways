<?php
require_once("search.php");

$wgAjaxExportList[] = "SearchPathwaysAjax::doSearch";
$wgAjaxExportList[] = "SearchPathwaysAjax::getResults";

class SearchPathwaysAjax {
	public static function parToXref($ids, $codes) {
		$ids = explode(',', $ids);
		$codes = explode(',', $codes);
		if(count($xrefs) > count($codes)) $singleCode = $codes[0];
		for($i = 0; $i < count($ids); $i += 1) {
			if($singleCode) $c = $singleCode;
			else $c = $codes[$i];
			$x = new XRef($ids[$i], $c);
			$xrefs[] = $x;
		}
		return($xrefs);
	}
	
	public static function doSearch($query, $species, $ids, $codes, $type) {
		if (!$type || $type == '') $type = 'query';
                if ((!$query || $query =='') && $type == 'query') $query = 'glucose';
		if ($species == 'ALL SPECIES') $species = '';
		if($type == 'query') {
			$results = PathwayIndex::searchByText($query, $species);
		} elseif ($type == 'xref'){
			$xrefs = self::parToXref($ids, $codes);
			$results = PathwayIndex::searchByXref($xrefs, true);
		}
		$doc = new DOMDocument();
		$root = $doc->createElement("results");
		$doc->appendChild($root);

		foreach($results as $r) {
			$pwy = $r->getFieldValue(PathwayIndex::$f_source);
			$rn = $doc->createElement("pathway");
			$rn->appendChild($doc->createTextNode($pwy));
			$root->appendChild($rn);
		}
		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return($resp);
	}
	
	public static function getResults($pathwayTitles, $searchId) {
		$html = "";
		foreach(explode(",", $pathwayTitles) as $t) {
			$pathway = Pathway::newFromTitle($t);
			$name = $pathway->name();
			$species = $pathway->getSpecies();
			$href = $pathway->getFullUrl();
			$caption = "<a href=\"$href\">$name ($species)</a>";
			$caption = html_entity_decode($caption);         //This can be quite dangerous (injection)
			$output = SearchPathways::makeThumbNail($pathway, $caption, $href, '', 'none', 'thumb', 200);
			preg_match('/height="(\d+)"/', $output, $matches);
			$height = $matches[1];
			if ($height > 160){
				$output = preg_replace('/height="(\d+)"/', 'height="160px"', $output);
			}
			$output = "<div class='thumbholder'>$output</div>";
			$html .= "\n" . $output;
		}
		
		$doc = new DOMDocument();
		$root = $doc->createElement("results");
		$doc->appendChild($root);

		$ni = $doc->createElement("searchid");
		$ni->appendChild($doc->createTextNode($searchId));
		$root->appendChild($ni);
		
		$nh = $doc->createElement("htmlcontent");
		$nh->appendChild($doc->createTextNode($html));
		$root->appendChild($nh);
		
		$resp = new AjaxResponse(trim($doc->saveXML()));
		$resp->setContentType("text/xml");
		return($resp);
	}
}
?>
