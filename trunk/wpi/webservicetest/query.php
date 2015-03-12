<?php



function getUserByOrcid($orcid){

	  $url = 'http://www.wikipathways.org/api.php?action=query&list=search&srwhat=text&srsearch=%22{{User+ORCID|'.$orcid.'}}%22&srnamespace=2&format=json';
  	  
	$ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch); 

	$result = json_decode($output);

	if(sizeof($result->query->search) == 0)
		return $r["error"] = "No results found";
	if(sizeof($result->query->search) > 1)
		return $r["error"] = "Ambiguous result. 2 or more results were found";
	
	return $r["success"] = $result->query->search[0]->title;

}

?>
