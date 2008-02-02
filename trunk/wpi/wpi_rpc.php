<?php

error_reporting(E_ERROR); //Supress warnings etc...will disrupt the rpc response

//Load XML-RCP libraries
require("includes/xmlrpc.inc");
require("includes/xmlrpcs.inc");
require("includes/docxmlrpcs.inc");

//Load WikiPathways Interface
require("wpi.php");

//Definition of functions
$updatePathway_sig=array(
	array(
		$xmlrpcBoolean, 
		$xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBase64,
		$xmlrpcInt
	),
	array(
		$xmlrpcBoolean,
		$xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, 
		$xmlrpcInt, $xmlrpcStruct
	),
);

$updatePathway_doc= "Update a pathway on wikipathways.";

$updatePathway_docsig = array(
	array(
		"True when the update was successful, false when not",
		"The pathway name (e.g. Apoptosis)",
		"The pathway species (e.g. Human)",
		"Description of the modifications",
		"The updated GPML data (base64 encoded)",
		"The revision id on which the updated GPML is based"
	),
	array(
		"True when the update was successful, false when not",
		"The pathway name (e.g. Apoptosis)",
		"The pathway species (e.g. Human)",
		"Description of the modifications",
		"The updated GPML data (base64 encoded)",
		"The revision id on which the updated GPML is based",
		"The authentication data, a struct with the key/value pairs:" .
		"<BR>'user', the username<BR>'token', the authentication token"
	)
);

$convertPathway_sig= array(
	array(
		$xmlrpcBase64,
		$xmlrpcBase64, $xmlrpcString
	)
);

$convertPathway_doc= "Convert GPML code to the given file format";

$convertPathway_docsig = array(
	array(
		"The converted file data (base64 encoded)",
		"The GPML code to convert (base64 encoded)",
		"The file extension to convert to (e.g. svg)",
	)
);

$getPathway_sig = array(
	array(
		$xmlrpcStruct,
		$xmlrpcString, $xmlrpcString),
	array(
		$xmlrpcStruct,
		$xmlrpcString, $xmlrpcString, $xmlrpcInt),
);

$getPathway_doc = "Get the GPML code for a pathway";

$getPathway_docsig = array(
	array(
		"A struct containing the following key/value pairs:<dl>" .
		"<dt>gpml<dd>The GPML code (base64 encoded)" .
		"<dt>revision<dd>The revision id of the returned GPML",
		"The pathway name (e.g. Apoptosis)",
		"The pathway species (e.g. Human)"
	),
	array(
		"A struct containing the following key/value pairs:<dl>" .
		"<dt>gpml<dd>The GPML code (base64 encoded)" .
		"<dt>revision<dd>The revision id of the returned GPML",
		"The pathway name (e.g. Apoptosis)",
		"The pathway species (e.g. Human)",
		"The revision id (use '0' for current revision)"
	)
);

$login_sig = array(
	array(
		$xmlrpcString,
		$xmlrpcString, $xmlrpcString
	)
);

$login_doc = "Start a logged in session, using an existing WikiPathways account. 
This will return an authentication code
that can be used to excecute methods that need authentication (e.g. 
updatePathway)";

$login_docsig = array(
	array (
		"The authentication code",
		"The user name",
		"The password"
	)
);

//Definition of dispatch map
$disp_map=array(
		"WikiPathways.updatePathway" => 
			array("function" => "updatePathway",
			"signature" => $updatePathway_sig,
			"docstring" => $updatePathway_doc,
			"signature_docs" => $updatePathway_docsig),
		"WikiPathways.convertPathway" =>
			array("function" => "convertPathway",
			"signature" => $convertPathway_sig,
			"docstring" => $convertPathway_doc,
			"signature_docs" => $convertPathway_docsig),
		"WikiPathways.getPathway" =>
			array("function" => "getPathway",
			"signature" => $getPathway_sig,
			"docstring" => $getPathway_doc,
			"signature_docs" => $getPathway_docsig),
		"WikiPathways.login" =>
			array("function" => "login",
			"signature" => $login_sig,
			"docstring" => $login_doc,
			"signature_docs" => $login_docsig),
);

//Setup the XML-RPC server
$s=new documenting_xmlrpc_server($disp_map,0);
$s->functions_parameters_type = 'phpvals';
//$s->setDebug(3);
$s->service();

//Function implementations
function updatePathway($pwName, $pwSpecies, $description, $gpmlData64, $revision, $auth = NULL) {
	global $xmlrpcerruser, $wgUser;
	
	//Authenticate first, if token is provided
	if($auth) {
		try {
			authenticate($auth['user'], $auth['token']);
		} catch(Exception $e) {
			return new xmlrpcresp(0, $xmlrpcerruser, $e);
		}
	}
		
	$resp = TRUE;
	try {
		$pathway = new Pathway($pwName, $pwSpecies);
		//Only update if the given revision is the newest
		if($revision == $pathway->getLatestRevision()) {
			$gpmlData = base64_decode($gpmlData64);
			$pathway->updatePathway($gpmlData, $description);
		} else {
			$resp = new xmlrpcresp(0, $xmlrpcerruser,
				"Revision out of date: your GPML code originates from " .
				"an old revision. This means somebody else modified the pathway " .
				"since you downloaded it. Please apply your changes on the newest version"
			);
		}
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
	$cmd = "java -jar bin/pathvisio_core.jar $gpmlFile $imgFile 2>&1";
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

function getPathway($pwName, $pwSpecies, $revision = 0) {
	global $xmlrpcerruser;
	
	try {
		$pathway = new Pathway($pwName, $pwSpecies);
		$revision = $pathway->getLatestRevision();
		$gpmlData64 = base64_encode($pathway->getGPML());
		ob_clean();
		return array(
    			"gpml" => $gpmlData64,
    			"revision" => $revision
    		);
	} catch(Exception $e) {
		wfDebug("XML-RPC ERROR: $e");
		$resp = new xmlrpcresp(0, $xmlrpcerruser, $e);
	}
}

function login($name, $pass) {
	global $wgUser, $wgAuth;
	
	$user = User::newFromName( $name );
	if( is_null($user) || $user->getID() == 0) {
		return new xmlrpcresp(0, $xmlrpcerruser, "Invalid user name");
	}
	$user->load();
	if ($user->checkPassword( $pass )) {
		$wgAuth->updateUser($user);
		$wgUser = $user;
		return $user->mToken;
	} else {
		return new xmlrpcresp(0, $xmlrpcerruser, "Wrong password");
	}
}

//Non-rpc functions
function authenticate($username, $token) {
	global $wgUser, $wgAuth;
	
	$user = User::newFromName( $username );
	if( is_null($user) || $user->getID() == 0) {
		throw new Exception("Invalid user name");
	}
	$user->load();
	if ($user->mToken == $token) {
			$wgAuth->updateUser($user);
			$wgUser = $user;
	} else {
		throw new Exception("Wrong authentication token");
	}
}
?>
