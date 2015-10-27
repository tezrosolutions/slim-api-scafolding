<?php

$app->curl = function ($c) use ($app) {
    return new \Curl();
};
$app->authentication = function ($c) use ($app) {
    return new \There4\Authentication\Cookie();
};

/* * ** Hooks used for logging ******** */
$app->hook('slim.before.router', function () use ($app) {
    $request = $app->request;
    $response = $app->response;

    $app->log->debug('[' . date('H:i:s', time()) . '] Request path: ' . $request->getPathInfo());
    $app->log->debug('[' . date('H:i:s', time()) . '] Request body: ' . $request->getBody());
});

$app->hook('slim.after.router', function () use ($app) {
    $request = $app->request;
    $response = $app->response;

    $app->log->debug('[' . date('H:i:s', time()) . '] Response status: ' . $response->getStatus());
});



/* * ** Hello World **** */
$app->get('/hello/:name', function ($name) use ($app) {
    echo "Hello, $name";
});




/**
 * ContactSpace group
 * */
$app->group('/contactspace', function () use ($app) {

    $app->post("/updateHubSpot", function() use ($app) {
        $callID = $app->request->post('CallID');


        require_once('app/lib/contactspace.php');
        $contactSpace = new Custom\Libs\ContactSpace();


        //get call information from CS
        $callInfoResponse = $contactSpace->getSingleRecord($callID);

        if (count($callInfoResponse) == 2) {
            if ($callInfoResponse[0] == 200) {
                $callInfo = simplexml_load_string($callInfoResponse[1]);
                if (isset($callInfo->records->record->Record_ID)) {
                    $vid = $callInfo->records->record->Record_ID;

                    $appConfig = $app->config('custom');
                    $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);

                    $fields = array();
                    if (isset($callInfo->records->record->Broker_email)) {

                        if (count($callInfo->records->record->Broker_email) > 0) {

                            $fields['broker_email'] = (string) $callInfo->records->record->Broker_email[0];
                            $fields['hs_lead_status'] = "QUALIFIED";
                        }
                    }


                    if (isset($callInfo->records->record->Lead_status)) {

                        if (count($callInfo->records->record->Lead_status) > 0) {

                            $fields['hs_lead_status'] = (string) strtoupper($callInfo->records->record->Lead_status[0]);
                        }
                    }
                    //@TODO for testing
                    /* $vid = 76669;
                      $fields['hs_lead_status'] = "QUALIFIED";
                      $fields['broker_email'] = "umair@tezrosolutions.com"; */



                    $hsUpdateResponse = $hubspot->contacts()->update_contact($vid, $fields);
                    if (isset($hsUpdateResponse->status)) {
                        if ($hsUpdateResponse->status == "error" && $hsUpdateResponse->message == "resource not found") {
                            if (isset($callInfo->records->record->Phone)) {
                                $phone = "0" . ltrim($callInfo->records->record->Phone, "61");
                            } else if (isset($callInfo->records->record->Work_Phone)) {
                                $phone = "0" . ltrim($callInfo->records->record->Work_Phone, "61");
                            } else if (isset($callInfo->records->record->Mobile_Phone)) {
                                $phone = "0" . ltrim($callInfo->records->record->Mobile_Phone, "61");
                            } else if (isset($callInfo->records->record->Home_Phone)) {
                                $phone = "0" . ltrim($callInfo->records->record->Home_Phone, "61");
                            }

                            $hsSearchResponse = $hubspot->contacts()->search_contacts(array("q" => $phone));
                            if ($hsSearchResponse->total > 0) {
                                $vid = $hsSearchResponse->contacts[0]->vid;
                                $hsUpdateResponse = $hubspot->contacts()->update_contact($vid, $fields);
                            } else {
                                $phone = ltrim($phone, "0");
                                $hsSearchResponse = $hubspot->contacts()->search_contacts(array("q" => $phone));
                                if ($hsSearchResponse->total > 0) {
                                    $vid = $hsSearchResponse->contacts[0]->vid;
                                    $hsUpdateResponse = $hubspot->contacts()->update_contact($vid, $fields);
                                }
                            }
                        }
                    }

                    if ($app->log->getEnabled()) {
                        $app->log->debug('[' . date('H:i:s', time()) . '] HS Update Request Body: ID: ' . $vid . ' BODY: ' . json_encode($fields));
                        $app->log->debug('[' . date('H:i:s', time()) . '] HS Update Response Body: ' . json_encode($hsUpdateResponse));
                    }

                    if (isset($hsUpdateResponse->status))
                        echo $hsUpdateResponse->status;
                    else
                        echo "success";
                } else {
                    echo "error";
                }
            } else {
                echo "error";
            }
        } else {
            echo "error";
        }
    });

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
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "lastname" || $key == "phone" || $key == "firstname" || $key == "hubspot_owner_id" ||
                    $key == "loan_purpose" || $key == "approved_loan_amount" || $key == "yes_i_accept" ||
                    $key == "employment_type_" || $key == "credit_status" || $key == "postal_code" ||
                    $key == "home_sts" || $key == "employment_length" || $key == "current_residency_length" ||
                    $key == "marital_status" || $key == "number_of_children" || $key == "mobilephone" ||
                    $key == "broker_email" || $key == "business_no")
                $fields[$key] = $property->value;
        }


        //preparing XML to be posted on ContactSpace


        require_once('app/lib/contactspace.php');
        $contactSpace = new Custom\Libs\ContactSpace();
        $contactSpaceXML = "<record><Record_ID>" . $fields['vid'] . "</Record_ID>";

        if (array_key_exists('phone', $fields)) {
            $fields['phone'] = ltrim($fields['phone'], '0');
            //@TODO right now its setup with Australia country code make it dynamic later as needed
            $fields['phone'] = "61" . $fields['phone'];
            //$contactSpaceXML .= "<Phone>" . $fields['phone'] . "</Phone>";
            $contactSpaceXML .= "<Mobile_Phone>" . $fields['phone'] . "</Mobile_Phone>";
        }

        if (array_key_exists('business_no', $fields)) {
            $fields['business_no'] = ltrim($fields['business_no'], '0');
            //@TODO right now its setup with Australia country code make it dynamic later as needed
            $fields['business_no'] = "61" . $fields['business_no'];
            //$contactSpaceXML .= "<Work_Phone>" . $fields['business_no'] . "</Work_Phone>";
            $contactSpaceXML .= "<Mobile_Phone>" . $fields['business_no'] . "</Mobile_Phone>";
        }

        if (array_key_exists('mobilephone', $fields)) {
            $fields['mobilephone'] = ltrim($fields['mobilephone'], '0');
            //@TODO right now its setup with Australia country code make it dynamic later as needed
            $fields['mobilephone'] = "61" . $fields['mobilephone'];
            $contactSpaceXML .= "<Mobile_Phone>" . $fields['mobilephone'] . "</Mobile_Phone>";
        }

        if (array_key_exists('firstname', $fields))
            $contactSpaceXML .= "<First_Name>" . $fields['firstname'] . "</First_Name>";

        if (array_key_exists('lastname', $fields))
            $contactSpaceXML .= "<Last_Name>" . $fields['lastname'] . "</Last_Name>";


        if (array_key_exists('loan_purpose', $fields))
            $contactSpaceXML .= "<Loan_Purpose>" . $fields['loan_purpose'] . "</Loan_Purpose>";


        if (array_key_exists('approved_loan_amount', $fields))
            $contactSpaceXML .= "<Loan_Amount>" . $fields['approved_loan_amount'] . "</Loan_Amount>";

        if (array_key_exists('yes_i_accept', $fields))
            $contactSpaceXML .= "<Privacy_Policy_Consent_Accepted>" . $fields['yes_i_accept'] . "</Privacy_Policy_Consent_Accepted>";

        if (array_key_exists('employment_type_', $fields))
            $contactSpaceXML .= "<Employment_Type>" . $fields['employment_type_'] . "</Employment_Type>";

        if (array_key_exists('credit_status', $fields))
            $contactSpaceXML .= "<Credit_Status>" . $fields['credit_status'] . "</Credit_Status>";

        if (array_key_exists('postal_code', $fields))
            $contactSpaceXML .= "<Postal_Code>" . $fields['postal_code'] . "</Postal_Code>";

        if (array_key_exists('home_sts', $fields))
            $contactSpaceXML .= "<Resident_Status>" . $fields['home_sts'] . "</Resident_Status>";

        if (array_key_exists('employment_length', $fields))
            $contactSpaceXML .= "<Employment_Length>" . $fields['employment_length'] . "</Employment_Length>";

        if (array_key_exists('current_residency_length', $fields))
            $contactSpaceXML .= "<Current_Residency_Length>" . $fields['current_residency_length'] . "</Current_Residency_Length>";

        if (array_key_exists('marital_status', $fields))
            $contactSpaceXML .= "<Marital_Status>" . $fields['marital_status'] . "</Marital_Status>";

        if (array_key_exists('number_of_children', $fields))
            $contactSpaceXML .= "<Number_of_Children>" . $fields['number_of_children'] . "</Number_of_Children>";

        if (array_key_exists('broker_email', $fields))
            $contactSpaceXML .= "<Broker_email>" . $fields['broker_email'] . "</Broker_email>";


        $contactSpaceXML .= "</record>";

        //post to ContactSpace
        $contactSpaceSyncResponseArr = $contactSpace->insertRecord($contactSpaceXML);

        //log ContactSpace request and response
        if ($app->log->getEnabled()) {
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Request: ' . $contactSpaceXML);
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Response Body: ' . $contactSpaceSyncResponseArr[1]);
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Response: ' . $contactSpaceSyncResponseArr[0]);
        }

        echo $contactSpaceSyncResponseArr[0];
    });
});




