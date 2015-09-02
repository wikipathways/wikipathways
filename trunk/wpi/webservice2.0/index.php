<?php

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);


include("webservice.lib.php");
include("webservice.php");
include('wp_ext.php');

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/*$_wservices["submitRawData"] = Array("method"=>"post", "fieldtype" => Array("cellfilename"=>"FILE") );
$_wservices["getFilenamesFromRawData"] = '';
$_wservices["getArrayInformation"] = '';
$_wservices["setGroups"] = '';
$_wservices["getQCReport"] = '';
$_wservices["getFilenamesFromRawData"] = '';
$_wservices["getFilenamesFromRawData"] = '';*/


$_wservices['listOrganisms'] = Array();
$_wservices['listPathways'] = Array();
$_wservices['getPathway'] = '';
/* Array(
        'fieldDescription' => Array(
                                        'pwId' => 'Whatever you want to say',
                                        'revision' => 'Whatever you want to say 2'
                                )
);*/

$_wservices["getPathwayInfo"] = array(
				"description" => "Get some general info about the pathway, such as the name, species, without downloading the GPML.",
				"metatags" => array("pathway", "analysis")
				);



$_wservices["getPathwayHistory"] = array(
				"description" => "Get the revision history of a pathway.",
				);

$_wservices["getRecentChanges"] = array(
				"description" => "Get the recently changed pathways.<br>Note: the recent changes table only retains items for a limited time (2 months), so there is no guarantee that you will get all changes when the timestamp points to a date that is more than 2 months in the past.",
				);

;
$_wservices["login"] = array(
				"description" => "Start a logged in session, using an existing WikiPathways account. This function will return an authentication code that can be used to excecute methods that need authentication (e.g. updatePathway).",
			);

$_wservices["getPathwayAs"] = array(
				"description" => "Download a pathway in the specified file format.",
			);


$_wservices["updatePathway"] = array(
				"description" => "Update a pathway on the wiki with the given GPML code.<br>Note: To create/modify pathways via the web service, you need to have an account with web service write permissions. Please contact us to request write access for the web service."
				);

$_wservices["createPathway"] = array(
				"description" => "Create a new pathway on the wiki with the given GPML code.<br>Note: To create/modify pathways via the web service, you need to have an account with web service write permissions. Please contact us to request write access for the web service.",
				'method' => 'post'
				);
/*Array(
					'method'=>'post',
					'fieldtype'=>Array("gpml"=>"textarea")
				);*/

$_wservices["findPathwaysByText"] = "";

$_wservices["findPathwaysByXref"] = array(
					"description"=>"",
					'fieldtype'=>Array("ids"=>"array","codes"=>"array")
					);

$_wservices["removeCurationTag"] = array(
					"description"=>"Remove a curation tag from a pathway."
				);

$_wservices["saveCurationTag"] = '';

$_wservices["getCurationTags"] = array(
					"description"=>"Get all curation tags for the given tag name. Use this method if you want to find all pathways that are tagged with a specific curation tag.",
					'fieldtype'=>Array("pwId"=>"string"),
					'fieldexample'=>Array("pwId"=>"WP4")
				);

$_wservices["getCurationTagsByName"] = array(
						"description"=>"Get all curation tags for the given tag name. Use this method if you want to find all pathways that are tagged with a specific curation tag."
					);

$_wservices["getCurationTagHistory"] = array(
						"description"=>""
					);

$_wservices["getColoredPathway"] = array(
					"description"=>"Get a colored image version of the pathway.",
					'fieldtype'=>Array("graphId"=>"array","color"=>"array")
					);

$_wservices["findInteractions"] = array(
					"description"=>"Find interactions defined in WikiPathways pathways."
				);

$_wservices["getXrefList"] = '';
$_wservices["findPathwaysByLiterature"] = '';
$_wservices["saveOntologyTag"] = '';
$_wservices["removeOntologyTag"] = '';
$_wservices["getOntologyTermsByPathway"] = '';
//$_wservices["getOntologyTermsByOntology"] = '';
$_wservices["getPathwaysByOntologyTerm"] = '';
$_wservices["getPathwaysByParentOntologyTerm"] = '';
$_wservices["getUserByOrcid"] = '';



$exceptionhand = function($except){
	//should I do this?
		//header("HTTP/1.1 500 Internal Server Error");

	return array("error", $except->getCode(), $except->getMessage());
};


$ws = new BCWebService($_wservices);
$ws->setExceptionHandler($exceptionhand);
$ws->listen();


?>
