<?php

class SyncTest extends LocalWebTestCase {
     /*public function testHello()
      {
      $this->client->get('/hello/William');
      $this->assertEquals(200, $this->client->response->status());
      $this->assertSame('Hello, William', $this->client->response->body());
      }

      public function testDealSynchronize()
      {
      $this->client->post('/deal/synchronize', '{"vid":65604,"canonical-vid":65604,"merged-vids":[],"portal-id":695602,"is-contact":true,"profile-token":"AO_T-mO3U9WAzOPjQY6LPiW3X6F0lZtNwdkUBv-9S0kW3SA98I2ED09NOM8Gh1h4KlNJy0F9Fz7EsMdw17raHYNttfFawB5SBDCHZ2FGuAHTvD1TCikfk027EqCdrrMUToSU0whzWPQ6","profile-url":"https://app.hubspot.com/contacts/695602/lists/public/contact/_AO_T-mO3U9WAzOPjQY6LPiW3X6F0lZtNwdkUBv-9S0kW3SA98I2ED09NOM8Gh1h4KlNJy0F9Fz7EsMdw17raHYNttfFawB5SBDCHZ2FGuAHTvD1TCikfk027EqCdrrMUToSU0whzWPQ6/","properties":{"firstname":{"value":"Testcase","versions":[{"value":"Testcase","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_analytics_last_url":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"num_unique_conversion_events":{"value":"0","versions":[{"value":"0","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":0,"selected":false}]},"hs_analytics_revenue":{"value":"0.0","versions":[{"value":"0.0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"createdate":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]},"hs_social_num_broadcast_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_referrer":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_last_timestamp":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hs_analytics_num_visits":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout_691089":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hs_social_linkedin_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_social_last_engagement":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hubspot_owner_id":{"value":"6168064","versions":[{"value":"6168064","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442412187448,"selected":false}]},"hs_analytics_source":{"value":"OFFLINE","versions":[{"value":"OFFLINE","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_num_page_views":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"email":{"value":"testcase_003@tezrosolutions.com","versions":[{"value":"testcase_003@tezrosolutions.com","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_analytics_first_url":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_visit_timestamp":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_timestamp":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lastmodifieddate":{"value":"1442412223496","versions":[{"value":"1442412223496","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":1442412223496,"selected":false}]},"hs_social_google_plus_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_last_referrer":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":142411863131,"selected":false}]},"hs_lifecyclestage_subscriber_date":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]},"hs_analytics_average_page_views":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lastname":{"value":"003","versions":[{"value":"003","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_social_facebook_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout_691095":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hubspot_owner_assigneddate":{"value":"1442412187448","versions":[{"value":"1442412187448","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442412187448,"selected":false}]},"num_conversion_events":{"value":"0","versions":[{"value":"0","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":0,"selected":false}]},"hs_email_optout_815077":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"currentlyinworkflow":{"value":"true","versions":[{"value":"true","source-type":"WORKFLOWS","source-id":null,"source-label":null,"timestamp":1442412223428,"selected":false}]},"hs_analytics_num_event_completions":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_source_data_2":{"value":"umair@tezrosolutions.com","versions":[{"value":"umair@tezrosolutions.com","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_social_twitter_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_source_data_1":{"value":"CONTACTS_WEB","versions":[{"value":"CONTACTS_WEB","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lifecyclestage":{"value":"subscriber","versions":[{"value":"subscriber","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]}},"form-submissions":[],"list-memberships":[{"static-list-id":19,"internal-list-id":20,"timestamp":1442412237750,"vid":65604,"is-member":true},{"static-list-id":20,"internal-list-id":21,"timestamp":1442412232831,"vid":65604,"is-member":true},{"static-list-id":24,"internal-list-id":25,"timestamp":1442412232623,"vid":65604,"is-member":true},{"static-list-id":31,"internal-list-id":32,"timestamp":1442412200515,"vid":65604,"is-member":true},{"static-list-id":443,"internal-list-id":503,"timestamp":1442412225175,"vid":65604,"is-member":true},{"static-list-id":444,"internal-list-id":504,"timestamp":1442412223172,"vid":65604,"is-member":true},{"static-list-id":447,"internal-list-id":507,"timestamp":1442412222896,"vid":65604,"is-member":true}],"identity-profiles":[{"vid":65604,"is-deleted":false,"is-contact":false,"pointer-vid":0,"previous-vid":0,"linked-vids":[],"saved-at-timestamp":0,"deleted-changed-timestamp":0,"identities":[{"type":"EMAIL","value":"testcase_003@tezrosolutions.com","timestamp":1442411859094,"source":"UNSPECIFIED"},{"type":"LEAD_GUID","value":"944b849c-3aa5-4cf4-b264-2b2de682e0ea","timestamp":1442411859175,"source":"UNSPECIFIED"}]}],"merge-audits":[],"associated-owner":{"first-name":"Umair","last-name":"M","email":"umair@tezrosolutions.com","type":"PERSON"}}');
      $this->assertEquals(200, $this->client->response->status());

      //checking if deal was properly entered
      $this->assertSame('200', $this->client->response->body());
      }

      public function testContactSpaceSynchronize()
      {
      $this->client->post('/contactspace/synchronize', '{"vid":65604,"canonical-vid":65604,"merged-vids":[],"portal-id":695602,"is-contact":true,"profile-token":"AO_T-mO3U9WAzOPjQY6LPiW3X6F0lZtNwdkUBv-9S0kW3SA98I2ED09NOM8Gh1h4KlNJy0F9Fz7EsMdw17raHYNttfFawB5SBDCHZ2FGuAHTvD1TCikfk027EqCdrrMUToSU0whzWPQ6","profile-url":"https://app.hubspot.com/contacts/695602/lists/public/contact/_AO_T-mO3U9WAzOPjQY6LPiW3X6F0lZtNwdkUBv-9S0kW3SA98I2ED09NOM8Gh1h4KlNJy0F9Fz7EsMdw17raHYNttfFawB5SBDCHZ2FGuAHTvD1TCikfk027EqCdrrMUToSU0whzWPQ6/","properties":{"firstname":{"value":"Testcase","versions":[{"value":"Testcase","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_analytics_last_url":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"num_unique_conversion_events":{"value":"0","versions":[{"value":"0","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":0,"selected":false}]},"hs_analytics_revenue":{"value":"0.0","versions":[{"value":"0.0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"createdate":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]},"hs_social_num_broadcast_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_referrer":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_last_timestamp":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hs_analytics_num_visits":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout_691089":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hs_social_linkedin_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_social_last_engagement":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hubspot_owner_id":{"value":"6168064","versions":[{"value":"6168064","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442412187448,"selected":false}]},"hs_analytics_source":{"value":"OFFLINE","versions":[{"value":"OFFLINE","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_num_page_views":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"email":{"value":"testcase_003@tezrosolutions.com","versions":[{"value":"testcase_003@tezrosolutions.com","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_analytics_first_url":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_visit_timestamp":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_timestamp":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lastmodifieddate":{"value":"1442412223496","versions":[{"value":"1442412223496","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":1442412223496,"selected":false}]},"hs_social_google_plus_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_last_referrer":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":142411863131,"selected":false}]},"hs_lifecyclestage_subscriber_date":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]},"hs_analytics_average_page_views":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lastname":{"value":"003","versions":[{"value":"003","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_social_facebook_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout_691095":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hubspot_owner_assigneddate":{"value":"1442412187448","versions":[{"value":"1442412187448","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442412187448,"selected":false}]},"num_conversion_events":{"value":"0","versions":[{"value":"0","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":0,"selected":false}]},"hs_email_optout_815077":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"currentlyinworkflow":{"value":"true","versions":[{"value":"true","source-type":"WORKFLOWS","source-id":null,"source-label":null,"timestamp":1442412223428,"selected":false}]},"hs_analytics_num_event_completions":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_source_data_2":{"value":"umair@tezrosolutions.com","versions":[{"value":"umair@tezrosolutions.com","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_social_twitter_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_source_data_1":{"value":"CONTACTS_WEB","versions":[{"value":"CONTACTS_WEB","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lifecyclestage":{"value":"subscriber","versions":[{"value":"subscriber","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]}},"form-submissions":[],"list-memberships":[{"static-list-id":19,"internal-list-id":20,"timestamp":1442412237750,"vid":65604,"is-member":true},{"static-list-id":20,"internal-list-id":21,"timestamp":1442412232831,"vid":65604,"is-member":true},{"static-list-id":24,"internal-list-id":25,"timestamp":1442412232623,"vid":65604,"is-member":true},{"static-list-id":31,"internal-list-id":32,"timestamp":1442412200515,"vid":65604,"is-member":true},{"static-list-id":443,"internal-list-id":503,"timestamp":1442412225175,"vid":65604,"is-member":true},{"static-list-id":444,"internal-list-id":504,"timestamp":1442412223172,"vid":65604,"is-member":true},{"static-list-id":447,"internal-list-id":507,"timestamp":1442412222896,"vid":65604,"is-member":true}],"identity-profiles":[{"vid":65604,"is-deleted":false,"is-contact":false,"pointer-vid":0,"previous-vid":0,"linked-vids":[],"saved-at-timestamp":0,"deleted-changed-timestamp":0,"identities":[{"type":"EMAIL","value":"testcase_003@tezrosolutions.com","timestamp":1442411859094,"source":"UNSPECIFIED"},{"type":"LEAD_GUID","value":"944b849c-3aa5-4cf4-b264-2b2de682e0ea","timestamp":1442411859175,"source":"UNSPECIFIED"}]}],"merge-audits":[],"associated-owner":{"first-name":"Umair","last-name":"M","email":"umair@tezrosolutions.com","type":"PERSON"}}');
      $this->assertEquals(200, $this->client->response->status());

      //checking if ContactSpace synchronization was done without any problem
      $this->assertSame('200', $this->client->response->body());
      }

      public function testEmailLeads()
      {
      $this->client->post('/emailleads/synchronize', array("username" => "umair@dev.1800approved.com.au", "password" => "U3D*vDfkF(;A", "type" => "test"));
      $this->assertEquals(200, $this->client->response->status());
      }

      public function testGeniusSychronize()
      {
      $this->client->post('/genius/synchronize', '{"vid":65604,"canonical-vid":65604,"merged-vids":[],"portal-id":695602,"is-contact":true,"profile-token":"AO_T-mO3U9WAzOPjQY6LPiW3X6F0lZtNwdkUBv-9S0kW3SA98I2ED09NOM8Gh1h4KlNJy0F9Fz7EsMdw17raHYNttfFawB5SBDCHZ2FGuAHTvD1TCikfk027EqCdrrMUToSU0whzWPQ6","profile-url":"https://app.hubspot.com/contacts/695602/lists/public/contact/_AO_T-mO3U9WAzOPjQY6LPiW3X6F0lZtNwdkUBv-9S0kW3SA98I2ED09NOM8Gh1h4KlNJy0F9Fz7EsMdw17raHYNttfFawB5SBDCHZ2FGuAHTvD1TCikfk027EqCdrrMUToSU0whzWPQ6/","properties":{"firstname":{"value":"Testcase","versions":[{"value":"Testcase","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_analytics_last_url":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"num_unique_conversion_events":{"value":"0","versions":[{"value":"0","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":0,"selected":false}]},"hs_analytics_revenue":{"value":"0.0","versions":[{"value":"0.0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"createdate":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]},"hs_social_num_broadcast_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_referrer":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_last_timestamp":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hs_analytics_num_visits":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout_691089":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hs_social_linkedin_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_social_last_engagement":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hubspot_owner_id":{"value":"6168064","versions":[{"value":"6168064","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442412187448,"selected":false}]},"hs_analytics_source":{"value":"OFFLINE","versions":[{"value":"OFFLINE","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_num_page_views":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"email":{"value":"testcase_003@tezrosolutions.com","versions":[{"value":"testcase_003@tezrosolutions.com","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_analytics_first_url":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_visit_timestamp":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_first_timestamp":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lastmodifieddate":{"value":"1442412223496","versions":[{"value":"1442412223496","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":1442412223496,"selected":false}]},"hs_social_google_plus_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_last_referrer":{"value":"","versions":[{"value":"","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":142411863131,"selected":false}]},"hs_lifecyclestage_subscriber_date":{"value":"1442411859094","versions":[{"value":"1442411859094","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]},"hs_analytics_average_page_views":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lastname":{"value":"003","versions":[{"value":"003","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442411859078,"selected":false}]},"hs_social_facebook_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_email_optout_691095":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"hubspot_owner_assigneddate":{"value":"1442412187448","versions":[{"value":"1442412187448","source-type":"CONTACTS_WEB","source-id":"umair@tezrosolutions.com","source-label":null,"timestamp":1442412187448,"selected":false}]},"num_conversion_events":{"value":"0","versions":[{"value":"0","source-type":"CALCULATED","source-id":null,"source-label":null,"timestamp":0,"selected":false}]},"hs_email_optout_815077":{"value":"","versions":[{"value":"","source-type":"EMAIL","source-id":"Updated in response to an email address change.","source-label":null,"timestamp":1442411859741,"selected":false}]},"currentlyinworkflow":{"value":"true","versions":[{"value":"true","source-type":"WORKFLOWS","source-id":null,"source-label":null,"timestamp":1442412223428,"selected":false}]},"hs_analytics_num_event_completions":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_source_data_2":{"value":"umair@tezrosolutions.com","versions":[{"value":"umair@tezrosolutions.com","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_social_twitter_clicks":{"value":"0","versions":[{"value":"0","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"hs_analytics_source_data_1":{"value":"CONTACTS_WEB","versions":[{"value":"CONTACTS_WEB","source-type":"ANALYTICS","source-id":"ContactAnalyticsDetailsUpdateWorker","source-label":null,"timestamp":1442411863131,"selected":false}]},"lifecyclestage":{"value":"subscriber","versions":[{"value":"subscriber","source-type":"CONTACTS_WEB","source-id":null,"source-label":null,"timestamp":1442411859094,"selected":false}]}},"form-submissions":[],"list-memberships":[{"static-list-id":19,"internal-list-id":20,"timestamp":1442412237750,"vid":65604,"is-member":true},{"static-list-id":20,"internal-list-id":21,"timestamp":1442412232831,"vid":65604,"is-member":true},{"static-list-id":24,"internal-list-id":25,"timestamp":1442412232623,"vid":65604,"is-member":true},{"static-list-id":31,"internal-list-id":32,"timestamp":1442412200515,"vid":65604,"is-member":true},{"static-list-id":443,"internal-list-id":503,"timestamp":1442412225175,"vid":65604,"is-member":true},{"static-list-id":444,"internal-list-id":504,"timestamp":1442412223172,"vid":65604,"is-member":true},{"static-list-id":447,"internal-list-id":507,"timestamp":1442412222896,"vid":65604,"is-member":true}],"identity-profiles":[{"vid":65604,"is-deleted":false,"is-contact":false,"pointer-vid":0,"previous-vid":0,"linked-vids":[],"saved-at-timestamp":0,"deleted-changed-timestamp":0,"identities":[{"type":"EMAIL","value":"testcase_003@tezrosolutions.com","timestamp":1442411859094,"source":"UNSPECIFIED"},{"type":"LEAD_GUID","value":"944b849c-3aa5-4cf4-b264-2b2de682e0ea","timestamp":1442411859175,"source":"UNSPECIFIED"}]}],"merge-audits":[],"associated-owner":{"first-name":"Umair","last-name":"M","email":"umair@tezrosolutions.com","type":"PERSON"}}');
      $this->assertEquals(200, $this->client->response->status());
      } 
*/
    public function testContactSpaceUpdateHubSpot() {
        $this->client->post('/contactspace/updateHubSpot', array("CallID" => "34"), array('SERVER_NAME' => 'local.dev','PHP_AUTH_USER' => 'root','PHP_AUTH_PW' => 'r0Ot_C0n643'));
        $this->assertEquals(200, $this->client->response->status());

        $this->assertEquals("success", $this->client->response->body());
    }

}

/* End of file SyncTest.php */
