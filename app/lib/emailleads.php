<?php

/**
 * Created by Muhammad Umair on 9/22/2015 as HubSpot helper
 * */

namespace Custom\Libs;

class EmailLeads {

    public $host = "{dev.1800approved.com.au/pop3/novalidate-cert}INBOX";
    public $username = "";
    public $password = "";

    public function __init() {
        
    }

    public function synchronizeTestEmailLeads($app) {
        /* try to connect */
        $inbox = imap_open($this->hostname, $this->username, $this->password) or die('Cannot connect to Server: ' . imap_last_error());


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

                $emailDate = date("Y-m-d H.i.s", strtotime($emailDate));

                $output .= $message;


                $output = preg_replace("/[\n]/", ",", $output);
                $contentA = rtrim($output, ',');
                $contentB = trim($contentA);


                $pairs = explode(',', $output);
                $a = array();
                foreach ($pairs as $pair) {
                    list($k, $v) = explode(':', $pair);
                    $v = trim($v);
                    if (empty($v)) {
                        $v = 'none';
                    }
                    $a[$k] = $v;
                }

                $json_results = json_encode($a);
                $json_obj = json_decode($json_results, true);

                foreach ($json_obj as $key => $value) {

                    $value = str_replace("<br/>", "", $value);

                    if ($key == 'FirstName') {

                        $fullname = @htmlentities(trim($value), ENT_QUOTES);
                        $nameparts = @htmlentities(explode(" ", $fullname), ENT_QUOTES);
                        $lastname = @htmlentities(array_pop($nameparts), ENT_QUOTES);
                        $firstname = @htmlentities(implode(" ", $nameparts), ENT_QUOTES);

                        if (empty($firstname)) {
                            $firstname = @htmlentities($fullname, ENT_QUOTES);
                            $lastname = '';
                        }
                    }

                    if ($key == 'LastName') {
                        $lastnameA = @htmlentities($value, ENT_QUOTES);
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
                }//END LOOP TO CREATE VARS



                $fullnameA = $firstname;
                $fullnameA = trim($fullnameA);
                $nameparts = explode(" ", $fullnameA);
                $lastname = array_pop($nameparts);
                $firstname = implode(" ", $nameparts);

                if (empty($firstname)) {
                    $firstname = $fullnameA;
                    $lastname = '';
                }

                $leads_comment = "Pref Contact Method: $pref_contact_method - Pref Contact Time: $pref_contact_time - Vehicle: $vehicle";

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
                    $forms_fields = array(
                        "firstname" => $firstname,
                        "lastname" => $lastname,
                        "email" => $leads_email,
                        "business_no" => $leads_phone_w,
                        "mobilephone" => $leads_phone_m,
                        "phone" => $leads_phone,
                        "loan_purpose" => $leads_finance_type,
                        "zip" => $leads_pcode,
                        "approved_loan_amount" => $leads_amount,
                        "interest_rate" => $rate,
                        "term_length" => $leads_term,
                        "employment_type_" => $leads_employment_type,
                        "deposit_trade_amount" => $leads_balloon,
                        "ballon_residual" => $leads_balloon2,
                        "accept_creditguide" => $accept_creditquote,
                        "accept_privacy" => $leads_privacy,
                        "hs_context" => $hs_context_json
                    );

                    //Portid and FormGuid
                    $formGuid = '48a1c82e-00ff-4e34-be68-7718ad0389ee';
                    $appConfig = $app->config('custom');

                    $hubspot = new Fungku\HubSpot($appConfig['HUBSPOT_API_KEY']);
                    print $hubspot->forms()->submit($appConfig['HUBSPOT_PORTAL_ID'], $formGuid, $form_fields);
                }
                /*                 * *** DELETE EMAILS **** */

                imap_delete($inbox, $email_number, $messageUid);
                imap_expunge($inbox);
            }
        }
    }

}
