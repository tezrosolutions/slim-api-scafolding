<?php
$app->curl = function ($c) use ($app) {
    return new \Curl();
};
$app->authentication = function ($c) use ($app) {
    return new \There4\Authentication\Cookie();
};

/**** Hooks used for logging *********/
$app->hook('slim.before.router', function () use ($app) {
   	$request = $app->request;
    $response = $app->response;

    $app->log->debug('['.date('H:i:s', time()).'] Request path: ' . $request->getPathInfo());
    $app->log->debug('['.date('H:i:s', time()).'] Request body: ' . $request->getBody());
	

});

$app->hook('slim.after.router', function () use ($app) {
   	$request = $app->request;
    $response = $app->response;

	$app->log->debug('['.date('H:i:s', time()).'] Response status: ' . $response->getStatus());

});


/**** Hello World *****/
$app->get('/hello/:name', function ($name) use ($app) {
    echo "Hello, $name";

});


/*
* Called from HubSpot API to synchronize deal on ContactSpace and HubSpot itself
* Receives JSON object in request body
*/
$app->post('/deal', function() use ($app) {
    
    $entityBody = $app->request->getBody();	
	
	
	$hubspotData = json_decode($entityBody);

	$fields = array();


	//extracting contact information from HubSpot
	foreach($hubspotData->properties as $key => $property) {
		if($key == "lastname" || $key == "phone" || $key == "firstname")
			$fields[$key] = $property->value;
	}


	//preparing XML to be posted on ContactSpace
	$rID = time();

	$contactSpace = new Custom\Libs\ContactSpace();
	$contactSpaceXML = "<record><RecordID>".$rID."</RecordID>";

	if(array_key_exists('phone', $fields))
		$contactSpaceXML .= "<HomePhone>".$fields['phone']."</HomePhone>";
		
	if(array_key_exists('firstname', $fields))
		$contactSpaceXML .= "<FirstName>".$fields['firstname']."</FirstName>";
	
	if(array_key_exists('lastname', $fields))
		$contactSpaceXML .= "<Surname>".$fields['lastname']."</Surname>";

	$contactSpaceXML .= "</record>";

	//post to ContactSpace
	$contactSpaceResponse = $contactSpace->insertRecord($contactSpaceXML);

	//log ContactSpace request and response
	if($app->log->getEnabled()) {
		$app->log->debug('['.date('H:i:s', time()).'] ContactSpace Request: ' . $contactSpaceXML);
		$app->log->debug('['.date('H:i:s', time()).'] ContactSpace Response: ' . $contactSpaceResponse['http_code']);
	} 
});