<?php

/**
 * Created by Muhammad Umair on 9/26/2015 as Contact Space helper
 * */

namespace Custom\Libs;

class Genius {

    private $_appInstance;

    public function __init($app) {
        $this->_appInstance = $app;
    }

    public function getCoplCodes($type, $strDesc, $match) {
        $customConfig = $app->config('custom');

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
<?xml version='1.0' encoding='UTF-8'?>
<Lead>
   <AccessCode>" . $fields['accessCode'] . "</AccessCode>
   <AccessPwd>" . $fields['accessPass'] . "</AccessPwd>
   <ID>" . $fields['ID'] . "</ID>
   <FormType>" . $fields['leads_type'] . "</FormType>
   <ABN>" . $fields['abn'] . "</ABN>
   <BusinessType>" . $fields['leads_businesstype'] . "</BusinessType>
   <BusinessName>" . $fields['leads_bname'] . "</BusinessName>
   <Assignee>" . $fields['leads_assignee'] . "</Assignee>
   <LeadData>
      <Introducer>" . $fields['introducer'] . "</Introducer>
      <Salesperson>" . $fields['salesperson'] . "</Salesperson>
      <BrokerEmail>" . $fields['broker_email'] . "</BrokerEmail>
      <LoanType>" . $fields['leads_finance_type'] . "</LoanType>
      <FirstName>" . $fields['firstname'] . "</FirstName>
      <Surname>" . $fields['lastname'] . "</Surname>
      <Gender>" . $fields['leads_sex'] . "</Gender>
      " . $fields['coplArea'] . "
      " . $fields['coplPh'] . "
      " . $fields['coplMob'] . "
      <EmailAddress>" . $fields['email'] . "</EmailAddress>
      <UnitNo>" . $fields['unitno'] . "</UnitNo>
      <StreetNo>" . $fields['streetno'] . "</StreetNo>
      <Street>" . $fields['streetname'] . "</Street>
      <StreetType>" . $fields['streettype'] . "</StreetType>
      <Suburb>" . $fields['leads_city'] . "</Suburb>
      <State>" . $fields['leads_state'] . "</State>
      <PostCode>" . $fields['zip'] . "</PostCode>
      <TimeAtAddressYears>" . $fields['residyear'] . "</TimeAtAddressYears>
      <TimeAtAddressMonths>" . $fields['residmonth'] . "</TimeAtAddressMonths>
      <Status>" . $fields['property'] . "</Status>
      <DOB>" . $fields['fullBday'] . "</DOB>
      <MaritalStatus>" . $fields['marital'] . "</MaritalStatus>
      <NoOfDependencies>" . $fields['number_of_children'] . "</NoOfDependencies>
      <DriversLicenceNo>" . $fields['leads_lic'] . "</DriversLicenceNo>
      <RetailPrice>" . $fields['approved_loan_amount'] . "</RetailPrice>
      <EmploymentType>" . $fields['employment'] . "</EmploymentType>
      <TimeEmployedYears>" . $fields['employment_length'] . "</TimeEmployedYears>
      <TimeEmployedMonths>" . $fields['emplengthmonth'] . "</TimeEmployedMonths>
      <Mortgage>" . $fields['mortgagePayments'] . "</Mortgage>
      <RentPayment>" . $fields['rentPayments'] . "</RentPayment>
      <UTMSource>" . $fields['utm_source'] . "</UTMSource>
	  <UTMMedium>" . $fields['utm_medium'] . "</UTMMedium>
	  <UTMCampaign>" . $fields['utm_cname'] . "</UTMCampaign>
	  <UTMTerm>" . $fields['utm_cterm'] . "</UTMTerm>
	  <UTMContent>" . $fields['utm_ccontent'] . "</UTMContent>
      <SalaryApplicant1>" . $fields['leads_income1'] . "</SalaryApplicant1>
      <Allowances>" . $fields['leads_income2'] . "</Allowances>
	  <PrivacyIndicator></PrivacyIndicator>
      <CreditGuideIndicator></CreditGuideIndicator>
      <Comments>" . $fields['leads_comment'] . "</Comments>
      <LeadStatus>" . $fields['lead_status'] . "</LeadStatus>
   </LeadData>
</Lead>";



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_appInstance['genius']['config']['API_ENDPOINT']);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml; charset=utf-8"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        //$info = curl_getinfo($ch);
        curl_close($ch);
        return $result;
    }

}
