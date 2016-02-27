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
 * HubSpot Group
 */
$app->group('/hubspot', function() use ($app) {
    /**
     * Returns contact Profile
     */
    $app->get('/contact/dealID/:dealID/profile', function($dealID) use ($app) {
        $customConfig = $app->config('custom');

        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();
        $dealOfInterest = $hubspotExt->getDeal($dealID)[1];
        $fields = array();

        if (isset(json_decode($dealOfInterest)->associations->associatedVids[0])) {//update lead status and settlement date
            $contactID = json_decode($dealOfInterest)->associations->associatedVids[0];
            $hubspot = new Fungku\HubSpot($customConfig['hubspot']['config']['HUBSPOT_API_KEY']);

            $hsContact = $hubspot->contacts()->get_contact_by_id($contactID);
            if (isset($hsContact->vid)) {
                echo json_encode($hsContact);
            } else
                echo '{"status": "error", "message": "No contact associated with this deal."}';
        } else {
            echo '{"status": "error", "message": "Deal not found."}';
        }
    });


    /*
     * Called from HubSpot to synchronize contact on ContactSpace
     * Receives JSON object in request body
     */
    $app->post('/create', function() use ($app) {

        $appConfig = $app->config('custom');

        $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);
        $contactFields = $app->request->post();


        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();

        //making sure broker_email is always in lower case to avoid matching problem as
        // HubSpot API is case sensitive
        if (isset($contactFields['broker_email'])) {
            $contactFields['broker_email'] = strtolower($contactFields['broker_email']);
        }


        if (!isset($contactFields['email']) || !isset($contactFields['firstname']) || !isset($contactFields['originator'])) {
            echo '{"status":"error","message":"Required fields are missing."}';
        } else {
            $contactFields['hubspot_owner_id'] = 4606650; //setting HubSpot owner to Rodney


            if (isset($contactFields['originator'])) {//masking Genius status codes
                if ($contactFields['originator'] == "genius") {
                    if (isset($contactFields['hs_lead_status'])) {
                        if (array_key_exists($contactFields['hs_lead_status'], $appConfig['hubspot']['dealStatuses'])) {
                            $contactFields['hs_lead_status'] = $appConfig['hubspot']['dealStatuses'][$contactFields['hs_lead_status']];
                        }
                    }

                    if (isset($contactFields['dob'])) {
                        $contactFields['dob'] = str_replace('/', '-', $contactFields['dob']);
                        if (strpos($contactFields['dob'], '-') !== false)
                            $contactFields['dob'] = strtotime($contactFields['dob']) * 1000;
                    }

                    if (isset($contactFields['settlement_dt'])) {
                        $contactFields['settlement_dt'] = str_replace('/', '-', $contactFields['settlement_dt']);
                        if (strpos($contactFields['settlement_dt'], '-') !== false)
                            $contactFields['settlement_dt'] = strtotime($contactFields['settlement_dt']) * 1000;
                    }


                    //Setting original soure code here
                    //$contactFields = $hubspotExt->getSourceInformation($contactFields, 'genius', $hubspot);
                }
            }

            if (isset($contactFields['gender'])) {
                $contactFields['gender'] = ucfirst($contactFields['gender']);
            }

            if (isset($contactFields['state'])) {
                $contactFields['state'] = strtoupper($contactFields['state']);
            }

            if (isset($contactFields['loan_purpose'])) {
                $contactFields['loan_purpose'] = str_replace(" ", "_", strtolower($contactFields['loan_purpose']));
            }


            $hsResponse = $hubspot->contacts()->create_contact($contactFields);

            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Update Request: ' . json_encode($contactFields));
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Update Response Body: ' . json_encode($hsResponse));
            }


            //@TODO if Contact already exists we need to update it

            if (isset($hsResponse->status)) {
                if ($hsResponse->status == "error") {
                    if ($hsResponse->message == "Contact already exists") {
                        $vid = $hsResponse->identityProfile->vid;
                        $hubspotData = $hubspot->contacts()->get_contact_by_id($vid);
                        $hsResponse = new stdClass();
                        $hsResponse->vid = $vid;
                        $hsResponse->message = "Contact already exists. A new deal was created for existing contact.";
                    }
                }
            } else {
                $hubspotData = $hsResponse;
            }

            $fields = array();
            if (isset($hubspotData->vid)) {

                $fields['vid'] = $hubspotData->vid;

                //extracting contact information from HubSpot contact create request response.
                foreach ($hubspotData->properties as $key => $property) {
                    if ($key == "firstname" || $key == "hubspot_owner_id" ||
                            $key == "loan_purpose" || $key == "approved_loan_amount" ||
                            $key == "settlement_dt" || $key == "total_sales_amount" ||
                            $key == "hs_lead_status")
                        $fields[$key] = $property->value;
                }


                //making sure lead status from request is used
                $fields['hs_lead_status'] = $contactFields['hs_lead_status'];

                if (!isset($fields['hubspot_owner_id'])) {
                    $fields['hubspot_owner_id'] = 4606650;
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
                    "value": "' . $fields['hubspot_owner_id'] . '",
                    "name": "hubspot_owner_id"
                },
                {
                    "value": "newbusiness",
                    "name": "dealtype"
                }';

                if (isset($fields['firstname']))
                    $dealJSON .= ',{
                    "value": "' . $fields['firstname'] . '\'s Deal",
                    "name": "dealname"
                	}';

                if (isset($fields['approved_loan_amount']))
                    $dealJSON .= ',{
                    "value": "' . $fields['approved_loan_amount'] . '",
                    "name": "loan_amount"
                	}';

                if (isset($fields['loan_purpose']))
                    $dealJSON .= ',{
                    "value": "' . $fields['loan_purpose'] . '",
                    "name": "loan_purpose"
                	}';

                if (isset($fields['hs_lead_status'])) {
                    $dealJSON .= ',{
                    "value": "' . $fields['hs_lead_status'] . '",
                    "name": "deal_status"
                	}';


                    if (isset($appConfig['hubspot']['dealStages'][$fields['hs_lead_status']])) {
                        $dealJSON .= ',{
                    "value": "' . $appConfig['hubspot']['dealStages'][$fields['hs_lead_status']] . '",
                    "name": "dealstage"
                	}';

                        if ($appConfig['hubspot']['dealStages'][$fields['hs_lead_status']] == "closedlost") {
                            $dealJSON .= ',{
                    "value": "' . time() * 1000 . '",
                    "name": "closedate"
                	}';
                        }
                    }

                    if (isset($appConfig['hubspot']['dealLostReasons'][$fields['hs_lead_status']])) {
                        $dealJSON .= ',{
                    "value": "' . $appConfig['hubspot']['dealLostReasons'][$fields['hs_lead_status']] . '",
                    "name": "closed_lost_reason"
                	}';
                    }
                }

                if (isset($fields['total_sales_amount']))
                    $dealJSON .= ',{
                    "value": "' . $fields['total_sales_amount'] . '",
                    "name": "amount"
                	}';

                //if deal status is not lost then execute this block to set correct close date
                if (isset($fields['settlement_dt']) && $appConfig['hubspot']['dealStages'][$fields['hs_lead_status']] != "closedlost") {
                    $dealJSON .= ',{
                    "value": "' . $fields['settlement_dt'] . '",
                    "name": "closedate"
                	}';
                }

                $dealJSON .= ']}';



                $dealSyncResponseArr = $hubspotExt->insertDeal($dealJSON);
                $dealResponse = json_decode($dealSyncResponseArr[1]);

                $hsResponse->dealId = $dealResponse->dealId;


                if ($app->log->getEnabled()) {
                    $app->log->debug('[' . date('H:i:s', time()) . '] Deal Update Request: ' . $dealJSON);
                    $app->log->debug('[' . date('H:i:s', time()) . '] Deal Update Response Body: ' . $dealSyncResponseArr[1]);
                    $app->log->debug('[' . date('H:i:s', time()) . '] Deal Update Response: ' . $dealSyncResponseArr[0]);
                }
            }

            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] Response body: ' . json_encode($hsResponse));
            }


            echo json_encode($hsResponse);
        }
    });


    /*
     * Called from HubSpot to synchronize contact on ContactSpace
     * Receives JSON object in request body
     */
    $app->post('/update', function() use ($app) {
        $appConfig = $app->config('custom');

        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();

        $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);
        $contactFields = $app->request->post();

        //making sure broker_email is always in lower case to avoid matching problem as
        // HubSpot API is case sensitive
        if (isset($contactFields['broker_email'])) {
            $contactFields['broker_email'] = strtolower($contactFields['broker_email']);
        }






        if (!isset($contactFields['dealId'])) {
            echo '{"status":"error","message":"dealId is required."}';
        } else {
            if (isset($contactFields['dealId'])) {
                $dealId = $contactFields['dealId'];
                unset($contactFields['dealId']);
            }

            if (isset($contactFields['vid'])) {
                $vid = $contactFields['vid'];
                unset($contactFields['vid']);
            } else {
                require_once('app/lib/hubspotext.php');
                $hubspotExt = new Custom\Libs\HubSpotExt();
                $dealOfInterest = $hubspotExt->getDeal($dealId)[1];
                $fields = array();

                if (isset(json_decode($dealOfInterest)->associations->associatedVids[0])) {//update lead status and settlement date
                    $contactID = json_decode($dealOfInterest)->associations->associatedVids[0];
                    $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);

                    $hsContact = $hubspot->contacts()->get_contact_by_id($contactID);
                    if (isset($hsContact->vid)) {
                        $vid = $hsContact->vid;
                    } else {
                        if ($app->log->getEnabled()) {
                            $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Error: No contact associated with thiss deal.');
                        }
                    }
                } else {
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Error: Deal not found.');
                }
            }

            if (isset($vid)) {
                $contactFields['hubspot_owner_id'] = 4606650; //setting HubSpot owner to Rodney

                if (isset($contactFields['originator'])) {//masking Genius status codes
                    if ($contactFields['originator'] == "genius") {
                        if (isset($contactFields['hs_lead_status'])) {
                            if (array_key_exists($contactFields['hs_lead_status'], $appConfig['hubspot']['dealStatuses'])) {
                                $contactFields['hs_lead_status'] = $appConfig['hubspot']['dealStatuses'][$contactFields['hs_lead_status']];
                            }
                        }

                        if (isset($contactFields['dob'])) {
                            $contactFields['dob'] = str_replace('/', '-', $contactFields['dob']);

                            if (strpos($contactFields['dob'], '-') !== false)
                                $contactFields['dob'] = strtotime($contactFields['dob']) * 1000;
                        }

                        if (isset($contactFields['settlement_dt'])) {
                            $contactFields['settlement_dt'] = str_replace('/', '-', $contactFields['settlement_dt']);

                            if (strpos($contactFields['settlement_dt'], '-') !== false)
                                $contactFields['settlement_dt'] = strtotime($contactFields['settlement_dt']) * 1000;
                        }
                    }
                }

                if (isset($contactFields['gender'])) {
                    $contactFields['gender'] = ucfirst($contactFields['gender']);
                }

                if (isset($contactFields['loan_purpose'])) {
                    $contactFields['loan_purpose'] = str_replace(" ", "_", strtolower($contactFields['loan_purpose']));
                }

                $hsResponse = $hubspot->contacts()->update_contact($vid, $contactFields);

                if ($app->log->getEnabled()) {
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Update Request: ' . json_encode($contactFields));
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Update Response Body: ' . json_encode($hsResponse));
                }
            }

            if (isset($contactFields['hs_lead_status'])) {
                if (isset($appConfig['hubspot']['dealStages'][$contactFields['hs_lead_status']])) {
                    $contactFields['dealstage'] = $appConfig['hubspot']['dealStages'][$contactFields['hs_lead_status']];

                    if ($contactFields['dealstage'] == "closedlost")
                        $contactFields['closedate'] = time() * 1000;
                }

                if (isset($appConfig['hubspot']['dealLostReasons'][$contactFields['hs_lead_status']]))
                    $contactFields['closed_lost_reason'] = $appConfig['hubspot']['dealLostReasons'][$contactFields['hs_lead_status']];
            }

            $fields = array();
            if (isset($dealId)) {

                $dealFields = array();

                //extracting contact information from HubSpot contact create request response.
                foreach ($contactFields as $key => $value) {

                    if ($key == "firstname" || $key == "hubspot_owner_id" ||
                            $key == "loan_purpose" || $key == "approved_loan_amount" ||
                            $key == "settlement_dt" || $key == "total_sales_amount" ||
                            $key == "hs_lead_status" || $key == "dealstage" ||
                            $key == "closed_lost_reason" || $key == "closedate") {
                        $property = new stdClass();
                        switch ($key) {
                            case 'firstname':
                                if (isset($contactFields[$key])) {
                                    $property->name = "dealname";
                                    $property->value = $contactFields[$key] . "'s Deal";
                                }
                                break;
                            case 'approved_loan_amount':
                                if (isset($contactFields[$key])) {
                                    $property->name = "loan_amount";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'loan_purpose':
                                if (isset($contactFields[$key])) {
                                    $property->name = "loan_purpose";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'hubspot_owner_id':
                                if (isset($contactFields[$key])) {
                                    $property->name = "hubspot_owner_id";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'hs_lead_status':
                                if (isset($contactFields[$key])) {
                                    $property->name = "deal_status";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'dealstage':
                                if (isset($contactFields[$key])) {
                                    $property->name = "dealstage";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'closedate':
                                if (isset($contactFields[$key])) {
                                    $property->name = "closedate";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'closed_lost_reason':
                                if (isset($contactFields[$key])) {
                                    $property->name = "closed_lost_reason";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'settlement_dt':
                                if (isset($contactFields[$key]) && $contactFields['dealstage'] != "closedlost") {
                                    $property->name = "closedate";
                                    $property->value = $contactFields[$key];
                                }
                                break;

                            case 'total_sales_amount':
                                if (isset($contactFields[$key])) {
                                    $property->name = "amount";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                        }
                        if (isset($contactFields[$key]) && isset($property->name)) {
                            $dealFields[] = $property;
                        }
                    }
                }


                $dealPropertiesObj = new stdClass();
                $dealPropertiesObj->properties = $dealFields;





                $dealGetResponseArr = $hubspotExt->updateDeal($dealId, json_encode($dealPropertiesObj));
                $dealResponse = json_decode($dealGetResponseArr[1]);

                if ($app->log->getEnabled()) {
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Request Body: ' . json_encode($dealPropertiesObj));
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Response Body: ' . $dealGetResponseArr[1]);
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Response Status: ' . $dealGetResponseArr[0]);
                }
            }


            echo json_encode($hsResponse);
        }
    });
});



/**
 * ContactSpace group
 * */
$app->group('/contactspace', function () use ($app) {
    /*
     * Called from ContactSpace to synchronize corresponding contact on HubSpot
     * Receive POST parameters
     */
    $app->post("/updateHubSpot", function() use ($app) {
        $callID = $app->request->post('CallID');


        require_once('app/lib/contactspace.php');
        $contactSpace = new ContactSpace();


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

        require_once('app/lib/contactspace.php');
        $contactSpace = new ContactSpace();

        //post to ContactSpace
        $contactSpaceSyncResponseArr = $contactSpace->insertRecord($entityBody, $app);

        echo $contactSpaceSyncResponseArr[0];
    });



    /*
     * Called from HubSpot to synchronize contact on ContactSpace based on days since settlement date
     * Receives JSON object in request body
     */
    $app->get('/outbound/:months', function($months) use ($app) {
        $entityBody = $app->request->getBody();

        require_once('app/lib/contactspace.php');
        $contactSpace = new ContactSpace();


        if ($months) {
            switch ($months) {
                case '1':
                    $contactSpace->_datasetID = 70;
                    break;
                case '6':
                    $contactSpace->_datasetID = 71;
                    break;
                case '11':
                    $contactSpace->_datasetID = 72;
                    break;
                case '18':
                    $contactSpace->_datasetID = 73;
                    break;
                case '23':
                    $contactSpace->_datasetID = 74;
                    break;
                case '36':
                    $contactSpace->_datasetID = 75;
                    break;
            }
        }


        //post to ContactSpace
        $contactSpaceSyncResponseArr = $contactSpace->insertRecord($entityBody, $app);

        echo $contactSpaceSyncResponseArr[0];
    });


    /*
     * Called from HubSpot to synchronize old contacts with settlement date on ContactSpace
     * Receives JSON object in request body
     */
    $app->post('/outbound_adhoc', function() use ($app) {
        $entityBody = $app->request->getBody();

        require_once('app/lib/contactspace.php');
        $contactSpace = new ContactSpace();


        $hubspotData = json_decode($entityBody);

        $fields = array();
        $fields['vid'] = $hubspotData->vid;

        //extracting contact information from HubSpot
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "settlement_dt") {
                $fields[$key] = $property->value;
                break;
            }
        }

        if (isset($fields['settlement_dt'])) {
            $settlementDate = $fields['settlement_dt'] / 1000;
            $currentTimestamp = time();


            $timeSinceSettlement = $currentTimestamp - $settlementDate;

            $daysSinceSettlemt = floor($timeSinceSettlement / (60 * 60 * 24));

            if ($daysSinceSettlemt >= 30 && $daysSinceSettlemt < 180) {
                $contactSpace->_datasetID = 70;
            } else if ($daysSinceSettlemt >= 180 && $daysSinceSettlemt < 330) {
                $contactSpace->_datasetID = 71;
            } else if ($daysSinceSettlemt >= 330 && $daysSinceSettlemt < 540) {
                $contactSpace->_datasetID = 72;
            } else if ($daysSinceSettlemt >= 540 && $daysSinceSettlemt < 690) {
                $contactSpace->_datasetID = 73;
            } else if ($daysSinceSettlemt >= 690 && $daysSinceSettlemt < 1080) {
                $contactSpace->_datasetID = 74;
            } else if ($daysSinceSettlemt >= 1080) {
                $contactSpace->_datasetID = 75;
            }

            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] Days passed since settlement: ' . $daysSinceSettlemt);
                $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Selected Dataset: ' . $contactSpace->_datasetID);
            }
        }


        //post to ContactSpace
        $contactSpaceSyncResponseArr = $contactSpace->insertRecord($entityBody, $app, false);

        echo $contactSpaceSyncResponseArr[0];
    });
});




