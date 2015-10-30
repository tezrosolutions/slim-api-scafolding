<?php

/**
 * Created by Muhammad Umair on 9/22/2015 as HubSpot helper
 * */
use Fungku\HubSpot;

class EmailLeads {

    public $host = "{dev.1800approved.com.au/pop3/novalidate-cert}INBOX";
    public $username = "";
    public $password = "";

    public function __init() {
        
    }

    public function synchronizeTestEmailLeads($app) {
        /* try to connect */
        $inbox = imap_open($this->host, $this->username, $this->password) or die('Cannot connect to Server: ' . imap_last_error());


        /* grab emails */
        $emails = imap_search($inbox, 'UNSEEN');


        /* if emails are returned, cycle through each... */
        if ($emails) {

            /* begin output var */
            $output = '';
            $json_results;
            $json_obj;

            /* put the newest emails on top */
            rsort($emails);

            /* for every email... */
            foreach ($emails as $email_number) {

                /* get information specific to this email */

                $overview = imap_fetch_overview($inbox, $email_number, 0);

                $message = imap_fetchbody($inbox, $email_number, 1);


                $messageUid = $overview[0]->uid;
                $status = imap_setflag_full($inbox, $email_number, "\\Seen \\Flagged", ST_UID);
                $structure = imap_fetchstructure($inbox, $email_number, ST_UID);


                $output .= $message;


                $output = preg_replace("/[\n]/", ";", $output);
                $contentA = rtrim($output, ';');
                $contentB = trim($contentA);


                $pairs = explode(';', $output);
                $a = array();

                for ($i = 0; $i < count($pairs); $i++) {
                    if (strlen($pairs[$i]) > 1) {
                        list($k, $v) = explode(':', $pairs[$i]);
                        $v = trim($v);
                        if (empty($v)) {
                            $v = 'none';
                        }
                        $a[$k] = $v;
                    }
                }



                $json_results = json_encode($a);
                $json_obj = json_decode($json_results, true);

                foreach ($json_obj as $key => $value) {

                    $value = str_replace("<br/>", "", $value);

                    if ($key == 'First Name') {
                        $firstname = @htmlentities($value, ENT_QUOTES);
                    }

                    if ($key == 'Last Name') {
                        $lastname = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'Email') {
                        $leads_email = $value;
                    }
                    if ($key == 'Address') {
                        $leads_address = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'Suburb') {
                        $leads_city = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'State') {
                        $leads_state = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'Postcode') {
                        $leads_pcode = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'HomePhone') {
                        $leads_phone = @htmlentities($value, ENT_QUOTES);
                    }

                    if ($key == 'MobilePhone') {
                        $leads_phone_m = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'WorkPhone') {
                        $leads_phone_w = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'PreferredContactMethod') {
                        $pref_contact_method = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'PreferredContactTime') {
                        $pref_contact_time = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'Comments') {
                        $leads_comment = @htmlentities($value, ENT_QUOTES);
                    }
                    if ($key == 'LoanAmount') {
                        $loan_amount = @htmlentities($value, ENT_QUOTES);
                        $leads_amount = round($loan_amount, -3);
                        echo 'Loan Amount = ' . $loan_amount . '<br />';
                        echo 'Leads_amount =' . $leads_amount;
                    }
                    if ($key == 'Vehicle') {
                        $vehicle = @htmlentities($value, ENT_QUOTES);
                    }


                    if ($key == 'Income') {
                        $income = @htmlentities($value, ENT_QUOTES);
                    }

                    if ($key == 'Phone') {
                        $phone = @htmlentities($value, ENT_QUOTES);
                    }

                    if ($key == 'Date') {
                        $dob = @htmlentities($value, ENT_QUOTES);

                        $dobParts = explode(" ", $dob);
                        if (count($dobParts) > 1) {
                            $dob = $dobParts[0];
                        }
                    }
                }//END LOOP TO CREATE VARS



                if (isset($pref_contact_method) && isset($pref_contact_time) && isset($pref_contact_time) && isset($vehicle)) {
                    $leads_comment = "Pref Contact Method: $pref_contact_method - Pref Contact Time: $pref_contact_time - Vehicle: $vehicle";
                }
                /*                 * *** FIXED VARS **** */

                $leads_active = '1';
                $leads_finance_type = 'car';
                $leads_refid = time();
                $leads_type = 'CarSales';

                /*                 * **** UTM STUFF *** */

                $utm_source = 'carsales.com.au';
                $utm_cname = 'CSForm';
                $utm_medium = 'cpm';

                if (!empty($json_obj)) {//Check if there is an email
                    //Process the information received from email for sending it into hubspot
                    $forms_fields = array();

                    if (isset($firstname)) {
                        $forms_fields["firstname"] = $firstname;
                    }

                    if (isset($lastname)) {
                        $forms_fields["lastname"] = $lastname;
                    }


                    if (isset($leads_email)) {
                        $forms_fields["email"] = $leads_email;
                    }

                    if (isset($leads_phone_w)) {
                        $forms_fields["business_no"] = $leads_phone_w;
                    }

                    if (isset($leads_phone_m)) {
                        $forms_fields["mobilephone"] = $leads_phone_m;
                    }

                    if (isset($leads_phone)) {
                        $forms_fields["phone"] = $leads_phone;
                    }



                    if (isset($leads_finance_type)) {
                        $forms_fields["loan_purpose"] = $leads_finance_type;
                    }


                    if (isset($leads_pcode)) {
                        $forms_fields["zip"] = $leads_pcode;
                    }

                    if (isset($leads_amount)) {
                        $forms_fields["approved_loan_amount"] = $leads_amount;
                    }


                    if (isset($rate)) {
                        $forms_fields["interest_rate"] = $rate;
                    }


                    if (isset($leads_term)) {
                        $forms_fields["term_length"] = $leads_term;
                    }

                    if (isset($leads_employment_type)) {
                        $forms_fields["employment_type_"] = $leads_employment_type;
                    }

                    if (isset($leads_balloon)) {
                        $forms_fields["deposit_trade_amount"] = $leads_balloon;
                    }

                    if (isset($leads_balloon2)) {
                        $forms_fields["ballon_residual"] = $leads_balloon2;
                    }

                    if (isset($accept_creditquote)) {
                        $forms_fields["accept_creditguide"] = $accept_creditquote;
                    }

                    if (isset($leads_privacy)) {
                        $forms_fields["accept_privacy"] = $leads_privacy;
                    }

                    if (isset($income)) {
                        $forms_fields['totalincome'] = $income;
                    }

                    if (isset($phone)) {
                        $forms_fields['phone'] = ltrim($phone, '0');
                        //@TODO right now its setup with Australia country code make it dynamic later as needed
                        $forms_fields['phone'] = "61" . $forms_fields['phone'];
                    }

                    if (isset($dob)) {
                        $forms_fields['dob'] = $dob;
                    }


                    //Portid and FormGuid
                    $formGuid = '48a1c82e-00ff-4e34-be68-7718ad0389ee';
                    $appConfig = $app->config('custom');


                    $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);
                    print $hubspot->forms()->submit_form($appConfig['hubspot']['config']['HUBSPOT_PORTAL_ID'], $formGuid, $forms_fields, array());
                }
                /*                 * *** DELETE EMAILS **** */

                imap_delete($inbox, $email_number, $messageUid);
                imap_expunge($inbox);
            }
        }

