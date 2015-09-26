<?php
/**
* Created by Muhammad Umair on 9/15/2015 as HubSpot helper
**/
namespace Custom\Libs;
class HubSpotExt {
	protected $_url = "https://api.hubapi.com";
	public $_apiKey = "6af915fd-806f-483a-b10b-bcb9f94b239d";
	public $_portalID = 695602;
	public $_xmlData;

	public function __init() {

	}

	public function insertDeal($json) {

		$this->postURL = $this->_url.'/deals/v1/deal?hapikey='.$this->_apiKey.'&portalId='.$this->_portalID;
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
}