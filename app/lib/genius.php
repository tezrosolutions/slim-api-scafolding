<?php

/**
 * Created by Muhammad Umair on 9/26/2015 as Contact Space helper
 * */

namespace Custom\Libs;

class Genius {

    private $_appInstance;

    public function __construct($app) {
        $this->_appInstance = $app;
    }

    public function getCoplCodes($type, $strDesc, $match) {
        $customConfig = $this->_appInstance->config('custom');

        $json = $customConfig['genius']['coplCodes'][$type];


        $parsedObjects = json_decode($json);

        $coplCodeReturn = "";
        foreach ($parsedObjects->$match as $coplCode) {
            if ($coplCode->desc == $strDesc) {
                $coplCodeReturn .= $coplCode->code;
            }
        }

        return $coplCodeReturn;
    }


    

    public function post($fields) {

        $xml = "
<Lead>
   <AccessCode>" . (isset($fields['accessCode']) ? $fields['accessCode'] : "") . "</AccessCode>
   <AccessPwd>" . (isset($fields['accessPass']) ? $fields['accessPass'] : "") . "</AccessPwd>
   <ID>" . (isset($fields['ID']) ? $fields['ID'] : "") . "</ID>
   <FormType>" . (isset($fields['leads_type']) ? $fields['leads_type'] : "") . "</FormType>
   <ABN>" . (isset($fields['abn']) ? $fields['abn'] : "") . "</ABN>
   <BusinessType>" . (isset($fields['leads_businesstype']) ? $fields['leads_businesstype'] : "") . "</BusinessType>
   <BusinessName>" . (isset($fields['company']) ? $fields['company'] : "") . "</BusinessName>
   <Assignee>" . (isset($fields['leads_assignee']) ? $fields['leads_assignee'] : "") . "</Assignee>
   <LeadData>
      <Introducer>" . (isset($fields['introducer']) ? $fields['introducer'] : "") . "</Introducer>
      <Salesperson>" . (isset($fields['salesperson']) ? $fields['salesperson'] : "") . "</Salesperson>
      <BrokerEmail>" . (isset($fields['broker_email']) ? $fields['broker_email'] : "") . "</BrokerEmail>
      <LoanType>" . (isset($fields['leads_finance_type']) ? $fields['leads_finance_type'] : "") . "</LoanType>
      <FirstName>" . (isset($fields['firstname']) ? $fields['firstname'] : "") . "</FirstName>
      <Surname>" . (isset($fields['lastname']) ? $fields['lastname'] : "") . "</Surname>
      <Gender>" . (isset($fields['gender']) ? $fields['gender'] : "") . "</Gender>
      " . $fields['coplArea'] . "
      " . $fields['coplPh'] . "
      " . $fields['coplMob'] . "
      <EmailAddress>" . (isset($fields['email']) ? $fields['email'] : "") . "</EmailAddress>
      <UnitNo>" . (isset($fields['unitno']) ? $fields['unitno'] : "") . "</UnitNo>
      <StreetNo>" . (isset($fields['streetno']) ? $fields['streetno'] : "") . "</StreetNo>
      <Street>" . (isset($fields['address']) ? $fields['address'] : "") . "</Street>
      <StreetType>" . (isset($fields['streettype']) ? $fields['streettype'] : "") . "</StreetType>
      <Suburb>" . (isset($fields['city']) ? $fields['city'] : "") . "</Suburb>
      <State>" . (isset($fields['state']) ? $fields['state'] : "") . "</State>
      <PostCode>" . (isset($fields['zip']) ? $fields['zip'] : "") . "</PostCode>
      <TimeAtAddressYears>" . (isset($fields['current_residency_length']) ? $fields['current_residency_length'] : "") . "</TimeAtAddressYears>
      <TimeAtAddressMonths>" . (isset($fields['residmonth']) ? $fields['residmonth'] : "") . "</TimeAtAddressMonths>
      <Status>" . (isset($fields['property']) ? $fields['property'] : "") . "</Status>
      <DOB>" . (isset($fields['fullBday']) ? $fields['fullBday'] : "") . "</DOB>
      <MaritalStatus>" . (isset($fields['marital']) ? $fields['marital'] : "") . "</MaritalStatus>
      <NoOfDependencies>" . (isset($fields['number_of_children']) ? $fields['number_of_children'] : "") . "</NoOfDependencies>
      <DriversLicenceNo>" . (isset($fields['leads_lic']) ? $fields['leads_lic'] : "") . "</DriversLicenceNo>
      <RetailPrice>" . (isset($fields['approved_loan_amount']) ? $fields['approved_loan_amount'] : "") . "</RetailPrice>
      <EmploymentType>" . (isset($fields['employment_type_']) ? $fields['employment_type_'] : "") . "</EmploymentType>
      <TimeEmployedYears>" . (isset($fields['employment_length']) ? $fields['employment_length'] : "") . "</TimeEmployedYears>
      <TimeEmployedMonths>" . (isset($fields['emplengthmonth']) ? $fields['emplengthmonth'] : "") . "</TimeEmployedMonths>
      <Mortgage>" . (isset($fields['mortgagePayments']) ? $fields['mortgagePayments'] : "") . "</Mortgage>
      <RentPayment>" . (isset($fields['rentPayments']) ? $fields['rentPayments'] : "") . "</RentPayment>
      <UTMSource>" . (isset($fields['utm']) ? $fields['utm'] : "") . "</UTMSource>
	  <UTMMedium>" . (isset($fields['utm_medium']) ? $fields['utm_medium'] : "") . "</UTMMedium>
	  <UTMCampaign>" . (isset($fields['utm_cname']) ? $fields['utm_cname'] : "") . "</UTMCampaign>
	  <UTMTerm>" . (isset($fields['utm_cterm']) ? $fields['utm_cterm'] : "") . "</UTMTerm>
	  <UTMContent>" . (isset($fields['utm_ccontent']) ? $fields['utm_ccontent'] : "") . "</UTMContent>
      <SalaryApplicant1>" . (isset($fields['totalincome']) ? $fields['totalincome'] : "") . "</SalaryApplicant1>
      <Allowances>" . (isset($fields['leads_income2']) ? $fields['leads_income2'] : "") . "</Allowances>
	  <PrivacyIndicator></PrivacyIndicator>
      <CreditGuideIndicator></CreditGuideIndicator>
      <Comments>" . (isset($fields['feedback_comments']) ? $fields['feedback_comments'] : "") . "</Comments>
      <LeadStatus>" . (isset($fields['hs_lead_status']) ? $fields['hs_lead_status'] : "") . "</LeadStatus>
   </LeadData>
</Lead>";


        $appConfig = $this->_appInstance->config('custom');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $appConfig['genius']['config']['API_ENDPOINT']);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml; charset=utf-8"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        //$info is not used right now
        $info = curl_getinfo($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);


        curl_close($ch);

        if ($this->_appInstance->log->getEnabled()) {
            $this->_appInstance->log->debug('[' . date('H:i:s', time()) . '] Genius Sync Request URL: ' . $appConfig['genius']['config']['API_ENDPOINT']);
            $this->_appInstance->log->debug('[' . date('H:i:s', time()) . '] Genius Sync Request: ' . $xml);
            $this->_appInstance->log->debug('[' . date('H:i:s', time()) . '] Genius Sync Response Body: ' . $body);
            $this->_appInstance->log->debug('[' . date('H:i:s', time()) . '] Genius Sync Response: ' . $info['http_code']);
        }

        return array($info['http_code'], $body);
    }

}
