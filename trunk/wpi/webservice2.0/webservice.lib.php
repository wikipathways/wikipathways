<?php

/*TODO: check max file size*/
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);


class BCWebService{


var $functionArray;
var $exceptionHandler;

var $phpdoc;
var $canPopulateFromPhpDoc = true;


function __construct($functionArray){
	
	$this->functionArray = $functionArray;
}


function populateFromPhpDoc(){}

function isParamRequired($functionName, $paramName){}

function getSupportedMethod($functionName){
/*	echo $functionName;
	var_dump( $this->functionArray[$functionName]["method"] );*/
	
	if( is_array($this->functionArray[$functionName]) && array_key_exists( "method", $this->functionArray[$functionName] )){		
//		var_dump( $this->functionArray[$functionName]);


		if(is_array( $this->functionArray[$functionName]["method"] )){
			return $this->functionArray[$functionName]["method"];
		}else{
			return array( $this->functionArray[$functionName]["method"] );
		}
	} else {
		return array("get");
	}
}


/*possible properties
fieldtype, fielddescription
*/
function getField($functionName, $fieldName, $property="fieldtype"){

	if(isset($this->functionArray[$functionName][$property][$fieldName])){
		return $this->functionArray[$functionName][$property][$fieldName];
	}else{ 
		return "string";
	}
}

/*
	
*/
function getFunction($functioname, $property){
	if(isset($this->functionArray[$functionName][$property][$fieldName])){
                return $this->functionArray[$functionName][$property][$fieldName];
        }else{ 
                return "unknown/mixed";
        }
}


function getSwaggerFunctionParameters($func){
	$params = array();
	
	$fct = new ReflectionFunction($func);
        $iRequiredParameters =  $fct->getNumberOfRequiredParameters();
  	$iParameters = $fct->getNumberofParameters();
 	$aParameter = $fct->getParameters();
	$parameterCount = 0;


	foreach($aParameter as $value){
		$parameterCount++;
        	foreach($value as $index => $val){
			$params[] = array(
				"name" => $val,
				"required" => $parameterCount > $iRequiredParameters ? false : true,
				"in" => "query",
				"type"=> $this->getField($func , $val),
				"description"=> $this->getField($func, $val, "fielddescription"),
				//"type"=>"array",
				//"collectionFormat"=>"multi"
			);
		}
	}

	$params[] = array(
		"name" => "format",
		"required" => false,
		"in" => "query",
		"type" => "string",
		"default"=>"xml",
		"enum" => array("json","xml","html","dump","jpg","pdf"),
	);


	return $params;

}

function parseParam($data){
	$data = explode(" ", $data, 3);
	

	/*if object, than an object is specified*/
	if($param[1]!="object"){
		$data["type"] = $data[0];
		$data["param"] = substr($data[1],1);
		$data["description"] = $data[2];
	} else {
		$data = explode(" ",$data,4);
		$data["type"] = $data[0];
		$data["param"] = substr($data[2], 1);
		$data["description"] = $data[3];
	}
	return $data;
}

function getDescription($funci){

			foreach($this->functionArray as $func => $description){	
			

			$fct = new ReflectionFunction($func);
			if($fct->getDocComment()==false)
				$comment = "";
			else
				$comment = $func. " true- " .$fct->getDocComment();

			if (preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $fct->getDocComment(), $matches)){
				  $result = $this->array_combine_($matches[1], $matches[2]);

				  preg_match('/\*\*(.|\n*)+\* @/', $fct->getDocComment(), $matches);
				  $match = $matches[0];
 				  //echo "->".$match."<-";
			}


			/*set description*/

			if(!isset($this->functionArray[$func][description]))
				$this->functionArray[$func]["description"] = $result["description"];

			/*set param type and description*/

			if(is_array($result["param"])){
				foreach($result["param"] as $param){
					$aParam = $this->parseParam($param);
					if(!isset($this->functionArray[$func]["fieldtype"][ $aParam["param"] ])) $this->functionArray[$func]["fieldtype"][ $aParam["param"] ] = swaggerTypeConverter($aParam["type"]);
					if(!isset($this->functionArray[$func]["fielddescription"][ $aParam["param"] ])) $this->functionArray[$func]["fielddescription"][ $aParam["param"] ] = $aParam["description"];
				}
			}else{
				if(isset($result["param"])){
					$aParam = $this->parseParam($result["param"]);
					if(!isset($this->functionArray[$func]["fieldtype"][ $aParam["param"] ])) $this->functionArray[$func]["fieldtype"][ $aParam["param"] ] =   swaggerTypeConverter($aParam["type"]);
					if(!isset($this->functionArray[$func]["fielddescription"][ $aParam["param"] ])) $this->functionArray[$func]["fielddescription"][ $aParam["param"] ] = $aParam["description"];
				}
			}


			if($aParam == "array"){
				//***
			}
				

			/*set return type and description*/
                        if(!isset($this->functionArray[$func]["returndescription"]))
				$aReturn = $this->parseParam($result["return"]);
                                $this->functionArray[$func]["returndescription"] = $aReturn["description"];
                                $this->functionArray[$func]["returntype"] = $aReturn["type"];
			}

