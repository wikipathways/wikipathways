<?php

$wgExtensionFunctions[] = 'wfSearchPathwaysBox';
$wgHooks['LanguageGetMagic'][]  = 'wfSearchPathwaysBox_Magic';

function wfSearchPathwaysBox() {
    global $wgParser;
    $wgParser->setFunctionHook( "searchPathwaysBox", "renderSearchPathwaysBox" );
}

function wfSearchPathwaysBox_Magic( &$magicWords, $langCode ) {
        $magicWords['searchPathwaysBox'] = array( 0, 'searchPathwaysBox' );
        return true;
}

# The callback function for converting the input text to HTML output
function renderSearchPathwaysBox(&$parser) {
        $parser->disableCache();
        $output= <<<SEARCH
<form id="searchbox_cref" action="http://test.wikipathways.org/index.php/Special:SearchPathways">
<table width="190" frame="void" border="0">
<tr>
<td align="center" bgcolor="#eeeeee" border="0">
<input name="query" type="text" size="20%" />
<input type='hidden' name='doSearch' value='1'>
<tr><td valign="top" align="center" border="0"><input type="submit" name="sa" value="Search" />
</tr>
</table></form>
SEARCH;

        return array($output, 'isHTML'=>1, 'noparse'=>1);
}

?>
