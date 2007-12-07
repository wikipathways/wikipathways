<?php
require_once('wpi/wpi.php');

$wgExtensionFunctions[] = 'wfDiffView';
$wgHooks['DiffViewHeader'][]  = 'wfDiffView';

function wfDiffView ( $diff, $oldRev, $newRev ) 
{
	global $wgOut;
	
	$wgOut->addHtml("<blink>BLINK!</blink>");
	
	return false;
}

?>