/**
 * Deal group
 * */
$app->group('/deal', function () use ($app) {
    /*
     * Update deal stage
     */
    $app->post('/updateStage', function() use ($app) {
        $entityBody = $app->request->getBody();


        $hubspotData = json_decode($entityBody);

        $fields = array();
        $fields['vid'] = $hubspotData->vid;

        //extracting contact information from HubSpot
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "num_associated_deals" || $key == "hs_lead_status")
                $fields[$key] = $property->value;
        }

        print_r($fields);
    });

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

        switch ($type) {
            case 'carsales':
                $_instanceEmailLeads->username = "carsales@dev.1800approved.com.au";
                $_instanceEmailLeads->password = "CarSales101";

                $_instanceEmailLeads->synchronizeTestEmailLeads($app);
                break;
            case 'loanplace':
                $_instanceEmailLeads->username = "loanplace@dev.1800approved.com.au";
                $_instanceEmailLeads->password = "~.RQk#,IK}dy";

                $_instanceEmailLeads->synchronizeLoanPlaceEmailLeads($app);
                break;
            case 'test':
                $_instanceEmailLeads->username = "umair@dev.1800approved.com.au";
                $_instanceEmailLeads->password = "U3D*vDfkF(;A";

                $_instanceEmailLeads->synchronizeTestEmailLeads($app);
                break;
        }

        echo "Cron executed successfully";
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
        require_once('app/lib/hubspotext.php');
        $hubspotExt = new Custom\Libs\HubSpotExt();

        $vid = $app->request->post("vid");
        $gid = $app->request->post("gid");
        $settlementDate = $app->request->post("settlement_date");
        $status = $app->request->post("status");

        $customConfig = $app->config('custom');

        $dealOfInterest = $hubspotExt->getDeal($vid)[1];
        $fields = array();

        if (isset(json_decode($dealOfInterest)->associations->associatedVids[0])) {//update lead status and settlement date
            $contactID = json_decode($dealOfInterest)->associations->associatedVids[0];
            $hubspot = new Fungku\HubSpot($customConfig['hubspot']['config']['HUBSPOT_API_KEY']);


            if (isset($settlementDate)) {
                $fields['settlement_dt'] = $settlementDate;
            }

            if (array_key_exists($status, $customConfig['hubspot']['dealStatuses'])) {
                $fields['hs_lead_status'] = $customConfig['hubspot']['dealStatuses'][$status];
            }

            $hsUpdateResponse = $hubspot->contacts()->update_contact($contactID, $fields);

            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot contact Update Requst Body: ' . json_encode($fields));
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot contact Update Response Body: ' . json_encode($hsUpdateResponse));
            }
        } else {
            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] No HubSpot contact associated with this deal: ' . json_encode($fields));
            }
        }



        //update deal status
        if (array_key_exists($status, $customConfig['hubspot']['dealStatuses'])) {
            $status = $customConfig['hubspot']['dealStatuses'][$app->request->post("status")];

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
                    $key == "marital_status" || $key == "number_of_children" || $key == "hear_from" ||
                    $key == "term_length" || $key == "business_no" || $key == "broker_email") {
                $fields[$key] = $property->value;
            }
        }


        $invalidEmails = array("", "unemployed@1800approved.com.au", "unemployedqld@1800approved.com.au", "bankrupt@1800approved.com.au", "bankruptqld@1800approved.com.au");

        if (in_array($fields['broker_email'], $invalidEmails)) {
            echo "500";
            return;
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
        $fields['vid'] = $hubspotData->vid;


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
                    $key == "mobilephone" || $key == "private_phone_number" || $key == "suburb" || $key == "hear_from" ||
                    $key == "term_length" || $key == "business_no") {
                $fields[$key] = $property->value;
            }
        }







        //@TODO Remove JUST USE FOR TESTING
        //$fields['broker_email'] = "umair@tezrosolutions.com";

        $fields['leads_type'] = "QuickQuote";
        $fields['accessCode'] = "money3";
        $fields['accessPass'] = "flying123";

        if (isset($fields['current_residency_length'])) {
            $fields['current_residency_length'] = (int) preg_replace('/\D/', '', $fields['current_residency_length']);
            $fields['current_residency_length'] = intval($fields['current_residency_length'] / 12);
            $fields['residmonth'] = intval(($fields['current_residency_length'] % 12) * 12);
        }

        if (isset($fields['employment_length'])) {
            $fields['employment_length'] = (int) preg_replace('/\D/', '', $fields['employment_length']);
            $fields['employment_length'] = intval($fields['employment_length'] / 12);
            $fields['emplengthmonth'] = intval(($fields['employment_length'] % 12) * 12);
        }

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

        if (isset($fields['business_no'])) {
            if (strlen($fields['business_no']) == 10) {
                $validAreaCodes = array("02", "03", "07", "08", "04");

                $fields['businessNoAreaCode'] = substr($fields['business_no'], 0, 2);

                if (!in_array($fields['businessNoAreaCode'], $validAreaCodes)) {
                    unset($fields['businessNoAreaCode']);
                }

                $fields['businessNo'] = substr($fields['business_no'], 2, 8);
            }
        }

        if (isset($fields['private_phone_number'])) {
            $fields['coplMob'] = "<Mobile>" . $fields['private_phone_number'] . "</Mobile>";
        } elseif (isset($fields['phone'])) {
            $fields['coplMob'] = "<Mobile>" . $fields['phone'] . "</Mobile>";
        } elseif (isset($fields['mobilephone'])) {
            $fields['coplMob'] = "<Mobile>" . $fields['mobilephone'] . "</Mobile>";
        } else {
            $fields['coplMob'] = "<Mobile></Mobile>";
        }


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
            $fields['leads_finance_type'] = "other";




        if (!empty($fields['employment_type_']))
            $fields['employment'] = $instanceGenius->getCoplCodes('employnents_types', $fields['employment_type_'], 'employementtype');
        else
            $fields['employment'] = "";


        if (!empty($fields['home_sts']))
            $fields['property'] = $instanceGenius->getCoplCodes('residential_statuses', $fields['home_sts'], 'residentialstatus');
        else
            $fields['property'] = "";


        if (!empty($fields['hear_from']))
            $fields['hear_from'] = $instanceGenius->getCoplCodes('source_statuses', $fields['hear_from'], 'sourcestatus');
        else
            $fields['hear_from'] = "";


        if (!empty($fields['totalincome'])) {
            if (!is_numeric($fields['totalincome'])) {
                $fields['totalincome'] = "";
            }
        } else {
            $fields['totalincome'] = "";
        }

        if (!empty($fields['gender'])) {

            switch (strtolower($fields['gender'])) {
                case 'm':
                    $fields['gender'] = "male";
                    break;
                case 'f':
                    $fields['gender'] = "female";
                    break;
            }
        }

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
        $form_fields['finder_date_posted'] = $app->request->post("date_posted");
        $form_fields['firstname'] = $app->request->post("fname");
        $form_fields['lastname'] = $app->request->post("lname");
        $form_fields['phone'] = $app->request->post("phone");
        $form_fields['email'] = $app->request->post("email");
        $form_fields['totalincome'] = $app->request->post("income");
        $form_fields['australian_citizen'] = (strtolower($app->request->post("australian_citizen")) == "yes") ? true : false;
        $form_fields['credit_defaults'] = (strtolower($app->request->post("credit_defaults")) == "yes") ? true : false;
        $form_fields['hs_lead_status'] = "PROSPECT";

        //@TODO Put original source code here


        $appConfig = $app->config('custom');
        $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);


        //Setting original soure code here
        /* require_once('app/lib/hubspotext.php');
          $hubspotExt = new Custom\Libs\HubSpotExt();
          $form_fields = $hubspotExt->getSourceInformation($form_fields, 'finder', $hubspot);
         */

        $hsResponse = $hubspot->contacts()->create_contact($form_fields);


        if ($app->log->getEnabled()) {
            $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Request: ' . json_encode($form_fields));
            $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Response Body: ' . json_encode($hsResponse));
        }

        echo 200;
    });
});


