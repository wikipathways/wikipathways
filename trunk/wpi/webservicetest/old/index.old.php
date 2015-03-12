<?php
error_reporting(E_ALL & ~E_NOTICE);
//ini_set("display_errors", 1);

//chdir(dirname(realpath(__FILE__)) . "/../");
//require_once('wpi.php');
//require_once('search.php');
//require_once('extensions/otag/OntologyFunctions.php');
//chdir($dir);

include('webservice.php');
include('query.php');

error_reporting(E_ALL & ~ E_NOTICE);
ini_set('display_errors', '1'); 

/*
    API Demo
 
    This script provides a RESTful API interface for a web application
 
    Input:
 
        $_GET['format'] = [ json | html | xml ]
        $_GET['method'] = []
 
    Output: A formatted HTTP response
 
    Author: Mark Roland
 
    History:
        11/13/2012 - Created
 
*/
 
// --- Step 1: Initialize variables and functions

if(!isset($_GET["format"])){
	$_GET["format"] = 'XML';	
}


 
/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/
function deliver_response($format, $api_response, $functionName = ''){

    //$state = $api_response['status'][0] == '2'?"success":"failure";
    //$api_response['state'] = $state;
    //$api_response['message'] = isset($api_response['message'])?$api_response['message']:'';
 
    // Define HTTP responses
    $http_response_code = array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found'
    );
 
    // Set HTTP Response
    //header('HTTP/1.1 '.$api_response['status'].' '.$http_response_code[ $api_response['status'] ]);
 
    // Process different content types
    if( strcasecmp($format,'json') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');
 
        // Format data into a JSON response
        $json_response = json_encode($api_response);
 
        // Deliver formatted data
        echo $json_response;
 
    }elseif( strcasecmp($format,'xml') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');
 

	$method = $_GET["method"]; 
        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = "<ns1:".$method."Response xmlns:ns1='http://www.wso2.org/php/xsd' xmlns:ns2='http://www.wikipathways.org/webservice'  >\t". array_to_xml( $api_response, "ns1") ."\n</ns1:".$method."Response>";


/*
            //$xml_response = ""; "<response>\t". array_to_xml( $api_response['data'] ) ."</response>";

	    //'<?xml version="1.0" encoding="UTF-8"?>'."\n".
            //"\t".'<state>'.$api_response['state'].'</status>'."\n".
            //"\t".'<status>'.$api_response['status'].'</status>'."\n".
            //"\t".'<code>'.$api_response['code'].'</code>'."\n".
            //"\t".'<message>'.$api_response['message'].'</message>'."\n".
            //  "\t".'<data>'.$api_response.'</data>'."\n".
            //"\t".'<data>'. array_to_xml($api_response['data']) .'</data>'."\n".
*/

 
	//print_r($api_response);

        // Deliver formatted data
        echo $xml_response;
 
    }elseif(strcasecmp($format,'dump') == 0 ){
	
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');
 
        // Deliver formatted data
        echo "<pre>";
		print_r($api_response);
	echo "</pre>";
//	var_dump($api_response);


    }else{
 
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');
 
        // Deliver formatted data
        var_dump($api_response);
 
    }
 
    // End script process
    exit;
 
}
 
// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;
 
// Define whether user authentication is required
$authentication_required = FALSE;
 
// Define API response codes and their related HTTP response
$api_response_code = array(
    0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
    1 => array('HTTP Response' => 200, 'Message' => 'Success'),
    2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
    3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
    4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
    5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
    6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format')
);
 
// Set default HTTP response of 'ok'
//$response['code'] = 0;
//$response['status'] = 404;
//$response['data'] = NULL;
 
// --- Step 2: Authorization
 
// Optionally require connections to be made via HTTPS
if( $HTTPS_required && $_SERVER['HTTPS'] != 'on' ){
    $response['code'] = 2;
    $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
    $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
    // Return Response to browser. This will exit the script.
    deliver_response($_GET['format'], $response);
}
 