			//var_dump($func);
			//var_dump($result);

			//var_dump($this->functionArray);
			
}


function array_combine_($keys, $values)
{
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = $values[$i];
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return    $result;
}

function getSwaggerCalls(){
	$swagDesc = array();

	foreach($this->functionArray as $func => $description){
		 $swagDesc["/".$func] = array();
		 $methods = array();
		 $comment = "";

			$sm = $this->getSupportedMethod($func);

			$this->getDescription($func);

			foreach($sm as $sm_elem){
			$methods[$sm_elem] = array(
				"description" => $comment .$func. ( isset( $this->functionArray[$func]["description"] ) ? $this->functionArray[$func]["description"]: "" ),
				"produces" => array("application/json", "application/xml", "text/html", "text/xml"),
				"parameters" => $this->getSwaggerFunctionParameters($func),
				"responses" => array(
						200 => array("description"=>"everything ok"), 
						
						),	
				);
			if(isset( $this->functionArray[$func]["metatags"] ))  $methods[$sm_elem]["tags"] = $this->functionArray[$func]["metatags"];

			//$methods[] = $sm_elem;
			}
		$swagDesc["/".$func] = $methods;

	}

	return $swagDesc;
}

function getSwagger(){
	$swagger = array(
		"swagger" => "2.0",
		"info" => array(
			"title" => "WikiPathways Webservices",
			"version" => "1.0"
		),
		"host" => "webservice.wikipathways.org",
		"schemes" => array("http"),
		"basePath" => "/",
 		"paths" => $this->getSwaggerCalls()
		);

	return json_encode($swagger);
}

function setExceptionHandler($exceptionh){
	$this->exceptionHandler = $exceptionh;
}

function listen(){
//	echo error_get_last();
	if(isset($_REQUEST[swagger])){
		header('Content-Type: application/json');
		echo $this->getSwagger();
	} else
	if(isset($_REQUEST["describe"]) && isset($_REQUEST["method"])){
		$this->describeMethod();
	} else if(isset($_REQUEST["method"])){
	   if(!isset($_REQUEST["format"])) $_REQUEST["format"] = 'XML'; //format defaults to XML
	   $data = $this->executeMethod();
		
	   $this->deliver_response($_REQUEST["format"], $data);
	} else {
		$this->listWebServices();
	}
}


 
/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/
function deliver_response($format, $api_response, $functionName = ''){

    $http_response_code = array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found'
    );
 
    // Set HTTP Response
    //header('HTTP/1.1 '.$api_response['status'].' '.$http_response_code[ $api_response['status'] ]);
 
//    echo strcasecmp($format,"json") . "-" . $format."-";
    // Process different content types
    if( strcasecmp($format ,'json')==0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8'); 
        // Format data into a JSON response
        $json_response = json_encode($api_response); 
        // Deliver formatted data
        echo $json_response;
 
    }elseif( strcasecmp($format,'xml') == 0 ){
 	
        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8'); 

	$method = $_REQUEST["method"]; 
        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = "<ns1:".$method."Response xmlns:ns1='http://www.wso2.org/php/xsd' xmlns:ns2='http://www.wikipathways.org/webservice'  >\t". array_to_xml( $api_response, "ns1") ."\n</ns1:".$method."Response>";

        echo $xml_response;
 
    }elseif(strcasecmp($format,'dump') == 0 ){	
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');
 
        // Deliver formatted data
        echo "<pre>";
		print_r($api_response);
	echo "</pre>";
    }elseif(strcasecmp($format,'jpg') == 0 || strcasecmp($format,'pdf')==0 || strcasecmp($format,'png')==0){	
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
	$finfo = new finfo(FILEINFO_MIME);
	$mime = $finfo->buffer($api_response);

        header("Content-Type: $mime");
 
        // Deliver formatted data
	echo $api_response;
    }else{
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8'); 
        // Deliver formatted data
	var_dump($api_response);
    }
    // End script process
    exit;
}

