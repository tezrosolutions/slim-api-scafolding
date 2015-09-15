<?php
/**
* Created by Muhammad Umair on 9/15/2015 as Contact Space helper
**/
namespace Custom\Libs;
class ContactSpace {
	protected $_url = "https://apidev.contactspace.com";
	public $_apiKey = "uKNcpIk930F55kw4E3VxUnbWG0NLk4oX";
	public $_datasetID = 171;
	public $_xmlData;

	public function __init() {

	}

	public function insertRecord($xml) {

		$this->postURL = $this->_url.'/?apikey='.$this->_apiKey.'&function=InsertRecord&module=data&datasetid='.$this->_datasetID.'&xmldata='.$xml;
  		$ch = curl_init();
  		curl_setopt($ch, CURLOPT_URL, $this->postURL);
  		curl_setopt($ch, CURLOPT_HEADER, 1);
  		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
  		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
  		curl_setopt($ch, CURLOPT_POST, 1);
  		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  		$result = curl_exec($ch);
  		$info = curl_getinfo($ch);
  		curl_close($ch);
  		return $info;
	}
}