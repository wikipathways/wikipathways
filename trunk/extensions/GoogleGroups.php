<?php

$wgExtensionFunctions[] = 'googleGroupSetup';

function googleGroupSetup() {
    global $wgParser;
    $wgParser->setHook( 'GoogleGroupSubscribe', 'googleGroupRender' );
}

function googleGroupRender( $input, $args, $parser ) {
	$group = htmlspecialchars( $input );
        if($args['visitlink'] != 'false') {
		$visitLink = "<tr><td align=right><a href=\"http://groups.google.com/group/{$group}\">Visit this mailing list</a></td></tr>";
	}
	$msg = $args['title'];
	if(!$msg) $msg = "Subscribe to {$group}";
        
	$out = <<<HTML
<table border=0 style="background-color: #fff; padding: 5px;" cellspacing=0>
<tr><td style="padding-left: 5px">
<b>{$msg}</b>
</td></tr>
<form action="http://groups.google.com/group/{$group}/boxsubscribe">
<tr><td style="padding-left: 5px;">
Email: <input type=text name=email>
<input type=submit name="sub" value="Subscribe">
</td></tr>
</form>
${visitLink}
</table>

HTML;
        return $out;
}

?>
