<?php

include("webservice.lib.php");
include("webservice.php");
include('query.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

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

$_wservices["getPathwayInfo"] = '';
$_wservices["getPathwayHistory"] = '';
$_wservices["getRecentChanges"] = '';
$_wservices["login"] = '';

$_wservices["getPathwayAs"] = '';
$_wservices["updatePathway"] = '';
$_wservices["createPathway"] = array('method'=>'post');
/*Array(
					'method'=>'post',
					'fieldtype'=>Array("gpml"=>"textarea")
				);*/
$_wservices["findPathwaysByText"] = '';
$_wservices["findPathwaysByXref"] = array('fieldtype'=>Array("ids"=>"array","codes"=>"array"));
$_wservices["removeCurationTag"] = '';
$_wservices["saveCurationTag"] = '';
$_wservices["getCurationTags"] = '';
$_wservices["getCurationTagsByName"] = '';
$_wservices["getCurationTagHistory"] = '';
$_wservices["getColoredPathway"] = array('fieldtype'=>Array("graphId"=>"array","color"=>"array"));
$_wservices["findInteractions"] = '';
$_wservices["getXrefList"] = '';
$_wservices["findPathwaysByLiterature"] = '';
$_wservices["saveOntologyTag"] = '';
$_wservices["removeOntologyTag"] = '';
$_wservices["getOntologyTermsByPathway"] = '';
//$_wservices["getOntologyTermsByOntology"] = '';
$_wservices["getPathwaysByOntologyTerm"] = '';
$_wservices["getPathwaysByParentOntologyTerm"] = '';
$_wservices["getUserByOrcid"] = '';




$ws = new BCWebService($_wservices);
$ws->listen();


?>
