<?php

/**
 * Created by Muhammad Umair on 9/15/2015 as HubSpot helper
 * */

namespace Custom\Libs;

class HubSpotExt {

    protected $_url = "https://api.hubapi.com";
    public $_apiKey = "6af915fd-806f-483a-b10b-bcb9f94b239d";
    public $_portalID = 695602;
    public $_xmlData;

    public function __init() {
        
    }

    public function insertDeal($json) {

        $this->postURL = $this->_url . '/deals/v1/deal?hapikey=' . $this->_apiKey . '&portalId=' . $this->_portalID;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->postURL);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        //$info is not used right now
        $info = curl_getinfo($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);

        curl_close($ch);



        return array($info['http_code'], $body);
    }

    public function updateDeal($dealID, $json) {

        $this->postURL = $this->_url . '/deals/v1/deal/' . $dealID . '?hapikey=' . $this->_apiKey . '&portalId=' . $this->_portalID;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_URL, $this->postURL);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        //$info is not used right now
        $info = curl_getinfo($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);

        curl_close($ch);



        return array($info['http_code'], $body);
    }

    public function getDeal($dealID) {

        $this->getURL = $this->_url . '/deals/v1/deal/' . $dealID . '?hapikey=' . $this->_apiKey . '&portalId=' . $this->_portalID;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getURL);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        //$info is not used right now
        $info = curl_getinfo($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);

        curl_close($ch);


        return array($info['http_code'], $body);
    }

    public function getSourceInformation($contactFields, $type, $hubspot) {

        $hsSearchResponse = $hubspot->contacts()->search_contacts(array("q" => $contactFields['email']));

        $recordExists = false;
        if ($hsSearchResponse->total > 0) {
            $recordExists = true;
        }


        switch ($type) {
            case 'genius':
                $contactFields['recent_conversion_event_name'] = "Genius";

                if (!$recordExists) {
                    $contactFields['hs_analytics_source'] = "Offline Sources";

                    if (isset($contactFields['broker_full_name'])) {
                        $contactFields['hs_analytics_source_data_1'] = $contactFields['broker_full_name'];
                    }

                    if (isset($contactFields['introducer'])) {
                        $contactFields['hs_analytics_source_data_2'] = "Genius / " . $contactFields['introducer'];
                    }
                }
                break;
            case 'carsales':
                $contactFields['recent_conversion_event_name'] = "Dev_Carsales";

                if (!$recordExists) {
                    $contactFields['hs_analytics_source'] = "Other Campaigns";
                    $contactFields['hs_analytics_source_data_1'] = "carsales";
                    $contactFields['hs_analytics_source_data_2'] = "carsales.com.au / application";
                    $contactFields['first_conversion_event_name'] = "Dev_Carsales";
                }
                break;
            case 'loanplace':
                $contactFields['recent_conversion_event_name'] = "Dev_Loanplace";

                if (!$recordExists) {
                    $contactFields['hs_analytics_source'] = "Other Campaigns";
                    $contactFields['hs_analytics_source_data_1'] = "loanplace";
                    $contactFields['hs_analytics_source_data_2'] = "loanplace.com.au / application";
                    $contactFields['first_conversion_event_name'] = "Dev_Loanplace";
                }

                break;
            case 'finder':
                $contactFields['recent_conversion_event_name'] = "finder.com.au";

                if (!$recordExists) {
                    $contactFields['hs_analytics_source'] = "Other Campaigns";
                    $contactFields['hs_analytics_source_data_1'] = "finder";
                    $contactFields['hs_analytics_source_data_2'] = "finder.com.au / api";
                    $contactFields['first_conversion_event_name'] = "finder.com.au";
                }
                break;
        }

        return $contactFields;
    }

}
