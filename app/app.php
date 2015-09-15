<?php
$app->curl = function ($c) use ($app) {
    return new \Curl();
};
$app->authentication = function ($c) use ($app) {
    return new \There4\Authentication\Cookie();
};



$app->hook('slim.after.router', function () use ($app) {
   	$request = $app->request;
    $response = $app->response;

    $app->log->debug('['.date('H:i:s', time()).'] Request path: ' . $request->getPathInfo());
    $app->log->debug('['.date('H:i:s', time()).'] Request body: ' . $request->getBody());
	$app->log->debug('['.date('H:i:s', time()).'] Response status: ' . $response->getStatus());

});


$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";

});


$app->post('/create/deal', function() {
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
	echo "deal created";
});