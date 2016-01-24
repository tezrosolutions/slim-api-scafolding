<?php

$hubspotConfig = array(
    "HUBSPOT_API_KEY" => "6af915fd-806f-483a-b10b-bcb9f94b239d",
    "HUBSPOT_PORTAL_ID" => "695602"
);

$geniusDealStatuses = array(
    '0001' => 'genius_in_progress', //
    '0003' => 'genius_new_enquiry', //
    '0007' => 'genius_declined', //
    '0008' => 'genius_withdrawn', //
    '0009' => 'genius_approved', //
    '0012' => 'genius_application', //
    '0013' => 'genius_no_contact', //
    '0014' => 'genius_waiting_info', //
    '0015' => 'genius_submitted', //
    '0016' => 'genius_appointed', //
    '0017' => 'genius_app_taken', //
    '0018' => 'genius_lost', //
    '0019' => 'genius_call_back', //
    '0025' => 'genius_settled_irregular', //
    '0026' => 'genius_settled', //
    '0027' => 'genius_new_sms', //
    '0028' => 'genius_in_progress_settled',
    '0029' => 'genius_withdrawn_settled',
    '0030' => 'genius_activated', //
    '0031' => 'genius_no_contact_settled',
    '0034' => 'genius_lost_no_contact', //
    '0032' => 'genius_waiting_info_settled',
    '0033' => 'genius_call_back_settled',
);

$geniusHSDealStagesMap = array(
    'genius_new_enquiry' => 'appointmentscheduled',
    'genius_call_back' => 'appointmentscheduled',
    'genius_no_contact' => 'appointmentscheduled',
    'genius_appointed' => 'appointmentscheduled',
    'genius_lost_no_contact' => 'closedlost',
    'genius_withdrawn' => 'closedlost',
    'genius_lost' => 'closedlost',
    'genius_declined' => 'closedlost',
    'genius_app_taken' => 'follow_up',
    'genius_waiting_info' => 'follow_up',
    'genius_application' => 'follow_up',
    'genius_in_progress' => 'follow_up',
    'genius_submitted' => 'qualifiedtobuy',
    'genius_approved' => 'agreements',
    'genius_settled' => 'closedwon',
    'genius_new_sms' => 'closedwon',
    'genius_activated' => 'closedwon',
    'genius_settled_irregular' => 'closedwon'
);