/**
 * Deal group
 * */
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
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "lastname" || $key == "phone" || $key == "firstname" || $key == "hubspot_owner_id" ||
                    $key == "loan_purpose" || $key == "approved_loan_amount" || $key == "yes_i_accept" ||
                    $key == "employment_type_" || $key == "credit_status" || $key == "postal_code" ||
                    $key == "home_sts" || $key == "employment_length" || $key == "current_residency_length" ||
                    $key == "marital_status" || $key == "number_of_children")
                $fields[$key] = $property->value;
        }


        /*         * ** Synchronizing deal to HubSpot *** */
        if (!array_key_exists('hubspot_owner_id', $fields)) {
            if ($app->log->getEnabled())
                $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Warning: HubSpot owner missing, setting to 5219627');

            $fields['hubspot_owner_id'] = '5219627';
        }

        $dealJSON = '{
            "associations": {
                "associatedCompanyIds": [
                    0
                ],
                "associatedVids": [
                    ' . $fields['vid'] . '
                ]
            },
            "portalId": 62515,
            "properties": [
                
                {
                    "value": "appointmentscheduled",
                    "name": "dealstage"
                },
                {
                    "value": "' . $fields['hubspot_owner_id'] . '",
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

        if (array_key_exists('firstname', $fields))
            $dealJSON .= ',{
                    "value": "' . $fields['firstname'] . '\'s Deal",
                    "name": "dealname"
                	}';


        if (array_key_exists('approved_loan_amount', $fields))
            $dealJSON .= ',{
                    "value": "' . $fields['approved_loan_amount'] . '",
                    "name": "loan_amount"
                	}';

        if (array_key_exists('loan_purpose', $fields))
            $dealJSON .= ',{
                    "value": "' . $fields['loan_purpose'] . '",
                    "name": "loan_purpose"
                	}';
        $dealJSON .= ']}';


        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();
        $dealSyncResponseArr = $hubspotExt->insertDeal($dealJSON);
        $dealResponse = json_decode($dealSyncResponseArr[1]);


        if ($app->log->getEnabled()) {
            $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Request: ' . $dealJSON);
            $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Response Body: ' . $dealSyncResponseArr[1]);
            $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Response: ' . $call_response);
        }
        echo $dealSyncResponseArr[0];
    });
});




