<?php

/**
 * Created by Muhammad Umair on 9/26/2015 as Contact Space helper
 * */

namespace Custom\Libs;

class Firebase {

    public static function sortByTotalAmount($x, $y) {
        return $y->total_budget - $x->total_budget;
    }

    public static function rollOverDailyCap($app, $yesterday, $qualifiedBroker, $firebase, $thisMonth, $qualifiedBrokerKey, $today, $newMonth = false) {
        $rollOvers = 0;

        $monthCap = (int) $qualifiedBroker->total_leads;
        if (isset($qualifiedBroker->daily_cap)) {
            $dailyCap = (int) $qualifiedBroker->daily_cap;
        } else {
            $dailyCap = ceil($monthCap / 30);
        }

        if ($newMonth) {
            $lastMonth = "/" . date("m-Y") . "/";
            $qualifiedBroker = json_decode($firebase->get($lastMonth . $qualifiedBrokerKey));
        }

        if (isset($qualifiedBroker->assigned->$yesterday)) {
            $assigned = count($qualifiedBroker->assigned->$yesterday);

            if (isset($qualifiedBroker->active_roll_overs)) {
                $dailyCap += $qualifiedBroker->active_roll_overs;
            }

            $rollOvers = $dailyCap - $assigned;
        } else {
            if (isset($qualifiedBroker->active_roll_overs)) {
                $dailyCap += $qualifiedBroker->active_roll_overs;
            }

            $rollOvers = $dailyCap;
        }
        $firebase->set($thisMonth . $qualifiedBrokerKey . "/active_roll_overs/", $rollOvers);
        $firebase->set($thisMonth . $qualifiedBrokerKey . "/roll_overs/" . $today, $rollOvers);
        
        $app->log->debug('[' . date('H:i:s', time()) . '] RollOvers for ' . $qualifiedBroker->name . ': ' . $rollOvers);
    }

    public static function assignLeadToBroker($app, $qualifiedBroker, $firebase, $hubspot, $customerFields) {

        $thisMonth = "/" . date("m-Y") . "/";
        
        if (date("t") == date("d"))
            $thisMonth = "/" . date("m-Y", strtotime("+1 days")) . "/";//increase month as well


        $qualifiedBrokerKey = strtolower(explode(" ", $qualifiedBroker->name)[1]);

        $appConfig = $app->config('custom');

        if (date('H') >= $appConfig['firebase']['config']['daily_reset_hour']) {
            $todayDay = date("d-m", strtotime("+1 days"));

            //making sure rollover are added
            $yesterday = date("d-m");
            if (!isset($qualifiedBroker->roll_overs->$todayDay)) {
                if (date("t") == date("d")) {
                    Firebase::rollOverDailyCap($app, $yesterday, $qualifiedBroker, $firebase, $thisMonth, $qualifiedBrokerKey, $todayDay, true);
                } else {
                    Firebase::rollOverDailyCap($app, $yesterday, $qualifiedBroker, $firebase, $thisMonth, $qualifiedBrokerKey, $todayDay, false);
                }
            }
        } else {
            $todayDay = date("d-m");
        }



        if (isset($qualifiedBroker->assigned->$todayDay)) {
            $assignedToday = count($qualifiedBroker->assigned->$todayDay);



            $monthCap = (int) $qualifiedBroker->total_leads;

            if (isset($qualifiedBroker->daily_cap)) {
                $dailyCap = (int) $qualifiedBroker->daily_cap;
            } else {
                $dailyCap = ceil($monthCap / 30);
            }

            if (isset($qualifiedBroker->active_roll_overs)) {
                $dailyCap += $qualifiedBroker->active_roll_overs;
            }


            //@TODO Assignment should take in account total monthly assigments
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
                $customerFields["broker_email"] = $qualifiedBroker->email;
                $firebase->set($thisMonth . $qualifiedBrokerKey . "/assigned/" . $todayDay . "/" . $assignedToday, $customerFields);
            } else {
                return false;
            }
        } else {
            if (isset($qualifiedBroker->daily_cap)) {//incase if dailycap is set to zero
                if ($qualifiedBroker->daily_cap == 0)
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

            $customerFields["broker_email"] = $qualifiedBroker->email;
            $firebase->set($thisMonth . $qualifiedBrokerKey . "/assigned/" . $todayDay . "/0", $customerFields);
        }


        $app->log->debug('[' . date('H:i:s', time()) . '] Date assgined for: ' . $todayDay);

        return true;
    }

}