// Optionally require user authentication
if( $authentication_required ){
 
    if( empty($_POST['username']) || empty($_POST['password']) ){
        //$response['code'] = 3;
        //$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        //$response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
        // Return Response to browser
        deliver_response($_GET['format'], $response);
 
    }
 
    // Return an error response if user fails authentication. This is a very simplistic example
    // that should be modified for security in a production environment
    elseif( $_POST['username'] != 'foo' && $_POST['password'] != 'bar' ){
        //$response['code'] = 4;
        //$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        //$response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
        // Return Response to browser
        deliver_response($_GET['format'], $response);
 
    }
 
}
 




// --- Step 3: Process Request

function sum2($i, $n, $l=1){
	return $i + $n;
}

$_wservices['listOrganisms'] = Array();
$_wservices['listPathways'] = Array();
$_wservices['getPathway'] = Array(
        'fieldDescription' => Array(
                                        'pwId' => 'Whatever you want to say',
                                        'revision' => 'Whatever you want to say 2'
                                )
);
$_wservices["getPathwayInfo"] = '';
$_wservices["getPathwayHistory"] = '';
$_wservices["getRecentChanges"] = '';
$_wservices["login"] = '';


$_wservices["getPathwayAs"] = '';
$_wservices["updatePathway"] = ''; 
$_wservices["createPathway"] = '';
$_wservices["findPathwaysByText"] = '';
$_wservices["findPathwaysByXref"] = '';
$_wservices["removeCurationTag"] = '';
$_wservices["saveCurationTag"] = '';
$_wservices["getCurationTags"] = '';
$_wservices["getCurationTagsByName"] = '';
$_wservices["getCurationTagHistory"] = '';
$_wservices["getColoredPathway"] = '';
$_wservices["findInteractions"] = '';
$_wservices["getXrefList"] = '';
$_wservices["findPathwaysByLiterature"] = '';
$_wservices["saveOntologyTag"] = '';
$_wservices["getOntologyTermsByPathway"] = '';
$_wservices["getOntologyTermsByOntology"] = '';
$_wservices["getPathwaysByOntologyTerm"] = '';
$_wservices["getPathwaysByParentOntologyTerm"] = '';
$_wservices["getUserByOrcid"] = '';



if(!isset($_GET['method'])){ 
	echo "<h1>List of services available</h1>";
	foreach($_wservices as $name => $value){
		echo "<h2><a href='?method=$name&describe'>".$name."</a></h2>";
	}
	exit;
}



$aInvokeParameter = array();

if(isset($_wservices[$_GET['method']]) && !isset($_GET['describe']) ){
  $fct = new ReflectionFunction($_GET["method"]);
  $iRequiredParameters =  $fct->getNumberOfRequiredParameters();
  $aParameter = $fct->getParameters();
  

//  echo $iRequiredParameters;
//  print_r($aParameter);
//  echo "<br>";
  foreach($aParameter as $value){
	foreach($value as $index => $val){
//		echo '<br>-> '.$index . " - " . $val;
		if(isset($_GET[$val])) //CHECK THIS FIX ***
			$aInvokeParameter[] = $_GET[$val];		
	}
  }

//  print_r($aInvokeParameter);
  $response = $fct->invokeArgs($aInvokeParameter);

}


if(isset($_wservices[$_GET['method']]) && isset($_GET['describe'])){
  $fct = new ReflectionFunction($_GET["method"]);
  $iRequiredParameters =  $fct->getNumberOfRequiredParameters();
  $iParameters = $fct->getNumberofParameters();
  $aParameter = $fct->getParameters();
  echo "<h1>".$_GET['method']."</h1>";

  $iCountRequired = 0;
  
  echo "<form action='' method='get'>";


  foreach($aParameter as $value){


	foreach($value as $index => $val){

	if(isset($_wservices[$_GET['method']]['fieldDescription'][$val])) $description = $_wservices[$_GET['method']]['fieldDescription'][$val];
		else $description = '';

		if($iCountRequired < $iRequiredParameters)
			echo "<b>".$val. "</b> <input type='text' name='$val' /> $description<br/>";
		else
			echo $val. " <input type='text' name='$val' /> $description<br/>";
		$iCountRequired++;
	}
  }

  echo '<p><b>Bold:</b> required parameters</p>';

  echo "<input type='hidden' name='method' value='".$_GET['method']."'>";
  echo "<input type='radio' name='format' value='json' checked='checked'> JSON";
  echo "<input type='radio' name='format' value='xml'> XML";
  echo "<input type='radio' name='format' value='html'> HTML <br/><br/>";

  echo "<input type='submit' /></form>";


//  print_r($aInvokeParameter);

}






