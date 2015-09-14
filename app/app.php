<?php
$app->curl = function ($c) use ($app) {
    return new \Curl();
};
$app->authentication = function ($c) use ($app) {
    return new \There4\Authentication\Cookie();
};


$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";

});


$app->post('/sync/contactspace', function() {
	//needed for 
    /*
    $entityBody = file_get_contents('php://input');	
	
	
	$hubspotData = json_decode($entityBody);

	$fields = array();

	foreach($hubspotData->properties as $key => $property) {
		if($key == "lastname" || $key == "phone" || $key == "firstname")
			$fields[$key] = $property->value;
	}
	*/
});