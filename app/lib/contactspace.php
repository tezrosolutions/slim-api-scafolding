<?php

/**
 * Created by Muhammad Umair on 9/15/2015 as Contact Space helper
 * */
use Fungku\HubSpot;

class ContactSpace {

    protected $_url = "https://apidev.contactspace.com";
    public $_apiKey = "approvhdjn5kfgkjhsygeiuhfnkjndg81jsdn800";
    public $_datasetID = 14;
    public $_initiativeID = 25;
    public $_xmlData;

    public function __init() {
        
    }

    public function insertRecord($entityBody, $app) {
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
                    $key == "broker_email" || $key == "business_no" || $key == "hear_from" || $key == "zip" ||
                    $key == "contactspace_id" || $key == "interest_rate" || $key == "settlement_dt" || 
                    $key == "vehicle_make" || $key == "vehicle_variant" || $key == "broker_full_name" ||
                    $key == "bankers")
                $fields[$key] = $property->value;
        }


        if (isset($fields['contactspace_id'])) {//dup check
            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Error: Record already exists with ID ' . $fields['contactspace_id']);
            }

            return array(200, 'ContactSpace Sync Error: Record already exists with ID ' . $fields['contactspace_id']);
        }

        //preparing XML to be posted on ContactSpace

        $contactSpaceXML = "<record><Record_ID>" . $fields['vid'] . "</Record_ID>";
        
        if(array_key_exists('interest_rate', $fields)) {
            $contactSpaceXML .= "<Interest_Rate>".$fields['interest_rate']."</Interest_Rate>";
        }
        
        if(array_key_exists('settlement_dt', $fields)) {
            $contactSpaceXML .= "<Settlement_Date>".$fields['settlement_dt']."</Settlement_Date>";
        }
        
        if(array_key_exists('vehicle_make', $fields)) {
            $contactSpaceXML .= "<Vehicle_Make>".$fields['vehicle_make']."</Vehicle_Make>";
        }
        
        if(array_key_exists('vehicle_variant', $fields)) {
            $contactSpaceXML .= "<Vehicle_Variant>".$fields['vehicle_variant']."</Vehicle_Variant>";
        }
        
        if(array_key_exists('broker_full_name', $fields)) {
            $contactSpaceXML .= "<Assign_to_Broker>".$fields['broker_full_name']."</Assign_to_Broker>";
        }
        
        if(array_key_exists('bankers', $fields)) {
            $contactSpaceXML .= "<Banker>".$fields['broker_full_name']."</Banker>";
        }
        
        if (array_key_exists('mobilephone', $fields)) {
            $fields['mobilephone'] = ltrim($fields['mobilephone'], '0');
            //@TODO right now its setup with Australia country code make it dynamic later as needed
            $fields['mobilephone'] = "61" . $fields['mobilephone'];
            $contactSpaceXML .= "<Mobile_Phone>" . $fields['mobilephone'] . "</Mobile_Phone>";
        } elseif (array_key_exists('phone', $fields)) {
            $fields['phone'] = ltrim($fields['phone'], '0');
            //@TODO right now its setup with Australia country code make it dynamic later as needed
            $fields['phone'] = "61" . $fields['phone'];
            //$contactSpaceXML .= "<Phone>" . $fields['phone'] . "</Phone>";
            $contactSpaceXML .= "<Mobile_Phone>" . $fields['phone'] . "</Mobile_Phone>";
        } elseif (array_key_exists('business_no', $fields)) {
            $fields['business_no'] = ltrim($fields['business_no'], '0');
            //@TODO right now its setup with Australia country code make it dynamic later as needed
            $fields['business_no'] = "61" . $fields['business_no'];
            //$contactSpaceXML .= "<Work_Phone>" . $fields['business_no'] . "</Work_Phone>";
            $contactSpaceXML .= "<Mobile_Phone>" . $fields['business_no'] . "</Mobile_Phone>";
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

        if (array_key_exists('zip', $fields))
            $contactSpaceXML .= "<Postal_Code>" . $fields['zip'] . "</Postal_Code>";

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

        if (array_key_exists('hear_from', $fields)) {
            $customConfig = $app->config('custom');
            $contactSpaceXML .= "<Where_did_you_hear_about_us>" . $customConfig['contactspace']['sourceCodes'][$fields['hear_from']] . "</Where_did_you_hear_about_us>";
        }


        $contactSpaceXML .= "</record>";






        $this->postURL = $this->_url . '/?apikey=' . $this->_apiKey . '&function=InsertRecord&module=data&datasetid=' . $this->_datasetID . '&xmldata=' . urlencode($contactSpaceXML);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->postURL);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        //$info is not used right now
        $info = curl_getinfo($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);

        curl_close($ch);


        //log ContactSpace request and response
        if ($app->log->getEnabled()) {
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Request URL: ' . $this->postURL);
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Posted Fields: ' . $contactSpaceXML);
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Response Body: ' . $body);
            $app->log->debug('[' . date('H:i:s', time()) . '] ContactSpace Sync Response: ' . $info['http_code']);
        }

        $appConfig = $app->config('custom');



        $csResponseBody = simplexml_load_string($body);

        if (strtolower($csResponseBody->outcome->message) == "success") {//update the ContactSpace 
            $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);
            $hsFields = array();
            $hsFields['contactspace_id'] = (string) $csResponseBody->outcome->id;

            $hsUpdateResponse = $hubspot->contacts()->update_contact($fields['vid'], $hsFields);
        }


        return array($info['http_code'], $body);
    }

    public function getSingleRecord($recordID) {

        $this->getURL = $this->_url . '/?apikey=' . $this->_apiKey . '&function=GetRecord&module=data&callid=' . $recordID;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getURL);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        //$info is not used right now
        $info = curl_getinfo($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);

        curl_close($ch);



        return array($info['http_code'], $body);
    }

}
