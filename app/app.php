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


/**
* ContactSpace group
**/
$app->group('/contactspace', function () use ($app) {
	
	/*
	* Called from HubSpot to synchronize contact on ContactSpace
	* Receives JSON object in request body
	*/
	$app->post('/synchronize', function() use ($app) {
	$entityBody = $app->request->getBody();	
	
	
	$hubspotData = json_decode($entityBody);

	$fields = array();
	$fields['vid'] = $hubspotData->vid;

	//extracting contact information from HubSpot
	foreach($hubspotData->properties as $key => $property) {
		if($key == "lastname" || $key == "phone" || $key == "firstname" || $key == "hubspot_owner_id" ||
			$key == "loan_purpose" || $key == "approved_loan_amount" || $key == "yes_i_accept" || 
			$key == "employment_type_" || $key == "credit_status" || $key == "postal_code" || 
			$key == "home_sts" || $key == "employment_length" || $key == "current_residency_length" || 
			$key == "marital_status"  ||  $key == "number_of_children" || $key == "mobilephone") 
			$fields[$key] = $property->value;
	}

	
	//preparing XML to be posted on ContactSpace
	$rID = time();


	require_once('app/lib/contactspace.php');
	$contactSpace = new Custom\Libs\ContactSpace();
	$contactSpaceXML = "<record><Record_ID>".$rID."</Record_ID>";

	if(array_key_exists('phone', $fields)) {
		$fields['phone'] = ltrim($fields['phone'], '0');
		//@TODO right now its setup with Australia country code make it dynamic later as needed
		$fields['phone'] = "61".$fields['phone'];
		$contactSpaceXML .= "<Home_Phone>".$fields['phone']."</Home_Phone>";
	}

	if(array_key_exists('mobilephone', $fields)) {
		$fields['mobilephone'] = ltrim($fields['mobilephone'], '0');
		//@TODO right now its setup with Australia country code make it dynamic later as needed
		$fields['mobilephone'] = "61".$fields['mobilephone'];
		$contactSpaceXML .= "<Mobile_Phone>".$fields['mobilephone']."</Mobile_Phone>";
	}
		
	if(array_key_exists('firstname', $fields))
		$contactSpaceXML .= "<First_Name>".$fields['firstname']."</First_Name>";
	
	if(array_key_exists('lastname', $fields))
		$contactSpaceXML .= "<Last_Name>".$fields['lastname']."</Last_Name>";


	if(array_key_exists('loan_purpose', $fields))
		$contactSpaceXML .= "<Loan_Purpose>".$fields['loan_purpose']."</Loan_Purpose>";


	if(array_key_exists('approved_loan_amount', $fields))
		$contactSpaceXML .= "<Loan_Amount>".$fields['approved_loan_amount']."</Loan_Amount>";

	if(array_key_exists('yes_i_accept', $fields))
		$contactSpaceXML .= "<Privacy_Policy_Consent_Accepted>".$fields['yes_i_accept']."</Privacy_Policy_Consent_Accepted>";

	if(array_key_exists('employment_type_', $fields))
		$contactSpaceXML .= "<Employment_Type>".$fields['employment_type_']."</Employment_Type>";

	if(array_key_exists('credit_status', $fields))
		$contactSpaceXML .= "<Credit_Status>".$fields['credit_status']."</Credit_Status>";

	if(array_key_exists('postal_code', $fields))
		$contactSpaceXML .= "<Postal_Code>".$fields['postal_code']."</Postal_Code>";

	if(array_key_exists('home_sts', $fields))
		$contactSpaceXML .= "<Resident_Status>".$fields['home_sts']."</Resident_Status>";

	if(array_key_exists('employment_length', $fields))
		$contactSpaceXML .= "<Employment_Length>".$fields['employment_length']."</Employment_Length>";

	if(array_key_exists('current_residency_length', $fields))
		$contactSpaceXML .= "<Current_Residency_Length>".$fields['current_residency_length']."</Current_Residency_Length>";

	if(array_key_exists('marital_status', $fields))
		$contactSpaceXML .= "<Marital_Status>".$fields['marital_status']."</Marital_Status>";

	if(array_key_exists('number_of_children', $fields))
		$contactSpaceXML .= "<Number_of_Children>".$fields['number_of_children']."</Number_of_Children>";


	$contactSpaceXML .= "</record>";

	//post to ContactSpace
	$contactSpaceSyncResponseArr = $contactSpace->insertRecord($contactSpaceXML);

	//log ContactSpace request and response
	if($app->log->getEnabled()) {
		$app->log->debug('['.date('H:i:s', time()).'] ContactSpace Sync Request: ' . $contactSpaceXML);
		$app->log->debug('['.date('H:i:s', time()).'] ContactSpace Sync Response Body: ' . $contactSpaceSyncResponseArr[1]);
		$app->log->debug('['.date('H:i:s', time()).'] ContactSpace Sync Response: ' . $contactSpaceSyncResponseArr[0]);
	} 

	echo $contactSpaceSyncResponseArr[0];
	});
});