// Method A: Say Hello to the API
if( strcasecmp($_GET['method'],'hello') == 0){
    $response['code'] = 1;
    $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
    $response['data'] = 'Hello World';
}
 
// --- Step 4: Deliver Response


 
// Return Response to browser
//print_r($response);
if(!isset($_GET['describe']))
	deliver_response($_GET['format'], $response, $_GET['method']);
 
















function is_object_array($obj){
	
	$is_numerical_array = true;
	$convArr = (array) $obj;

	foreach($convArr as $key => $value){
		//echo $key . " ";
		if(!is_numeric($key)) $is_numerical_array = false;
	}
	
	//if( $is_numerical_array ) echo "true | "; else echo "false | ";
	return $is_numerical_array;
}









function array_to_xml($array, $namespace = '', $deftag = '', $level=1){
	$xml = "";
	$debug = false;

	if(!is_array($array)){
	 	$array = Array('Result'=>$array);
	}


        if($level < 2) $namespace = "ns1"; else $namespace = "ns2";        

	foreach($array as $key => $value){
		if(is_array($value)){
			if($debug) echo "a. processing ".$key ."\n";			
			$xml .= array_to_xml($value, $namespace, $key, $level); // dont increment level, this tag is not displayed
		} else if(is_object($value)){
			if(!is_numeric($deftag) && strlen($deftag) > 0)
				$stag = $namespace != ''? $namespace . ":" . $deftag : $deftag;
			else	
				$stag = $namespace != ''? $namespace . ":" . $key : $key;

			if($debug) echo "0. processing ".$key . " -  " . $stag . " - ".$deftag."\n";			
			//echo "xxxxy - $key";
			//print_r((array)$value);

			//$xml .= "\n<$stag>" . array_to_xml(get_object_vars($value), $namespace, $key, $level + 1) . "\n</$stag>";

			if(is_object_array($value))
				$xml .= array_to_xml( ((array)$value) , $namespace, $key, $level+1); //should we increase level?
			else{
				//echo " - stringinfying " . $stag . " ";
				$xml .= "\n".str_repeat("\t",$level)."<$stag>" . array_to_xml( ((array)$value) , $namespace, $key, $level +1) . "\n".str_repeat("\t",$level)."</$stag>";
				//$xml .= str_repeat("\t",$level)."<$stag>" . array_to_xml( ((array)$value) , $namespace, $key, $level +1) . str_repeat("\t",$level)."</$stag>";
			}
			
		} else {	
			//$value = ''; //ignore value
			$value = htmlentities($value);

			if(strlen($deftag)>0 && is_integer($key)){
				if($debug) echo "1. processing ".$key . " " . $stag . "\n";
				$stag = $namespace != ''? $namespace . ":" . $deftag : $deftag; 
			        $xml .= "\n".str_repeat("\t",$level)."<$stag>$value</$stag>"; 			
			}else{
				if($debug) echo "2. processing ".$key . " " . $stag . "\n";
				$stag = $namespace != ''? $namespace . ":" . $key : $key; 
			        $xml .= "\n".str_repeat("\t",$level)."<$stag>$value</$stag>"; 
			}
		}
	}

	return $xml;
}








// support functions

