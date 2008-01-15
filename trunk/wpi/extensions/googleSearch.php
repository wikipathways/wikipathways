<?php
/*
Extension to add a google search box
Usage:
{{#googleCoop:}}
*/$wgExtensionFunctions[] = 'wfGoogleCoop';
$wgHooks['LanguageGetMagic'][]  = 'wfGoogleCoop_Magic';

function wfGoogleCoop() {
    global $wgParser;
    $wgParser->setFunctionHook( "googleCoop", "renderSearchBox" );
}

function wfGoogleCoop_Magic( &$magicWords, $langCode ) {
        $magicWords['googleCoop'] = array( 0, 'googleCoop' );
        return true;
}

# The callback function for converting the input text to HTML output
function renderSearchBox(&$parser) {
        $parser->disableCache();
        $output= <<<SEARCH
<div id="googleSearch">
<form id="searchbox_cref" action="http://www.wikipathways.org/index.php/WikiPathways:GoogleSearch">
<table width="190" frame="void" border="0">
<tr>
<td align="center" bgcolor="#eeeeee" border="1">
<input type="hidden" name="cref" value="http://www.wikipathways.org/wp_cse_context.xml" />
<input type="hidden" name="cof" value="FORID:11" />
<input type="hidden" name="filter" value="0" /> <!--set filter=0 to disable omitting similiar hits-->
<input name="q" type="text" size="20%" />
<tr><td valign="top" align="center" border="0"><input type="submit" name="sa" value="Search" />
</tr>
</table></form><script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=searchbox_cref"></script>
SEARCH;

        return array($output, 'isHTML'=>1, 'noparse'=>1);
}


# Google Custom Search Engine Extension
# 
# Tag :
#   <Googlecoop></Googlecoop>
# Ex :
#   Add this tag to the wiki page you configed at your google co-op control panel.
#   
# 
# Enjoy !

$wgExtensionFunctions[] = 'GoogleCoop';
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'Google Co-op Extension',
        'description' => 'Using Google Co-op',
        'author' => 'Liang Chen The BiGreat',
        'url' => 'http://liang-chen.com'
);

function GoogleCoop() {
        global $wgParser;
        $wgParser->setHook('Googlecoop', 'renderGoogleCoop');
}

# The callback function for converting the input text to HTML output
function renderGoogleCoop($input) {
        $output='<!-- Google Search Result Snippet Begins -->
  <div id="results_cref"></div>
  <script type="text/javascript">
    var googleSearchIframeName = "results_cref";
    var googleSearchFormName = "searchbox_cref";
    var googleSearchFrameWidth = 600;
    var googleSearchFrameborder = 0;
    var googleSearchDomain = "www.google.com";
    var googleSearchPath = "/cse";
    var googleSearchResizeIframe = false; //fixes back button issue in firefox
  </script>
  <script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>
<!-- Google Search Result Snippet Ends -->';

        return $output;
}

?>