/**
 * Displays a list of webservices
 * @return void
 */ 


function listWebServices(){
		ksort($this->functionArray);
		echo "<h1>List of services available</h1>";
		foreach($this->functionArray as $name => $value){
			echo "<h2><a href='?method=$name&describe'>".$name."</a></h2>";
		}
		exit;

}

/**
 * Executes a method
 * @return array
 */ 

function executeMethod(){

$_wservices = $this->functionArray;
$aInvokeParameter = array();
$response = "";
// ****	var_dump(findPathwaysByXref(1234, "L"));	

  try{
  
  $fct = new ReflectionFunction($_REQUEST["method"]);
  $iRequiredParameters =  $fct->getNumberOfRequiredParameters();
  $aParameter = $fct->getParameters();
  

//  echo $iRequiredParameters;
//  print_r($aParameter);
//  echo "<br>";
  foreach($aParameter as $value){
	foreach($value as $index => $val){
//		echo '<br>-> '.$index . " - " . $val;
//		var_dump($_FILES);

		if(isset($_wservices[$_REQUEST['method']]['fieldtype'][$val]) && isset($_wservices[$_REQUEST['method']]['fieldtype'][$val])==='file' ) {
			$aInvokeParameter[] = $val;
		}else if(isset($_wservices[$_REQUEST['method']]['fieldtype'][$val]) && $_wservices[$_REQUEST['method']]['fieldtype'][$val]==='array' ){
			//echo "doing array for " .  $val;
			if(isset($_REQUEST[$val])){
				$parameters = getMultipleParameters($val);
				$aInvokeParameter[] = $parameters;
				//print_r($parameters);
			}
		}else{
			//if(isset($_GET[$val])) why get?
			if(isset($_REQUEST[$val])){
				$aInvokeParameter[] = $_REQUEST[$val];
			}
		}
	}
  }

  $response = @$fct->invokeArgs($aInvokeParameter);
  //echo "". $response;



  } catch(Exception $e){
	if(is_callable($this->exceptionHandler)){
		$deffunc = $this->exceptionHandler;
		$response = $deffunc($e);
	}
  }


  return $response;
}

/**
 * Describes a method   // displays it in HTML
 */ 

function describeMethod(){

  $fct = new ReflectionFunction($_REQUEST["method"]);
  $iRequiredParameters =  $fct->getNumberOfRequiredParameters();
  $iParameters = $fct->getNumberofParameters();
  $aParameter = $fct->getParameters();
  echo "<h1>".$_REQUEST['method']."</h1>";

  $iCountRequired = 0;
  
  $_wservices = $this->functionArray;

  if(isset($_wservices[$_REQUEST['method']]['method']))
	$method = $_wservices[$_REQUEST['method']]['method'];
  else
	$method = "GET";
  
  echo "<form action='index.php' method='".$method."' enctype='multipart/form-data'>";
//  echo "<form action='index.php' method='".$method."'>";


  foreach($aParameter as $value){

	foreach($value as $index => $val){

	if(isset($_wservices[$_REQUEST['method']]['fieldDescription'][$val])) $description = $_wservices[$_REQUEST['method']]['fieldDescription'][$val];
		else $description = '';
		$type = isset($_wservices[$_REQUEST['method']]['fieldtype'][$val]) ? $_wservices[$_REQUEST['method']]['fieldtype'][$val] : 'text';

		if($iCountRequired < $iRequiredParameters){
			if($type == 'textarea')
				echo "<b>".$val. "</b> <textarea name='$val' ></textarea> $description<br/>";
			else
				echo "<b>".$val. "</b> <input type='$type' name='$val' /> $description<br/>";
			
		}else{
			if($type == 'textarea')
				echo $val. " <textarea name='$val' ></textarea> $description<br/>";
			else
				echo $val. " <input type='$type' name='$val' /> $description<br/>";
		}
		$iCountRequired++;
	}
  }

  echo '<p><b>Bold:</b> required parameters</p>';

  echo "<input type='hidden' name='method' value='".$_REQUEST['method']."'>";
  echo "<input type='radio' name='format' value='json' checked='checked'> JSON";
  echo "<input type='radio' name='format' value='xml'> XML";
  echo "<input type='radio' name='format' value='html'> HTML";
  echo "<input type='radio' name='format' value='jpg'> JPG (not all functions support it)";
  echo "<input type='radio' name='format' value='pdf'> PDF (not all functions support it)";
  echo "<input type='radio' name='format' value='png'> png (not all functions support it) <br/><br/>";

  echo "<input type='submit' /></form>";


//  print_r($aInvokeParameter);
}


