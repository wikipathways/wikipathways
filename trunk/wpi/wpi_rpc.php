<?php

error_reporting(E_ERROR); //Supress warnings etc...will disrupt the rpc response

//Load XML-RCP libraries
require("includes/xmlrpc.inc");
require("includes/xmlrpcs.inc");
require("includes/docxmlrpcs.inc");

//Load WikiPathways Interface
require("wpi.php");

//Definition of functions
$updatePathway_sig=array(array(
							$xmlrpcBoolean, 
							$xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBase64
						));

$updatePathway_doc=<<<DOC
Update a pathway on wikipathways.
<dl>
<lh>Arguments
<dt>String
	<dd>The pathway name (e.g. Apoptosis)
<dt>String
	<dd>The pathway species (e.g. Human)
<dt>String
	<dd>Description of the modifications
<dt>Base64
	<dd>The updated GPML data (base64 encoded)
</dl>
<dl>
<lh>Returns
<dt>Boolean
	<dd>True when the update was successful, false when not
</dl>
DOC;

$convertPathway_sig= array(array(
	$xmlrpcBase64,
	$xmlrpcBase64, $xmlrpcString
));

$convertPathway_doc= <<<DOC
Convert GPML code to the given file format
<dl>
<lh>Arguments
<dt>Base64
	<dd>The GPML code to convert (base64 encoded)
<dt>String
	<dd>The file extension to convert to (e.g. svg)
</dl>
<dl>
<lh>Returns
<dt>Base64
	<dd>The converted file data (base64 encoded) 
DOC;

$getGPML_sig = array(array(
	$xmlrpcBase64,
	$xmlrpcString, $xmlrpcString, $xmlrpcInt
));

$getGPML_doc = <<<DOC
Get the GPML code for a pathway
<dl>
<lh>Arguments
<dt>String
	<dd>The pathway name (e.g. Apoptosis)
<dt>String
	<dd>The pathway species (e.g. Human)
<dt>Integer
	<dd>The revision id (use '0' for current revision)
</dl>
<dl>
<lh>Returns
<dt>Base64
	<dd>The GPML code (base64 encoded)
</dl>
DOC;

//Definition of dispatch map
$disp_map=array("WikiPathways.updatePathway" => 
                        array("function" => "updatePathway",
                        "signature" => $updatePathway_sig,
			"docstring" => $updatePathway_doc),
		"WikiPathways.convertPathway" =>
			array("function" => "convertPathway",
			"signature" => $convertPathway_sig,
			"docstring" => $convertPathway_doc),
		"WikiPathways.getGPML" =>
			array("function" => "getGPML",
			"signature" => $getGPML_sig,
			"docstring" => $getGPML_doc),
);

//Setup the XML-RPC server
$s=new documenting_xmlrpc_server($disp_map,0);
$s->functions_parameters_type = 'phpvals';
//$s->setDebug(3);
$s->service();

//Function implementations
function updatePathway($pwName, $pwSpecies, $description, $gpmlData64) {
	global $xmlrpcerruser;
	
	$resp = TRUE;
	try {
		$pathway = new Pathway($pwName, $pwSpecies);
		$gpmlData = base64_decode($gpmlData64);
		$pathway->updatePathway($gpmlData, $description);
	} catch(Exception $e) {
		wfDebug("XML-RPC ERROR: $e");
		$resp = new xmlrpcresp(0, $xmlrpcerruser, $e);
	}
	ob_clean(); //Clean the output buffer, so nothing is printed before the xml response
	return $resp;
}

function convertPathway($gpmlData64, $fileType) {
	global $xmlrpcerruser;
	
	$gpmlData = base64_decode($gpmlData64);
	$gpmlFile = tempnam(WPI_TMP_PATH, "gpml");
	writeFile($gpmlFile, $gpmlData);
	$imgFile = tempnam(WPI_TMP_PATH, $fileType) . ".$fileType";
	$cmd = "java -jar bin/pathvisio_converter.jar $gpmlFile $imgFile 2>&1";
	wfDebug($cmd);
	exec($cmd, $output, $status);
	
	foreach ($output as $line) {
		$msg .= $line . "\n";
	}
	wfDebug("Converting to $fileType:\nStatus:$status\nMessage:$msg");
	if($status != 0 ) {
		return new xmlrpcresp(0, $xmlrpcerruser, "Unable to convert:\nStatus:$status\nMessage:$msg");
	}
	$imgData = file_get_contents($imgFile);
	$imgData64 = base64_encode($imgData);
	unlink($gpmlFile);
	unlink($imgFile);
	ob_clean(); //Clean the output buffer, so nothing is printed before the xml response
	return $imgData64;
}

function getGPML($pwName, $pwSpecies, $revision = 0) {
	global $xmlrpcerruser;
	
	try {
		$pathway = new Pathway($pwName, $pwSpecies);
		$gpmlData64 = base64_encode($pathway->getGPML());
		ob_clean();
		return $gpmlData64;
	} catch(Exception $e) {
		wfDebug("XML-RPC ERROR: $e");
		$resp = new xmlrpcresp(0, $xmlrpcerruser, $e);
	}
}
?>
