<?php

include('../../wpi.php');

class ontologycache
{
public static function updateCache($function,$params,$response)
{
    $dbw =& wfGetDB( DB_MASTER );
    $dbw->immediateBegin();
    $dbw->delete( 'ontologycache',array(
                                    'function' => $function,
                                    'params'    => $params),$fname = 'Database::delete');
    $dbw->insert( 'ontologycache', array(
                                    'function' => $function,
                                    'params'    => $params,
                                    'timestamp'=> time(),
                                    'response'   => $response),
                                    $fname,
                                    'IGNORE' );
    $dbw->immediateCommit();
}
public static function fetchCache($function,$params)
{

    global $wgOntologiesExpiryTime;
    
    $time = time() - $wgOntologiesExpiryTime;
    $dbr =& wfGetDB(DB_SLAVE);
    $query = "SELECT * FROM `ontologycache` where function = '$function' AND params = '$params' ORDER BY timestamp DESC ";
    $res = $dbr->query($query);
    //$res = $dbr->select( 'ontology', array('term','term_id','ontology'), array( 'pw_id' => $title ), $fname = 'Database::select', $options = array('Group by' => 'ontology' ));
    
    if($row = $dbr->fetchObject($res))
    {
       if($row->timestamp > $time)
           return($row->response);
       else
       {
           if($xml = @simplexml_load_file($params))
           {
               $xml = $xml->asXML();
               ontologycache::updateCache($function,$params,$xml);
               return($xml);
           }
           else
           {
                $dbw =& wfGetDB( DB_MASTER );
                $dbw->immediateBegin();
                $dbw->update('ontologycache',array('timestamp'=>time()) ,array("function"=>$function,"params"=>$params),$fname = 'Database::update', $options = array() );
                $dbw->immediateCommit();
                return($row->response);
           }
       }
    }
    else
    {
           if($xml = @simplexml_load_file($params))
           {
                $xml = $xml->asXML();
                ontologycache::updateCache($function,$params,$xml);
                return($xml);
           }
    }
   $dbr->freeResult( $res );
}
}
?>