function array_to_xml3($array, $level=1) {
	//var_dump($array);
	$processingArray = false;
	$xml = '';
   // if ($level==1) {
   //     $xml .= "<array>\n";
   // }
    foreach ($array as $key=>$value) {

        $key = strtolower($key);
        if (is_object($value)) {$value=get_object_vars($value);} // convert object to array
        
        if (is_array($value)) {
	    $processingArray = true;
	    $tagName = $key;
            $multi_tags = false;
	    $xml_tmp = '';

	    foreach($value as $key2=>$value2) {

	    if(is_integer($key2)) $key2 = $tagName; //.$key2; (converts values to elem)

             if (is_object($value2)) {$value2=get_object_vars($value2);} // convert object to array
                if (is_array($value2)) {
		//echo "axx";
                    $xml .= str_repeat("\t",$level)."<$key>\n";
                    $xml .= array_to_xml($value2, $level+1);
                    $xml .= str_repeat("\t",$level)."</$key>\n";
                    $multi_tags = true;
                } else {
		//echo "bxx";
                    if (trim($value2)!='') {
			/*if(is_array($value)){
			    //echo $value2;
			    $xml_tmp .= "<$tagName>".$value2."</$tagName>";
			} else*/
			if (htmlspecialchars($value2)!=$value2) {			 
                            $xml_tmp .= str_repeat("\t",$level).
                                    "<$key2><![CDATA[$value2]]>". // changed $key to $key2... didn't work otherwise.
                                    "</$key2>\n";
                        } else {
	                            $xml_tmp .= str_repeat("\t",$level).
        	                            "<$key2>$value2</$key2>\n"; // changed $key to $key2
                        }
                    }
                    $multi_tags = true;
                }
            
	    }
            if (!$multi_tags and count($value)>0) {
                $xml .= str_repeat("\t",$level)."<$key>\n";
                $xml .= array_to_xml($value, $level+1);
                $xml .= str_repeat("\t",$level)."</$key>\n";
            } else {
		$xml .= "\n".str_repeat("\t",$level) ."<$key>\n" . $xml_tmp;
		//$xml .= $xml;
		$xml .= str_repeat("\t",$level) ."</$key>\n";
	    }

      
         } else {
            if (trim($value)!='') {
             echo "value=$value<br>";
                if (htmlspecialchars($value)!=$value) {
                    $xml .= str_repeat("\t",$level)."<$key>".
                            "<![CDATA[$value]]></$key>\n";
                } else {
                    $xml .= str_repeat("\t",$level).
                            "<$key>$value</$key>\n";
                }
            }
        }
    }
   //if ($level==1) {
    //    $xml .= "</array>\n";
   // }
    return $xml;
}

/*function array2xml($array, $node_name="root") {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $root = $dom->createElement($node_name);
    $dom->appendChild($root);

    $array2xml = function ($node, $array) use ($dom, &$array2xml) {
        foreach($array as $key => $value){
            if ( is_array($value) ) {
                $n = $dom->createElement($key);
                $node->appendChild($n);
                $array2xml($n, $value);
            }else{
                $attr = $dom->createAttribute($key);
                $attr->value = $value;
                $node->appendChild($attr);
            }
        }
    };

    $array2xml($root, $array);

    return $dom->saveXML();
}*/

//function listOrganisms() {
//        return array("organisms" => Pathway::getAvailableSpecies());
//        //print_r(Pathway::getAvailableSpecies());
//}


/**
 * Get a list of all available pathways.
 * @param string $organism The organism to filter by (optional)
 * @return array of object WSPathwayInfo $pathways Array of pathway info objects
 **/
//function listPathways($organism = false) {
/*function listPathways() {

	$organism = false;

        try {
                $pathways = Pathway::getAllPathways($organism);
                $objects = array();
                foreach($pathways as $p) {
                        $objects[] = new WSPathwayInfo($p);
                }
                return array("pathways" => $objects);
        } catch(Exception $e) {
                wfDebug("ERROR: $e");
                throw new WSFault("Receiver", $e);
        }
}


function getPathway($pwId) {
        $revision = 0;
	//echo "aa";
	
        try {
                $pathway = new Pathway($pwId);
                if($revision) $pathway->setActiveRevision($revision);
                $pwi = new WSPathway($pathway);
                return array("pathway" => $pwi);
        } catch(Exception $e) {
                wfDebug("ERROR: $e");
                throw new WSFault("Receiver", $e);
        }
}*/


?>
       
