<?php

class WSFault extends Exception{

	function __construct($code, $reason, $role = "", $detail = ""){
		$lCode = 500;
		parent::__construct($reason . " : " . $code  . " :  " . $detail , $lCode);

	}

}
