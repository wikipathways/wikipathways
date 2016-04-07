<?php

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);


include("webservice.lib.php");
include("webservice.php");
include('ws_ext.php');

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/*$_wservices["submitRawData"] = Array("method"=>"post", "fieldtype" => Array("cellfilename"=>"FILE") );
$_wservices["getFilenamesFromRawData"] = '';
$_wservices["getArrayInformation"] = '';
$_wservices["setGroups"] = '';
$_wservices["getQCReport"] = '';
$_wservices["getFilenamesFromRawData"] = '';
$_wservices["getFilenamesFromRawData"] = '';*/


$_wservices['listOrganisms'] = array(
				"metatags" => array("Organism list", "All functions"),
			);

$_wservices['listPathways'] = array(
				"metatags"=> array("Pathway list", "All functions"),
			);

$_wservices['getPathway'] = array(
			"metatags"=>array("Pathway information", "All functions"),
);

/* Array(
        'fieldDescription' => Array(
                                        'pwId' => 'Whatever you want to say',
                                        'revision' => 'Whatever you want to say 2'
                                )
);*/

$_wservices["getPathwayInfo"] = array(
				"description" => "Get some general info about the pathway, such as the name, species, without downloading the GPML.",
				"metatags" => array("Pathway information","All functions"),
				);



$_wservices["getPathwayHistory"] = array(
				"description" => "Get the revision history of a pathway.",
				"metatags" => array("History", "All functions"),
				);

$_wservices["getRecentChanges"] = array(
				"description" => "Get the recently changed pathways.<br>Note: the recent changes table only retains items for a limited time (2 months), so there is no guarantee that you will get all changes when the timestamp points to a date that is more than 2 months in the past.",
				"metatags" => array("History","All functions"),
				);

;
$_wservices["login"] = array(
				"description" => "Start a logged in session, using an existing WikiPathways account. This function will return an authentication code that can be used to excecute methods that need authentication (e.g. updatePathway).",
				"metatags" => array("User management", "All functions"),
			);

$_wservices["getPathwayAs"] = array(
				"description" => "Download a pathway in the specified file format.",
				"metatags" => array("Download", "All functions"),
			);


$_wservices["updatePathway"] = array(
				"description" => "Update a pathway on the wiki with the given GPML code.<br>Note: To create/modify pathways via the web service, you need to have an account with web service write permissions. Please contact us to request write access for the web service.",
				"metatags" => array("Write (create/update/delete)", "All functions"),
				);

$_wservices["createPathway"] = array(
				"description" => "Create a new pathway on the wiki with the given GPML code.<br>Note: To create/modify pathways via the web service, you need to have an account with web service write permissions. Please contact us to request write access for the web service.",
				"method" => 'post',
				"metatags" => array("All functions", "Write (create/update/delete)"),
				);
/*Array(
					'method'=>'post',
					'fieldtype'=>Array("gpml"=>"textarea")
				);*/

$_wservices["findPathwaysByText"] = array(
					"metatags" => array("All functions", "Search")
				);

$_wservices["findPathwaysByXref"] = array(
					"description"=>"",
					'fieldtype'=>Array("ids"=>"array","codes"=>"array"),
					"metatags" => array("All functions", "Search")
					);

$_wservices["removeCurationTag"] = array(
					"description"=>"Remove a curation tag from a pathway.",
					"metatags" => array("All functions", "Search")
				);

$_wservices["saveCurationTag"] = array(
					"metatags" =>array("All functions", "Write (create/update/delete)", "Curation tags")
				);

$_wservices["getCurationTags"] = array(
					"description"=>"Get all curation tags for the given tag name. Use this method if you want to find all pathways that are tagged with a specific curation tag.",
					'fieldtype'=>Array("pwId"=>"string"),
					'fieldexample'=>Array("pwId"=>"WP4"),
					"metatags" =>array("All functions", 'Pathway information', 'Curation tags')
				);

$_wservices["getCurationTagsByName"] = array(
						"description"=>"Get all curation tags for the given tag name. Use this method if you want to find all pathways that are tagged with a specific curation tag.",
						"metatags" =>array("All functions", "Pathway list", "Curation tags"),
					);

$_wservices["getCurationTagHistory"] = array(
						"description"=>"",
						"metatags" =>array("All functions", 'History', 'Curation tags')
					);

$_wservices["getColoredPathway"] = array(
					"description"=>"Get a colored image version of the pathway.",
					"fieldtype"=>array("graphId"=>"array","color"=>"array"),
					"metatags" =>array("All functions", "Download")
					);

$_wservices["findInteractions"] = array(
					"description"=>"Find interactions defined in WikiPathways pathways.",
					"metatags"=>array("Search","All functions"),
				);

$_wservices["getXrefList"] =  array(
                                                 "metatags"=>array("Download", "All functions"),
                                        );
$_wservices["findPathwaysByLiterature"] = array(
						 "metatags"=>array("Search", "All functions"),
					);
$_wservices["saveOntologyTag"] = array(
				"metatags"=>array("Write (create/update/delete)", "Ontology tags", "All functions"),
			);
$_wservices["removeOntologyTag"] = array(
				"metatags"=>array("Write (create/update/delete)", "Ontology tags", "All functions"),
			);
$_wservices["getOntologyTermsByPathway"] = array(
				"metatags"=>array("Pathway information", "Curation tags", "All functions"),
			);
//$_wservices["getOntologyTermsByOntology"] = '';
$_wservices["getPathwaysByOntologyTerm"] = array(
						"metatags"=>array("Pathway list", "Ontology tags", "All functions"),
					);

$_wservices["getPathwaysByParentOntologyTerm"] = array(
						"metatags"=>array("Pathway list", "Ontology tags", "All functions"),
					);
$_wservices["getUserByOrcid"] = array(
					"metatags"=>array("User management", "All functions"),
				);



$exceptionhand = function($except){
	//should I do this?
		//header("HTTP/1.1 500 Internal Server Error");

	return array("error", $except->getCode(), $except->getMessage());
};


$ws = new BCWebService($_wservices);
$ws->setExceptionHandler($exceptionhand);
$ws->listen();


?>