/*if(!isset($_GET['describe']))
	deliver_response($_GET['format'], $response, $_GET['method']);
 */


}




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

	if($debug) print_r($array);

	if(!is_array($array)){
	 	$array = Array('Result'=>$array);
	}


        if($level < 2) $namespace = "ns1"; else $namespace = "ns2";        

	foreach($array as $key => $value){
		if(is_array($value)){
			if($debug) echo "a. processing ".$key ." $level\n";			
			$xml .= array_to_xml($value, $namespace, $key, $level + 0); /* didnt incremente level */
//			$xml .= array_to_xml($value, $namespace, $key, $level + 1);
		} else if(is_object($value)){
			if(!is_numeric($deftag) && strlen($deftag) > 0)
				$stag = $namespace != ''? $namespace . ":" . $deftag : $deftag;
			else	
				$stag = $namespace != ''? $namespace . ":" . $key : $key;

			if($debug) echo "0. processing ".$key . " -  " . $stag . " - ".$deftag." $level\n";			
			//echo "xxxxy - $key";
			//print_r((array)$value);

			//$xml .= "\n<$stag>" . array_to_xml(get_object_vars($value), $namespace, $key, $level + 1) . "\n</$stag>";

			if(is_object_array($value))
				$xml .= array_to_xml( ((array)$value) , $namespace, $key, $level+1); //should we increase level?
			else{
				//echo " - stringinfying " . $stag . " ";
				$xml .= "\n".str_repeat("\t",$level)."<$stag>" . array_to_xml( ((array)$value) , $namespace, $key, $level +1) . "\n".str_repeat("\t",$level)."</$stag>";
			}
			
		} else {	
			//$value = ''; //ignore value

			//$tvalue = str_replace("\n","",$value);
			//$tvalue = str_replace("\t","",$tvalue);
			//$tvalue = str_replace("\r","",$tvalue);

			/*if(!ctype_print($value) && !is_boolean($value))
			//if($_REQUEST["method"]==="getColoredPathway" && strlen($value)>200)
				$value = base64_encode($value);
				//$value = "<![CDATA[". $value . "]]>";			
			else */
				$value = htmlentities($value);

			if(strlen($deftag)>0 && is_integer($key)){
				if($debug) echo "1. processing ".$key . " " . $stag . " $level\n";
				$stag = $namespace != ''? $namespace . ":" . $deftag : $deftag; 
			        $xml .= "\n".str_repeat("\t",$level)."<$stag>$value</$stag>"; 			
			}else{
				if($debug) echo "2. processing ".$key . " " . $stag . " $level\n";
				$stag = $namespace != ''? $namespace . ":" . $key : $key; 
			        $xml .= "\n".str_repeat("\t",$level)."<$stag>$value</$stag>"; 
			}
		}
	}

	return $xml;
}



function getMultipleParameters($kval)
    {
	$ret = array();
	
        $query = $_SERVER['QUERY_STRING'];
        $vars = array();
        $second = array();
        foreach (explode('&', $query) as $pair) {
            list($key, $value) = explode('=', $pair);
            if('' == trim($value)){
                continue;
            }

	    if($key===$kval){
		$ret[] = $value;
	    }
	}

    return $ret;
    }



function swaggerTypeConverter($t){

	$type["int"] = "integer";
	$type["integer"] = "integer";
	$type["long"] = "integer";
	$type["float"] = "number";
	$type["double"] = "number";
	$type["string"] = "string";
	$type["byte"] = "string";
	$type["boolean"] = "boolean";
	$type["bool"] = "boolean";
	$type["date"] = "string";
	$type["dateTime"] = "string";
	$type["password"] = "string";

	if(isset( $type[$t] ))
		return $type[$t];
	else
		return $t;

}