        /* close the connection */
        imap_close($inbox);
    }

    public function synchronizeLoanPlaceEmailLeads($app) {
        /* try to connect */
        $inbox = imap_open($this->host, $this->username, $this->password) or die('Cannot connect to Server: ' . imap_last_error());


        /* grab emails */
        $emails = imap_search($inbox, 'UNSEEN');


        /* if emails are returned, cycle through each... */
        if ($emails) {

            /* begin output var */
            $output = '';
            $json_results;
            $json_obj;

            /* put the newest emails on top */
            rsort($emails);

            /* for every email... */
            foreach ($emails as $email_number) {

                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);

                $message = imap_fetchbody($inbox, $email_number, 1);

                $messageUid = $overview[0]->uid;
                $status = imap_setflag_full($inbox, $email_number, "\\Seen \\Flagged", ST_UID);
                $structure = imap_fetchstructure($inbox, $email_number, ST_UID);


                $output .= $message;


                $output = preg_replace("/[\n]/", ";", $output);
                $contentA = rtrim($output, ';');
                $contentB = trim($contentA);


                $pairs = explode(';', $output);

                $a = array();


                for ($i = 0; $i < count($pairs); $i++) {
                    if ($i % 2 == 0) {
                        if (strlen($pairs[$i]) > 1) {
                            $k = trim($pairs[$i]);
                            $v = trim($pairs[$i + 1]);
                            if (empty($v)) {
                                $v = 'none';
                            }
                            $a[$k] = $v;
                        }
                    }
                }



                $json_results = json_encode($a);
                $json_obj = json_decode($json_results, true);












                $leads_type = 'LoanPlace';

                /*                 * **** UTM STUFF *** */

                $utm_source = 'http://loanplace.com.au/';
                $utm_cname = 'LoanPlaceForm';
                $utm_medium = 'cpm';

                if (!empty($json_obj)) {//Check if there is an email
                    //Process the information received from email for sending it into hubspot
                    $contact_fields = array();

                    if (isset($json_obj['Name'])) {
                        $name_parts = explode(" ", $json_obj['Name']);
                        if (count($name_parts) == 2) {
                            $contact_fields["firstname"] = trim($name_parts[0]);
                            $contact_fields["lastname"] = trim($name_parts[1]);
                        }
                    }



                    if (isset($json_obj['Email Address'])) {
                        $contact_fields["email"] = $json_obj['Email Address'];
                    }

                    if (isset($json_obj['Mobile No.'])) {
                        $contact_fields["mobilephone"] = $json_obj['Mobile No.'];
                    }

                    if (isset($json_obj['What is the timescale for your purchase?'])) {
                        $contact_fields["purchase_timescale"] = $json_obj['What is the timescale for your purchase?'];
                    }

                    if (isset($json_obj['What are you looking to finance?'])) {
                        $contact_fields["loan_purpose"] = strtolower(str_replace(" ", "", $json_obj['What are you looking to finance?']));
                    }

                    if (isset($json_obj['What is the age of the asset?'])) {
                        $contact_fields["asset_age"] = $json_obj['What is the age of the asset?'];
                    }

                    if (isset($json_obj['Where are you buying from?'])) {
                        $contact_fields["buying_from"] = $json_obj['Where are you buying from?'];
                    }

                    if (isset($json_obj['What loan amount are you looking for?'])) {
                        $contact_fields["loanplace_loan_amount"] = $json_obj['What loan amount are you looking for?'];
                    }


                    if (isset($json_obj['What is your preferred loan option?'])) {
                        $contact_fields["preferred_loan_option"] = $json_obj['What is your preferred loan option?'];
                    }



                    if (isset($json_obj['What is your employment status?'])) {
                        
                        
                        switch($contact_fields["employment_type_"]) {
                            case 'Unemployed':
                                $contact_fields["employment_type_"] = "un_employed";
                                break;
                            case 'N/A - Business':
                                $contact_fields["employment_type_"] = "business";
                                break;
                             case 'Self Employed':
                                $contact_fields["employment_type_"] = "self_employment";
                                break;
                            default:
                                $contact_fields["employment_type_"] = strtolower(str_replace(" ", "_", $json_obj['What is your employment status?']));
                                break;
                        }
                        
                    }

                    if (isset($json_obj['What is your credit history?'])) {
                        $contact_fields["credit_history"] = $json_obj['What is your credit history?'];
                    }


                    if (isset($json_obj['State'])) {
                        $contact_fields["state"] = $json_obj['State'];
                    }


                    if (isset($json_obj['Postcode'])) {
                        $contact_fields["zip"] = $json_obj['Postcode'];
                    }

                    if (isset($json_obj['Provider name'])) {
                        $contact_fields["provider_name"] = $json_obj['Provider name'];
                    }

                    if (isset($json_obj['Provider ID'])) {
                        $contact_fields["provider_id"] = $json_obj['Provider ID'];
                    }



                    $appConfig = $app->config('custom');

                    $hubspot = new Fungku\HubSpot($appConfig['hubspot']['config']['HUBSPOT_API_KEY']);
                    $response = $hubspot->contacts()->create_contact($contact_fields);

                    if ($app->log->getEnabled()) {
                        $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Sync Request Body: ' . json_encode($contact_fields));
                        $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Sync Response: ' . json_encode($response));
                    }
                }
                /*                 * *** DELETE EMAILS **** */

                imap_delete($inbox, $email_number, $messageUid);
                imap_expunge($inbox);
            }
        }

        /* close the connection */
        imap_close($inbox);
    }

}