/**
 * Email Leads group
 * */
$app->group('/emailleads', function() use ($app) {
    /*
     * Called from CRON to synchronize lead information in email messages to HubSpot 
     */
    $app->post('/synchronize', function() use ($app) {

        require_once('app/lib/emailleads.php');
        $_instanceEmailLeads = new EmailLeads();

        $type = $app->request->post("type");

        $_instanceEmailLeads->username = $app->request->post("username");

        $_instanceEmailLeads->password = $app->request->post("password");



        switch ($type) {
            case 'carsales':
                $_instanceEmailLeads->synchronizeTestEmailLeads($app);
                break;
            case 'loanplace':
                $_instanceEmailLeads->synchronizeLoanPlaceEmailLeads($app);
                break;
            case 'test':
                $_instanceEmailLeads->synchronizeTestEmailLeads($app);
                break;
        }
    });
});




/**
 * Genius group
 * */
$app->group('/genius', function() use ($app) {
    /**
     * Prints the deal properties
     */
    $app->get('/deal/:id', function ($id) use ($app) {
        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();
        //print_r(json_encode(json_decode($hubspotExt->getDeal($id)[1])->properties));
        echo ($hubspotExt->getDeal($id)[1]);
    });

    /**
     * Get called from Genius when application status is changed
     */
    $app->post('/updateHubSpot', function() use ($app) {
        $vid = $app->request->post("vid");

        $gid = $app->request->post("gid");

        $customConfig = $app->config('custom');
        if (array_key_exists($app->request->post("status"), $customConfig['hubspot']['dealStatuses'])) {
            $status = $customConfig['hubspot']['dealStatuses'][$app->request->post("status")];


            require_once('app/lib/hubspotext.php');
            $hubspotExt = new Custom\Libs\HubSpotExt();
            $fields = '
            {
            "properties": [
                {
                    "name": "deal_status",
                    "value": "' . $status . '"
                }
            ]
        }';

            $dealGetResponseArr = $hubspotExt->updateDeal($vid, $fields);

            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Request Body: ' . $fields);
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Response Body: ' . $dealGetResponseArr[1]);
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Response Status: ' . $dealGetResponseArr[0]);
            }


            echo $dealGetResponseArr[0];
        } else {
            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Error: Invalid status code');
            }

            echo 400;
        }
    });

    /*
     * Called from HubSpot to synchronize contact as a Genius loan application
     * Receives JSON object in request body
     */
    $app->post('/synchronize', function() use ($app) {



        $entityBody = $app->request->getBody();


        $hubspotData = json_decode($entityBody);

        $fields = array();
        $fields['vid'] = $hubspotData->vid;

        //extracting contact information from HubSpot
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "lastname" || $key == "phone" || $key == "firstname" || $key == "hubspot_owner_id" ||
                    $key == "loan_purpose" || $key == "approved_loan_amount" || $key == "yes_i_accept" ||
                    $key == "employment_type_" || $key == "credit_status" || $key == "postal_code" ||
                    $key == "home_sts" || $key == "employment_length" || $key == "current_residency_length" ||
                    $key == "marital_status" || $key == "number_of_children")
                $fields[$key] = $property->value;
        }


        /*         * ** Synchronizing deal to Genius *** */
        if (!array_key_exists('hubspot_owner_id', $fields)) {
            if ($app->log->getEnabled())
                $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Warning: HubSpot owner missing, setting to 5219627');

            $fields['hubspot_owner_id'] = '5219627';
        }

        $dealJSON = '{
            "associations": {
                "associatedCompanyIds": [
                    0
                ],
                "associatedVids": [
                    ' . $fields['vid'] . '
                ]
            },
            "portalId": 62515,
            "properties": [
                
                {
                    "value": "appointmentscheduled",
                    "name": "dealstage"
                },
                {
                    "value": "' . $fields['hubspot_owner_id'] . '",
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

        if (array_key_exists('firstname', $fields))
            $dealJSON .= ',{
                    "value": "' . $fields['firstname'] . '\'s Deal",
                    "name": "dealname"
                	}';


        if (array_key_exists('approved_loan_amount', $fields))
            $dealJSON .= ',{
                    "value": "' . $fields['approved_loan_amount'] . '",
                    "name": "loan_amount"
                	}';

        if (array_key_exists('loan_purpose', $fields))
            $dealJSON .= ',{
                    "value": "' . $fields['loan_purpose'] . '",
                    "name": "loan_purpose"
                	}';
        $dealJSON .= ']}';


        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();
        $dealSyncResponseArr = $hubspotExt->insertDeal($dealJSON);
        $deal = json_decode($dealSyncResponseArr[1]);


        if ($app->log->getEnabled()) {
            $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Request: ' . $dealJSON);
            $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Response Body: ' . $dealSyncResponseArr[1]);
            $app->log->debug('[' . date('H:i:s', time()) . '] Deal Sync Response: ' . $dealSyncResponseArr[0]);
        }

        /**
         * Synchronizing to Genius as new loan application
         */
        require_once('app/lib/genius.php');
        $instanceGenius = new Custom\Libs\Genius($app);



        $fields = array();
        $fields['ID'] = $deal->dealId;

        //extracting contact information from HubSpot
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "lastname" || $key == "phone" || $key == "firstname" || $key == "loan_purpose" ||
                    $key == "approved_loan_amount" || $key == "zip" || $key == "accept_privacy" ||
                    $key == "accept_creditguide" || $key == "abn" || $key == "dob" ||
                    $key == "employment_type_" || $key == "broker_email" || $key == "home_sts" ||
                    $key == "marital_status" || $key == "number_of_children" || $key == "employment_length" ||
                    $key == "email" || $key == "company" || $key == "broker_email" || $key == "gender" ||
                    $key == "address" || $key == "city" || $key == "state" || $key == "current_residency_length" ||
                    $key == "utm" || $key == "totalincome" || $key == "feedback_comments" || $key == "hs_lead_status" ||
                    $key == "mobilephone" || $key == "private_phone_number" || $key == "suburb")
                $fields[$key] = $property->value;
        }


        //@TODO Remove JUST USE FOR TESTING
        //$fields['broker_email'] = "umair@tezrosolutions.com";

        $fields['leads_type'] = "QuickQuote";
        $fields['accessCode'] = "money3";
        $fields['accessPass'] = "flying123";

        //@TODO inquire about these
        $fields['leads_businesstype'] = $fields['leads_assignee'] = $fields['unitno'] = $fields['streetno'] = $fields['streettype'] = $fields['residmonth'] = $fields['leads_lic'] = $fields['emplengthmonth'] = $fields['mortgagePayments'] = $fields['rentPayments'] = $fields['utm_medium'] = $fields['utm_cname'] = $fields['utm_cterm'] = $fields['utm_ccontent'] = $fields['leads_income2'] = "";

        if (!empty($fields['abn'])) {
            $fields['introducer'] = '121';
            $fields['salesperson'] = '2053'; //amgelo

            $countAbn = preg_match_all("/[0-9]/", $fields['abn'], $nothing);

            if ($countAbn != '11') {
                $fields['abn'] = '11111111111';
            }
        } else {
            $fields['introducer'] = '74';
            $fields['salesperson'] = '1127'; //rod
            $fields['abn'] = '11111111111';
        }


        $fields['coplArea'] = "<PhHomeAreaCode></PhHomeAreaCode>";

        if (isset($fields['private_phone_number']))
            $fields['coplPh'] = "<PhHome>" . $fields['private_phone_number'] . "</PhHome>";
        else
            $fields['coplPh'] = "<PhHome></PhHome>";

        if (isset($fields['mobilephone']))
            $fields['coplMob'] = "<Mobile>" . $fields['mobilephone'] . "</Mobile>";
        else
            $fields['coplMob'] = "<Mobile></Mobile>";


        if (empty($fields['dob'])) {
            $fields['fullBday'] = "";
        } else {
            $dob_parts = explode("/", $fields['dob']);
            if (count($dob_parts) == 3) {
                $birthyear = $dob_parts[2];
                $birthmonth = $dob_parts[1];
                $birthday = $dob_parts[0];
                $fields['fullBday'] = $birthyear . "-" . $birthmonth . "-" . $birthday;
            } else {
                $timestamp = $fields['dob'] / 1000;
                $dob_parts = explode("/", gmdate("d/m/Y", $timestamp));
                if (count($dob_parts) == 3) {
                    $birthyear = $dob_parts[2];
                    $birthmonth = $dob_parts[1];
                    $birthday = $dob_parts[0];
                    $fields['fullBday'] = $birthyear . "-" . $birthmonth . "-" . $birthday;
                }
            }
        }


        if (!empty($fields['approved_loan_amount'])) {
            $fields['approved_loan_amount'] = str_replace('$', '', $fields['approved_loan_amount']);
            $fields['approved_loan_amount'] = str_replace(',', '', $fields['approved_loan_amount']);
        } else {
            $fields['approved_loan_amount'] = "";
        }

        if (!empty($fields['marital_status']))
            $fields['marital'] = $instanceGenius->getCoplCodes("marital_statuses", $fields['marital_status'], 'maritalstatus');
        else
            $fields['marital'] = "";

        if (!empty($fields['loan_purpose']))
            $fields['leads_finance_type'] = $instanceGenius->getCoplCodes('loan_types', $fields['loan_purpose'], 'loantype'); //required
        else
            $fields['leads_finance_type'] = "personal";


        if (!empty($fields['employment_type_']))
            $fields['employment'] = $instanceGenius->getCoplCodes('employnents_types', $fields['employment_type_'], 'employementtype');
        else
            $fields['employment'] = "";

        if (!empty($fields['home_sts']))
            $fields['property'] = $instanceGenius->getCoplCodes('residential_statuses', $fields['home_sts'], 'residentialstatus');
        else
            $fields['property'] = "";




        echo $instanceGenius->post($fields)[0];
    });
});


/**
 * Finder group
 * */
$app->group('/finder', function () use ($app) {

    $app->post("/synchronize", function() use ($app) {
        
        $form_fields = array();
        $form_fields['finder_identifier'] = $app->request->post("lead_id");
        $form_fields['finder_date_posted']  = $app->request->post("date_posted");
        $form_fields['firstname'] = $app->request->post("fname");
        $form_fields['lastname'] = $app->request->post("lname");
        $form_fields['phone']  = $app->request->post("phone");
        $form_fields['email']  = $app->request->post("email");
        $form_fields['totalincome'] = $app->request->post("income");
        $form_fields['australian_citizen'] = $app->request->post("australian_citizen")?"true":"false";
        $form_fields['credit_defaults'] = $app->request->post("credit_defaults")?"true":"false";

        $appConfig = $app->config('custom');
        $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);
        
        $hubspot->contacts()->create_contact($form_fields);
        
        echo 200;
        
    });
});