/**
 * Fields group
 * */
$app->group('/field', function () use ($app) {

    $app->get("/details", function() use ($app) {

        $appConfig = $app->config('custom');

        $fieldParams = $app->request->get();
        if (isset($fieldParams['field']) && isset($fieldParams['callback'])) {
            $url = "http://api.hubapi.com/contacts/v2/properties/named/" . $fieldParams['field'] . "?portalId=" . $appConfig['hubspot']['config']['HUBSPOT_PORTAL_ID'] . "&hapikey=" . $appConfig['hubspot']['config']['HUBSPOT_API_KEY'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);


            echo $fieldParams['callback'] . "(";
            echo $response;
            echo ")";
        } else {
            echo "{'error': 'Invalid message'}";
        }
    });
});


/**
 * Fields group
 * */
$app->group('/misc', function () use ($app) {
    $app->get("/update_deal", function() use ($app) {
        $appConfig = $app->config('custom');

        require_once('app/lib/hubspotext.php');
        $dealIDs = json_decode(file_get_contents("https://api.myjson.com/bins/5b62v"));

        foreach ($dealIDs as $dealID) {


            $hubspotExt = new Custom\Libs\HubSpotExt();
            $dealOfInterest = $hubspotExt->getDeal($dealID->deal_id)[1];
            $fields = array();

            if (isset(json_decode($dealOfInterest)->associations->associatedVids[0])) {//update lead status and settlement date
                $contactID = json_decode($dealOfInterest)->associations->associatedVids[0];
                $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);

                $hsContact = $hubspot->contacts()->get_contact_by_id($contactID);

                //if no contact is associated
                if (!isset($hsContact->vid)) {
                    echo '{"status": "error", "message": "No contact associated with this deal."}';
                    return;
                }

                $contactFields = array();

                foreach ($hsContact->properties as $key => $property) {

                    if ($key == "firstname" || $key == "hubspot_owner_id" ||
                            $key == "loan_purpose" || $key == "approved_loan_amount" ||
                            $key == "settlement_dt" || $key == "total_sales_amount" ||
                            $key == "hs_lead_status") {
                        $contactFields[$key] = $property->value;
                    }
                }



                if (isset($contactFields['hs_lead_status'])) {
                    if (isset($appConfig['hubspot']['dealStages'][$contactFields['hs_lead_status']])) {
                        $contactFields['dealstage'] = $appConfig['hubspot']['dealStages'][$contactFields['hs_lead_status']];

                        if ($contactFields['dealstage'] == "closedlost")
                            $contactFields['closedate'] = time() * 1000;
                    }

                    if (isset($appConfig['hubspot']['dealLostReasons'][$contactFields['hs_lead_status']]))
                        $contactFields['closed_lost_reason'] = $appConfig['hubspot']['dealLostReasons'][$contactFields['hs_lead_status']];
                }

                $dealFields = array();
                //extracting contact information from HubSpot contact create request response.
                foreach ($contactFields as $key => $value) {

                    if ($key == "firstname" || $key == "hubspot_owner_id" ||
                            $key == "loan_purpose" || $key == "approved_loan_amount" ||
                            $key == "settlement_dt" || $key == "total_sales_amount" ||
                            $key == "hs_lead_status" || $key == "dealstage" ||
                            $key == "closed_lost_reason" || $key == "closedate") {
                        $property = new stdClass();
                        switch ($key) {
                            case 'firstname':
                                if (isset($contactFields[$key])) {
                                    $property->name = "dealname";
                                    $property->value = $contactFields[$key] . "'s Deal";
                                }
                                break;
                            case 'approved_loan_amount':
                                if (isset($contactFields[$key])) {
                                    $property->name = "loan_amount";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'loan_purpose':
                                if (isset($contactFields[$key])) {
                                    $property->name = "loan_purpose";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'hubspot_owner_id':
                                if (isset($contactFields[$key])) {
                                    $property->name = "hubspot_owner_id";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'hs_lead_status':
                                if (isset($contactFields[$key])) {
                                    $property->name = "deal_status";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'dealstage':
                                if (isset($contactFields[$key])) {
                                    $property->name = "dealstage";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'closedate':
                                if (isset($contactFields[$key])) {
                                    $property->name = "closedate";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'closed_lost_reason':
                                if (isset($contactFields[$key])) {
                                    $property->name = "closed_lost_reason";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                            case 'settlement_dt':
                                if (isset($contactFields[$key])) {
                                    if (isset($contactFields['dealstage'])) {
                                        if ($contactFields['dealstage'] != "closedlost") {
                                            $property->name = "closedate";
                                            $property->value = $contactFields[$key];
                                        }
                                    } else {
                                        $property->name = "closedate";
                                        $property->value = $contactFields[$key];
                                    }
                                }
                                break;

                            case 'total_sales_amount':
                                if (isset($contactFields[$key])) {
                                    $property->name = "amount";
                                    $property->value = $contactFields[$key];
                                }
                                break;
                        }
                        if (isset($contactFields[$key]) && isset($property->name)) {
                            $dealFields[] = $property;
                        }
                    }
                }
                echo $dealID->deal_id . " => ";
                print_r($dealFields);
                echo "<br>";
                $dealData = new stdClass();
                $dealData->properties = $dealFields;

                $dealGetResponseArr = $hubspotExt->updateDeal($dealID->deal_id, json_encode($dealData));
                echo $dealGetResponseArr[1];
                echo "<br><br><br>";


                if ($app->log->getEnabled()) {
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Request Body: ' . json_encode($dealData));
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Response Body: ' . $dealGetResponseArr[1]);
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Deal Update Response Status: ' . $dealGetResponseArr[0]);
                }
            } else {
                echo $dealID->deal_id . " => ";
                echo '{"status": "error", "message": "Deal not found."}<br><br><br>';
            }
        }
    });

    $app->get("/match_zip/:zip", function($zip) use ($app) {
        //print preg_match('/^(^3[0-9]{3})$/',$zip);//Alister
        //print preg_match('/^(4[7|8][0-9]{2})$/',$zip);//Damien
        //print preg_match('/^(42[0-9]{2})$/',$zip);//Ryan
        //print preg_match('/^(6[0-9]{3})$/',$zip);//Henry
        //print preg_match('/^(4[4-6]{1}[0-9]{2})$/',$zip);//Wayne
        //print preg_match('/^(4[1-3]{1}[0-9]{2})$/',$zip);//Jed
        //print preg_match('/^(2[0-7]{1}[0-9]{2})$/',$zip);//Russel Dunn
        //print preg_match('/^(5[0-9]{3})$/',$zip);//Russel Pelvin
        print preg_match('/^(0[8|9][0-9]{2})$/', $zip); //Bronwyn
        //print preg_match('/^(4[2-3]{1}[0-9]{2})$/',$zip);//Matthew
        //print preg_match('/^(4[0|1][1-9]{1}[0-9]{1})$/',$zip);//James Grady
    });

    $app->post("/track_manual_assignments", function() use ($app) {
        $entityBody = $app->request->getBody();
        $hubspotData = json_decode($entityBody);

        $appConfig = $app->config('custom');
        $firebase = new \Firebase\FirebaseLib($appConfig['firebase']['config']['FIREBASE_APP_URL'], $appConfig['firebase']['config']['FIREBASE_TOKEN']);

        $customerFields = array();
        $customerFields['vid'] = $hubspotData->vid;
        $profile_url_key = "profile-url";
        $customerFields['profile_url'] = $hubspotData->$profile_url_key;

        //extracting contact information from HubSpot
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "firstname" || $key == "lastname" || $key == "zip" || $key == "loan_purpose" || $key == "email" || $key == "employment_type_" || $key == "credit_status" || $key == "dob" || $key == "broker_email") {
                $customerFields[$key] = $property->value;
            }
        }


        $app->log->debug('[' . date('H:i:s', time()) . '] Customer Fields: ' . json_encode($customerFields));


        $thisMonth = "/" . date("m-Y") . "/";



        if (date('H') >= $appConfig['firebase']['config']['daily_reset_hour']) {
            $todayDay = date("d-m", strtotime("+1 days"));
        } else {
            $todayDay = date("d-m");
        }


        $brokers = json_decode($firebase->get("/" . date("m-Y")));

        foreach ($brokers as $key => $broker) {

            if ($broker->email == $customerFields['broker_email']) {


                if (isset($broker->assigned->$todayDay)) {
                    //making sure the lead was not already assigned to broker today
                    foreach ($broker->assigned->$todayDay as $lead) {
                        if ($lead->email == $customerFields['email']) {
                            $app->log->debug('[' . date('H:i:s', time()) . '] WARNING: Lead already tracked and assigned to '.$broker->name);
                            return;
                        }
                    }

                    $assignmentKey = count($broker->assigned->$todayDay);
                } else
                    $assignmentKey = 0;

                $firebase->set($thisMonth . $key . "/assigned/" . $todayDay . "/" . $assignmentKey, $customerFields);
                return;
            }
        }
    });

    $app->post("/lead_distribution", function() use ($app) {


        $entityBody = $app->request->getBody();
        $hubspotData = json_decode($entityBody);


        $appConfig = $app->config('custom');
        $firebase = new \Firebase\FirebaseLib($appConfig['firebase']['config']['FIREBASE_APP_URL'], $appConfig['firebase']['config']['FIREBASE_TOKEN']);
        $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);

        require_once('app/lib/firebase.php');


        $customerFields = array();
        $customerFields['vid'] = $hubspotData->vid;
        $profile_url_key = "profile-url";
        $customerFields['profile_url'] = $hubspotData->$profile_url_key;

        //extracting contact information from HubSpot
        foreach ($hubspotData->properties as $key => $property) {
            if ($key == "firstname" || $key == "lastname" || $key == "zip" || $key == "loan_purpose" || $key == "email" || $key == "employment_type_" || $key == "credit_status" || $key == "dob" || $key == "broker_email") {
                $customerFields[$key] = $property->value;
            }
        }

        $app->log->debug('[' . date('H:i:s', time()) . '] Customer Fields: ' . json_encode($customerFields));
        $eligibleBrokers = json_decode($firebase->get("/" . date("m-Y")));

        $error = 0;
        //Checking  Employment Status
        if ($customerFields['employment_type_'] == "un_employed") {
            $app->log->debug('[' . date('H:i:s', time()) . '] ERROR: Lead not assigned, lead emplyment status is unemployed.');
            $error++;
        }

        //Checkin Credit Status
        if ($customerFields['credit_status'] == "Bankruptcy") {
            $app->log->debug('[' . date('H:i:s', time()) . '] ERROR: Lead not assigned, lead credit status is Bankruptcy.');
            $error++;
        }

        if (isset($customerFields['dob'])) {
            $now = strtotime(date('Y-m-d')); // Today in UNIX Timestamp
            $dob = $customerFields['dob'] / 1000;
            $age = $now - $dob; // As the UNIX Timestamp is in seconds, get the seconds you lived
            $age = floor($age / 60 / 60 / 24 / 365);

            if ($age < 18) {
                $app->log->debug('[' . date('H:i:s', time()) . '] ERROR: Lead not assigned, Age less than 18.');
                $error++;
            }
        }


        if (isset($customerFields['broker_email'])) {
            if ($customerFields['broker_email'] != "") {
                $app->log->debug('[' . date('H:i:s', time()) . '] ERROR: Lead already assigned to broker: ' . $customerFields['broker_email']);
                $error++;
            }
        }




        if ($error > 0) {
            echo "Lead not assigned to any broker";
            return;
        }



        /*
         * This block will be executed if employment status, credit status and DOB is correct
         */

        $brokerArr = array();
        foreach ($eligibleBrokers as $broker) {
            $brokerArr[] = $broker;
        }

        usort($brokerArr, array('\Custom\Libs\Firebase', 'sortByTotalAmount'));

        foreach ($brokerArr as $qualifiedBroker) {

            if (\Custom\Libs\Firebase::assignLeadToBroker($app, $qualifiedBroker, $firebase, $hubspot, $customerFields)) {
                echo "Lead Assigned Succesfully to " . $qualifiedBroker->name;
                return;
            }
        }

        $app->log->debug('[' . date('H:i:s', time()) . '] WARNING: Lead not assigned, no broker qualified.');
        echo "Lead not assigned to any broker";
    });


    $app->get("/post_broker_email_field", function() use ($app) {

        $appConfig = $app->config('custom');

        $fieldParams = $app->request->get();
        $url = "http://api.hubapi.com/contacts/v2/properties/named/broker_email?portalId=" . $appConfig['hubspot']['config']['HUBSPOT_PORTAL_ID'] . "&hapikey=" . $appConfig['hubspot']['config']['HUBSPOT_API_KEY'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $options = json_decode($response)->options;

        file_put_contents(__DIR__ . "/../files/broker_emails.js", 'test(' . json_encode($options) . ')');

        $url = "http://api.hubapi.com/filemanager/api/v2/files?hapikey=" . $appConfig['hubspot']['config']['HUBSPOT_API_KEY'];


        $fields = array("folder_paths" => "proxy", "files" => "@" . __DIR__ . "/../files/broker_emails.js;text/plain");

        $ch = "";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: multipart/form-data',
            'Connection: Keep-Alive'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $file_upload_result = curl_exec($ch);
        $headerInfo = curl_getinfo($ch);
        curl_close($ch);

        print_r(array("http_code" => $headerInfo['http_code'], "response" => $file_upload_result));
    });
});

