<?php
/**
Provide an information and cross-reference panel for xrefs on a wiki page.

<Xref id="1234" datasource="L" species="Homo sapiens">Label</Xref>

**/

$wgExtensionFunctions[] = "XrefPanel::xref";



class XrefPanel {
	static function xref() {
		global $wgParser;
		$wgParser->setHook( "Xref", "XrefPanel::renderXref" );

		wpiAddXrefPanelScripts();
	}

	static function renderXref($input, $argv, &$parser) {
		return wpiXrefHTML($argv['id'], $argv['datasource'], $input, $argv['species']);
	}

	static function getXrefHTML($id, $datasource, $label, $text, $species) {
		$datasource = json_encode($datasource);
		$label = json_encode($label);
		$id = json_encode($id);
		$species = json_encode($species);
		$url = SITE_URL . '/skins/common/images/info.png';
		$fun = "XrefPanel.registerTrigger(this, $id, $datasource, $species, $label);";
		$html = $text . " <img title='Show additional info and linkouts' style='cursor:pointer;' onload='$fun' src='$url'/>";
		return $html;
	}

	static function getJsDependencies() {
		global $jsJQueryUI, $wgScriptPath;

		$js = array(
			"$wgScriptPath/wpi/js/xrefpanel.js",
			$jsJQueryUI
		);

		return $js;
	}

	static function getJsSnippets() {
		global $wpiXrefPanelDisableAttributes, $wpiBridgeUrl,
			$wpiBridgeUseProxy;

		$js = array();

		$js[] = 'XrefPanel_searchUrl = "' . SITE_URL . '/index.php?title=Special:SearchPathways&doSearch=1&ids=$ID&codes=$DATASOURCE&type=xref";';
		if($wpiXrefPanelDisableAttributes) {
			$js[] = 'XrefPanel_lookupAttributes = false;';
		}

		$bridge = "XrefPanel_dataSourcesUrl = '" . WPI_CACHE_URL . "/datasources.txt';\n";

		if($wpiBridgeUrl !== false) { //bridgedb web service support can be disabled by setting $wpiBridgeDb to false
			if(!isset($wpiBridgeUrl) || $wpiBridgeUseProxy) {
				//Point to bridgedb proxy by default
				$bridge .= "XrefPanel_bridgeUrl = '" . WPI_URL . '/extensions/bridgedb.php' . "';\n";
			} else {
				$bridge .= "XrefPanel_bridgeUrl = '$wpiBridgeUrl';\n";
			}
		}
		$js[] = $bridge;

		return $js;
	}

	static function addXrefPanelScripts() {
		global $wpiJavascriptSources, $wpiJavascriptSnippets, $cssJQueryUI, $wgScriptPath, $wgStylePath, $wgOut, $jsRequireJQuery;

		$jsRequireJQuery = true;

		//Add CSS
		//Hack to add a css that's not in the skins directory
		$oldStylePath = $wgStylePath;
		$wgStylePath = dirname($cssJQueryUI);
		$wgOut->addStyle(basename($cssJQueryUI));
		$wgStylePath = $oldStylePath;

		$wpiJavascriptSources = array_merge($wpiJavascriptSources, self::getJsDependencies());
		$wpiJavascriptSnippets = array_merge($wpiJavascriptSnippets, self::getJsSnippets());
	}
}
