<?php

/**
 * Created by Muhammad Umair on 9/26/2015 as Contact Space helper
 * */

namespace Custom\Libs;

class Firebase {

    public static function sortByTotalAmount($x, $y) {
        return $y->total_budget - $x->total_budget;
    }

    public static function assignLeadToBroker($app, $qualifiedBroker, $firebase, $hubspot, $customerFields) {

        $todayDay = date("d-m");
        $thisMonth = "/" . date("m-Y") . "/";
        $qualifiedBrokerKey = strtolower(explode(" ", $qualifiedBroker->name)[1]);
        if (isset($qualifiedBroker->lead_assigment->$todayDay)) {
            $assignedToday = (int) $qualifiedBroker->lead_assigment->$todayDay;




            $monthCap = (int) $qualifiedBroker->total_leads;

            if (isset($qualifiedBroker->daily_cap)) {
                $dailyCap = (int) $qualifiedBroker->daily_cap;
            } else {
                $dailyCap = ceil($monthCap / 30);
            }






            if ($dailyCap > $assignedToday && $monthCap > $assignedToday) {
                $leadTypeMatched = false;

                foreach ($qualifiedBroker->lead_requests[0] as $key => $leadRequest) {
                    if ($key == $customerFields['loan_purpose'] && $leadRequest->count > 0) {
                        $leadTypeMatched = true;
                    }
                }

                if (!$leadTypeMatched)
                    return false;
                
                if (isset($qualifiedBroker->preferred_zip)) {
                    if (!preg_match($qualifiedBroker->preferred_zip, $customerFields['zip'])) {
                        return false;
                    }
                }


                $hsResponse = $hubspot->contacts()->update_contact($customerFields['vid'], array("broker_email" => $qualifiedBroker->email));
                if ($app->log->getEnabled()) {
                    $app->log->debug('[' . date('H:i:s', time()) . '] Assigned Broker: ' . json_encode($qualifiedBroker));
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Request: ' . json_encode(array("broker_email" => $qualifiedBroker->email)));
                    $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Response Body: ' . json_encode($hsResponse));
                }

                $firebase->set($thisMonth . $qualifiedBrokerKey . "/lead_assigment/" . $todayDay, (int) $firebase->get($thisMonth . $qualifiedBrokerKey . "/lead_assigment/" . $todayDay) + 1);
            } else {
                return false;
            }
        } else {
            if (isset($qualifiedBroker->daily_cap)) {//incase if dailycap is set to zero
                if($qualifiedBroker->daily_cap == 0)
                    return false;
            }
            
            $leadTypeMatched = false;

            foreach ($qualifiedBroker->lead_requests[0] as $key => $leadRequest) {
                if ($key == $customerFields['loan_purpose'] && $leadRequest->count > 0) {
                    $leadTypeMatched = true;
                }
            }


            if (!$leadTypeMatched)
                return false;

            
            if (isset($qualifiedBroker->preferred_zip)) {
                if (!preg_match($qualifiedBroker->preferred_zip, $customerFields['zip'])) {
                    return false;
                }
            }




            $hsResponse = $hubspot->contacts()->update_contact($customerFields['vid'], array("broker_email" => $qualifiedBroker->email));
            if ($app->log->getEnabled()) {
                $app->log->debug('[' . date('H:i:s', time()) . '] Assigned Broker: ' . json_encode($qualifiedBroker));
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Request: ' . json_encode(array("broker_email" => $qualifiedBroker->email)));
                $app->log->debug('[' . date('H:i:s', time()) . '] HubSpot Contact Response Body: ' . json_encode($hsResponse));
            }

            $firebase->set($thisMonth . $qualifiedBrokerKey . "/lead_assigment/" . $todayDay, 1);
        }

        return true;
    }

}
