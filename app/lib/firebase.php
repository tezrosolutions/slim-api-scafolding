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
        $thisMonth = "/".date("m-Y")."/";
        $qualifiedBrokerKey = strtolower(explode(" ", $qualifiedBroker->name)[1]);
        if (isset($qualifiedBroker->lead_assigment->$todayDay)) {
            $assignedToday = (int) $qualifiedBroker->lead_assigment->$todayDay;




            $monthCap = (int) $qualifiedBroker->total_leads;

            if (isset($qualifiedBroker->daily_cap)) {
                $dailyCap = (int) $qualifiedBroker->daily_cap;
            } else {
                $dailyCap = ceil($monthCap / 30);
            }





            echo $dailyCap . ">" . $assignedToday . " && " . $monthCap . ">" . $assignedToday;
            echo "<br><br>";


            if ($dailyCap > $assignedToday && $monthCap > $assignedToday) {
                $leadTypeMatched = false;

                foreach ($qualifiedBroker->lead_requests[0] as $key => $leadRequest) {
                    if ($key == $customerFields['loan_purpose'] && $leadRequest->count > 0) {
                        $leadTypeMatched = true;
                    }
                }

                if (!$leadTypeMatched)
                    return false;

                if (isset($qualifiedBroker->preferred_address) && isset($qualifiedBroker->preferred_state)) {
                    if (!is_array($qualifiedBroker->preferred_address)) {
                    if (!empty($qualifiedBroker->preferred_address)) {
                            if (!strpos($customerFields['city'], $qualifiedBroker->preferred_address) || !strpos($customerFields['state'], $qualifiedBroker->preferred_state)) {
                                return false;
                            }
                        } else {
                            if (!strpos($customerFields['state'], $qualifiedBroker->preferred_state)) {
                                return false;
                            }
                        }
                    } else {
                        if (!array_search($customerFields['city'], $qualifiedBroker->preferred_address) || !strpos($customerFields['state'], $qualifiedBroker->preferred_state)) {
                            return false;
                        }
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
            $leadTypeMatched = false;

            foreach ($qualifiedBroker->lead_requests[0] as $key => $leadRequest) {
                if ($key == $customerFields['loan_purpose'] && $leadRequest->count > 0) {
                    $leadTypeMatched = true;
                }
            }


            if (!$leadTypeMatched)
                return false;



            if (isset($qualifiedBroker->preferred_address) && isset($qualifiedBroker->preferred_state)) {
                if (!is_array($qualifiedBroker->preferred_address)) {
                    if (!empty($qualifiedBroker->preferred_address)) {
                        if (!strpos($customerFields['city'], $qualifiedBroker->preferred_address) || !strpos($customerFields['state'], $qualifiedBroker->preferred_state)) {
                            return false;
                        }
                    } else {
                        if (!strpos($customerFields['state'], $qualifiedBroker->preferred_state)) {
                            return false;
                        }
                    }
                } else {
                    if (!array_search($customerFields['city'], $qualifiedBroker->preferred_address) || !strpos($customerFields['state'], $qualifiedBroker->preferred_state)) {
                        return false;
                    }
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