/**
* Deal group
**/
$app->group('/deal', function () use ($app) {
	/*
	* Called from HubSpot to synchronize deal on HubSpot Sales portal
	* Receives JSON object in request body
	*/
	$app->post('/synchronize', function() use ($app) {

	$call_response = 200;

    
    $entityBody = $app->request->getBody();	
	
	
	$hubspotData = json_decode($entityBody);

	$fields = array();
	$fields['vid'] = $hubspotData->vid;

	//extracting contact information from HubSpot
	foreach($hubspotData->properties as $key => $property) {
		if($key == "lastname" || $key == "phone" || $key == "firstname" || $key == "hubspot_owner_id" ||
			$key == "loan_purpose" || $key == "approved_loan_amount" || $key == "yes_i_accept" || 
			$key == "employment_type_" || $key == "credit_status" || $key == "postal_code" || 
			$key == "home_sts" || $key == "employment_length" || $key == "current_residency_length" || 
			$key == "marital_status"  ||  $key == "number_of_children")
			$fields[$key] = $property->value;
	}
	

	/**** Synchronizing deal to HubSpot ****/
	if(!array_key_exists('hubspot_owner_id', $fields)) {
		if($app->log->getEnabled())
			$app->log->debug('['.date('H:i:s', time()).'] Deal Sync Warning: HubSpot owner missing, setting to 5219627');

		$fields['hubspot_owner_id'] = '5219627';
	}

		$dealJSON = '{
            "associations": {
                "associatedCompanyIds": [
                    0
                ],
                "associatedVids": [
                    '.$fields['vid'].'
                ]
            },
            "portalId": 62515,
            "properties": [
                
                {
                    "value": "appointmentscheduled",
                    "name": "dealstage"
                },
                {
                    "value": "'.$fields['hubspot_owner_id'].'",
                    "name": "hubspot_owner_id"
                },
                {
                    "value": "newbusiness",
                    "name": "dealtype"
                },
                {
                    "value": "new_enquiry",
                    "name": "deal_status"
                }';

                if(array_key_exists('firstname', $fields))
                	$dealJSON .= ',{
                    "value": "'.$fields['firstname'].'\'s Deal",
                    "name": "dealname"
                	}';


                if(array_key_exists('approved_loan_amount', $fields))
                	$dealJSON .= ',{
                    "value": "'.$fields['approved_loan_amount'].'",
                    "name": "loan_amount"
                	}';

                if(array_key_exists('loan_purpose', $fields))	
                	$dealJSON .= ',{
                    "value": "'.$fields['loan_purpose'].'",
                    "name": "loan_purpose"
                	}';
            $dealJSON .= ']}';
    	

        require_once('app/lib/hubspotext.php');
		$hubspotExt = new Custom\Libs\HubSpotExt();
		$dealSyncResponseArr = $hubspotExt->insertDeal($dealJSON);
		$dealResponse = json_decode($dealSyncResponseArr[1]);  

		
        if($app->log->getEnabled()) {
			$app->log->debug('['.date('H:i:s', time()).'] Deal Sync Request: '. $dealJSON);
			$app->log->debug('['.date('H:i:s', time()).'] Deal Sync Response Body: ' . $dealSyncResponseArr[1]);
			$app->log->debug('['.date('H:i:s', time()).'] Deal Sync Response: ' . $call_response );
			
		} 
		echo $dealSyncResponseArr[0];

	


	
	});
});


$app->group('/emailleads', function() use ($app) {
	$app->post('/synchronize', function() use ($app) {

		require_once('app/lib/emailleads.php');
		$_instanceEmailLeads = new Custom\Libs\EmailLeads();

		$type = $app->request->post("type");

		$_instanceEmailLeads->username = $app->request->post("username");

		$_instanceEmailLeads->password = $app->request->post("password");


		
		switch($type) {
			case 'carsales':
				break;
			case 'loanplace':
				break;
			case 'test':
				$_instanceEmailLeads->synchronizeTestEmailLeads($app);
				break;
		}
	});
});
