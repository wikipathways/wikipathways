<?php
$requestPayloadString = <<<XML
<fetchTerms>
<pathwayId>WP554</pathwayId>
</fetchTerms>
XML;
try {
    $client = new WSClient(array( "to" => "http://bkup.wikipathways.org/ontologyservice/service.php"));
    $responseMessage = $client->request( $requestPayloadString );
    printf("Response = %s <br>", htmlspecialchars($responseMessage->str));

} catch (Exception $e) {

    if ($e instanceof WSFault) {
        printf("Soap Fault: %s\n", $e->Reason);
    } else {
        printf("Message = %s\n",$e->getMessage());
    }
}
?>