<?php

/**
 * Created by Muhammad Umair on 9/26/2015 as Contact Space helper
 * */

namespace Custom\Libs;

class Stats {

    public static function sendDailyLeadAssigmentEmail($brokers, $day, $mandrill, $message, $recipient) {
        $brokerSection = "";
        $tmpl = file_get_contents(dirname(__FILE__) . "/../tmpl/email_daily_stats.html");

        foreach ($brokers as $broker) {
            if (!isset($broker->daily_cap)) {
                $broker->daily_cap = ceil($broker->total_leads / 30);
            }

            if ($broker->daily_cap == 0)
                continue;

            $brokerSection .= '<table width="100%" cellspacing="0" cellpadding="0" align="center" class="small_table">
                                                                            <tbody>

                                                                                <!-- spacing -->
                                                                                <tr>
                                                                                    <td width="100%" class="devicewidth" style="
                                                                                        color: #525252;
                                                                                        font-size: 16px;
                                                                                        font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                        line-height: 1.5;
                                                                                        font-weight: normal;
                                                                                        text-align: left;">
                                                                                        ' . $broker->name . '
                                                                                    </td>   
                                                                                </tr>

                                                                                <tr>
                                                                                    <td width="100%" class="devicewidth" style="
                                                                                        color: #525252;
                                                                                        font-size: 14px;
                                                                                        font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                        line-height: 1.5;
                                                                                        font-weight: normal;
                                                                                        text-align: left;">
                                                                                        Daily Cap: ' . $broker->daily_cap . '    
                                                                                    </td>   
                                                                                </tr>

                                                                                <!-- spacing -->
                                                                                <tr>
                                                                                    <td width="100%" height="10" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                                                                                </tr>
                                                                                <!-- spacing -->

                                                                                <tr>
                                                                                    <td>

                                                                                        <table width="100%" style="border-bottom:1px solid #999">
                                                                                            
                                                                                        ';
            if (isset($broker->assigned->$day)) {
                $brokerSection .= '<tr style="background-color:#dfdfdf;">
                                                                                                <th width="8%" style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">S#</th>

                                                                                                <th width="25%" style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">Name</th>

                                                                                                <th width="30%" style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">Email</th>

                                                                                                <th  width="15%" style="color: #525252;
                                                                                                     font-size: 14px;
                                                                                                     font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                     line-height: 1.5;
                                                                                                     font-weight: normal;
                                                                                                     padding:5px;
                                                                                                     text-align: left;">Post Code</th>

                                                                                                <th width="22%" style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">Loan Purpose</th>
                                                                                            </tr>';
                foreach ($broker->assigned->$day as $assignment) {
                    $brokerSection .= '<tr>
                                                                                                <td style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">1</td>

                                                                                                <td style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">' . $assignment->firstname . ' ' . $assignment->lastname . '</td>

                                                                                                <td style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">' . $assignment->email . '</td>

                                                                                                <td style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">' . $assignment->zip . '</td>

                                                                                                <td style="color: #525252;
                                                                                                    font-size: 14px;
                                                                                                    font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                                    line-height: 1.5;
                                                                                                    font-weight: normal;
                                                                                                    padding:5px;
                                                                                                    text-align: left;">' . $assignment->loan_purpose . '</td>
                                                                                            </tr>';
                }
            } else {
                $brokerSection .= '<tr>
                                                                                    <td width="100%" class="devicewidth" style="
                                                                                        color: #525252;
                                                                                        font-size: 14px;
                                                                                        font-family: ProximaNova, arial, sans-serif;                                                                  
                                                                                        line-height: 1.5;
                                                                                        font-weight: normal;
                                                                                        text-align: left;">
                                                                                        No leads assigned.   
                                                                                    </td>   
                                                                                </tr>';
            }

            $brokerSection .= '<table width="100%" cellpadding="0" cellspacing="0" align="center">
                                                                            <tbody>                                                                               
                                                                                <tr>
                                                                                    <td width="100%" height="25" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>';
        }


        $email = str_replace("!brokerSection", $brokerSection, $tmpl);
        $email = str_replace("!Date", date('d-m-Y'), $email);

        
        

        $message->html = $email;
        $message->subject = 'Daily Lead Assignment Report for ' . date('d-m-Y');
        $message->from_email = 'stats@tezrosolutions.com';
        $message->from_name = 'Daily Stats';

        $recipient->email = 'umair655@gmail.com';
        $recipient->name = 'Jane';

        // add the recipient to the message
        $message->addRecipient($recipient);

        // send the message
        $response = $mandrill->messages()->send($message);
        
        echo $email;
    }

}
