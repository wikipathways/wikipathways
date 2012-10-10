<?php
require_once("wso2/DataServices/DataService.php");
require_once("db_config.php");

$inputFormat = array("pathwayId" => "STRING");

$sql = "SELECT * from ontology where pw_id = ? ";

$outputFormat = array("resultElement" => "wikipathways",
                      "rowElement" => "ontology",  
                      "elements" => array( "termId" => "term_id",
                                            "term" => "term",
                                           "ontology" => "ontology",
                                           "termPath" => "term_path"));

$inputFormat1 = array("pathwayId" => "STRING");

$sql1 = "SELECT COUNT(*) from ontology where pw_id = ?";

$outputFormat1 = array("resultElement" => "wikipathways",
                      "rowElement" => "ontology",  
                      "elements" => array( "count" => "COUNT(*)"));

$inputFormat2 = array("termId" => "STRING");


$sql2 = "SELECT t.tag_text, p.page_title, o.term, o.term_id, o.ontology, o.term_path , o.pw_id
FROM tag t, page p, ontology o
WHERE t.page_id = p.page_id
AND o.pw_id = p.page_title
AND t.tag_name = 'cache-name' AND o.term_id = ?";


$outputFormat2 = array("resultElement" => "wikipathways",
                      "rowElement" => "pathway",  
                      "elements" => array("pathwayId" => "pw_id",
                                          "pathwayName" => "tag_text"));

$inputFormat3 = array("termId" => "STRING");

$sql3 = "SELECT COUNT(*) from ontology where term_id = ?";

$outputFormat3 = array("resultElement" => "wikipathways",
                      "rowElement" => "pathway",  
                      "elements" => array( "count" => "COUNT(*)"));


$inputFormat4 = array("ontologyName" => "STRING");


$sql4 = "SELECT t.tag_text, p.page_title, o.term, o.term_id, o.ontology, o.term_path , o.pw_id
FROM tag t, page p, ontology o
WHERE t.page_id = p.page_id
AND o.pw_id = p.page_title
AND t.tag_name = 'cache-name' AND o.ontology = ? GROUP BY o.pw_id";

$outputFormat4 = array("resultElement" => "wikipathways",
                      "rowElement" => "pathway",  
                      "elements" => array("pathwayId" => "pw_id",
                                          "pathwayName" => "tag_text",
                                          "termId" => "term_id",
                                          "term" => "term"));

$operations = array("fetchTerms" => array("inputFormat" => $inputFormat,
                                            "sql" => $sql,
                                            "outputFormat" => $outputFormat),
                    "countTerms" => array("inputFormat" => $inputFormat1,
                                            "sql" => $sql1,
                                            "outputFormat" => $outputFormat1),
                    "fetchPathways" => array("inputFormat" => $inputFormat2,
                                            "sql" => $sql2,
                                            "outputFormat" => $outputFormat2),
                    "countPathways" => array("inputFormat" => $inputFormat3,
                                            "sql" => $sql3,
                                            "outputFormat" => $outputFormat3),
                    "ontologyOp" => array("inputFormat" => $inputFormat4,
                                            "sql" => $sql4,
                                            "outputFormat" => $outputFormat4));

$restmap = array("fetchTerms" => array("HTTPMethod" => "GET",
                                        "RESTLocation" => "pathway/{pathwayId}"),
                 "countTerms" => array("HTTPMethod" => "GET",
                                        "RESTLocation" => "countTerms/{pathwayId}"),
                 "fetchPathways" => array("HTTPMethod" => "GET",
                                        "RESTLocation" => "term/{termId}"),
                 "ontologyOp" => array("HTTPMethod" => "GET",
                                        "RESTLocation" => "ontology/{ontologyName}"),
                 "countPathways" => array("HTTPMethod" => "GET",
                                        "RESTLocation" => "countPathways/{termId}"));


$my_data_service = new DataService(array("config" => $db_config,
                                         "operations" => $operations,
                                         "RESTMapping" => $restmap));
$my_data_service->reply();




?>