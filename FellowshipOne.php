<?php 

	require_once('OAuth.php'); 
	
	/**
	 * Simple 2nd party helper for FellowshipOne.com (F1) API. 
	 * @class FellowshipOne
	 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
	 * @copyright 2012 Daniel Boorn
	 * @author Daniel Boorn phpsales@gmail.com
	 *
	 */
	class FellowshipOne{
		
		private $settings;
		private $consumer;
		private $token;
		
		
		public $paths = array(
			'portalUser' => array(
				'accessToken'=>'/v1/PortalUser/AccessToken',
			),
			'giving' => array(
				'accountTypes'=>'/giving/v1/accounts/accounttypes',
				'contributionTypes'=>'/giving/v1/contributiontypes',
				'fundTypes'=>'/giving/v1/funds/fundtypes',
				'funds'=>'/giving/v1/funds',
				'newContributionReceipt'=>'/giving/v1/contributionreceipts/new',
				'createContributionReceipt'=>'/giving/v1/contributionreceipts',
			),
			'people' => array(
				'newHousehold' => '/v1/Households/new',
				'createHousehold' => '/v1/Households',
				'householdMemberTypes' => '/v1/People/HouseholdMemberTypes',
				'householdSearch' => '/v1/Households/Search',
				'peopleSearch' => '/v1/People/Search',
				'newPerson' => '/v1/People/new',
				'createPerson' => '/v1/People',
				'newAddress' => '/v1/People/{personID}/Addresses/new',
				'createAddress' => '/v1/People/{personID}/Addresses',
			),
		);
		
		
		/**
		 * contruct fellowship one class with settings array that contains
		 * @param unknown_type $settings
		 */
		public function __construct($settings){
			$this->settings = (object) $settings;
			$this->consumer = new OAuthConsumer($this->settings->key, $this->settings->secret, NULL);
		}
		
		
		/**
		 * use __get magic method for easy property methods
		 * @param string $property
		 */
		public function __get($property){
			if (method_exists($this, 'get'.ucfirst($property))){
				return call_user_func(array($this, 'get'.ucfirst($property)));
			}		
		}
		
		/**
		 * dump object to output
		 * @param object $object
		 */
		public function debug($object){
			if($this->settings->debug) var_dump($object);
		}
		
		
		/**
		 * fetch contribution receipt model from F1
		 */
		public function getContributionReceiptModel(){
			$url = $this->settings->baseUrl . $this->paths['giving']['newContributionReceipt'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * create new contribution receipt
		 * @param object $model
		 */
		public function createContributionReceipt($model){
			$url = $this->settings->baseUrl . $this->paths['giving']['createContributionReceipt'] . ".json";
			$model = json_encode($model);
			return $this->fetchPostJson($url,$model);
		}
		
		/**
		 * fetch address model from F1
		 * @param int $personId
		 */
		public function getAddressModel($personId){
			$url = str_replace('{personID}',$personId, $this->settings->baseUrl . $this->paths['people']['newAddress'] . ".json");
			var_dump($url);
			return $this->fetchGetJson($url);
		}
		
		/**
		 * create new address record
		 * @param object $model
		 * @param int $personId
		 */
		public function createAddress($model,$personId){
			$url = str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['createAddress'] . ".json");
			$model = json_encode($model);
			return $this->fetchPostJson($url,$model);
		}
		
		/**
		 * fetch person model from F1
		 */
		public function getPersonModel(){
			$url = $this->settings->baseUrl . $this->paths['people']['newPerson'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * create new person record
		 * @param object $model
		 */
		public function createPerson($model){
			$url = $this->settings->baseUrl . $this->paths['people']['createPerson'] . ".json";
			$model = json_encode($model);
			return $this->fetchPostJson($url,$model);
		}
		
		/**
		 * fetch household model from F1
		 */
		public function getHouseholdModel(){
			$url = $this->settings->baseUrl . $this->paths['people']['newHousehold'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * create new household
		 * @param object $model
		 */
		public function createHousehold($model){
			$url = $this->settings->baseUrl . $this->paths['people']['createHousehold'] . ".json";
			$model = json_encode($model);
			return $this->fetchPostJson($url,$model);
		}
		
		/**
		 * fetch people by searching attributes
		 * @param array $attributes
		 */
		public function searchPeople($attributes){
			$url = $this->settings->baseUrl . $this->paths['people']['peopleSearch'] . ".json";
			$url .= "?" . http_build_query($attributes);
			return $this->fetchGetJson($url);	
		}
		
		
		/**
		 * fetch households by name search
		 * @param string $name
		 */
		public function getHouseholdsByName($name){
			$url = $this->settings->baseUrl . $this->paths['people']['householdSearch'] . ".json";
			$url .= "?searchFor=" . urlencode($name);
			return $this->fetchGetJson($url);	
		}
		
		/**
		 * fetch household member types
		 */
		public function getPeopleHouseholdMemberTypes(){
			$url = $this->settings->baseUrl . $this->paths['people']['householdMemberTypes'] . ".json";
			return $this->fetchGetJson($url);
		}
	
		/**
		 * fetch giving funds
		 */
		public function getGivingFunds(){
			$url = $this->settings->baseUrl . $this->paths['giving']['funds'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * fetch giving fund types
		 */
		public function getGivingFundTypes(){
			$url = $this->settings->baseUrl . $this->paths['giving']['fundTypes'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * fetch giving contribution types
		 */
		public function getGivingContributionTypes(){
			$url = $this->settings->baseUrl . $this->paths['giving']['contributionTypes'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * fetch giving account types
		 */
		public function getGivingAccountTypes(){
			$url = $this->settings->baseUrl . $this->paths['giving']['accountTypes'] . ".json";
			return $this->fetchGetJson($url);
		}
		
		/**
		 * fetches GET JSON request on F1, parses and returns response
		 * @param string $url
		 * @param array $params (assocate array)
		 */
		public function fetchGetJson($url,$params = NULL){
			$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $url, $params);
			$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, $this->token);
			$response = $this->sendRequest($request->get_normalized_http_method(), $url, $request->to_header());
			return $this->parseResponseJson($response);
		}
		
		/**
		 * fetches POST JSON request on F1, parses and returns response
		 * @param string $url
		 * @param array $params (assocate array)
		 */
		public function fetchPostJson($url,$data = NULL){
			$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'POST', $url, NULL);
			$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, $this->token);
			$response = $this->sendRequest($request->get_normalized_http_method(), $url, $request->to_header(),$data);
			return $this->parseResponseJson($response);
		}
		
		/**
		 * login to F1 system using 2nd party username/password
		 */
		public function login(){
			$this->r = $this->requestAccessToken();
			if(!$this->r->oauth_token || !$this->r->oauth_token_secret){
				return false;
			}
			$this->token->key = $this->r->oauth_token;
			$this->token->secret = $this->r->oauth_token_secret;
			return true;
		}
		
		/**
		 * request 2nd party access token from f1
		 * @return stdClass or null
		 */
		public function requestAccessToken(){
			
			$message = urlencode(base64_encode("{$this->settings->username} {$this->settings->password}"));
			$url = $this->settings->baseUrl . $this->paths['portalUser']['accessToken'];
			$params = array("ec"=>$message);  
			$request = OAuthRequest::from_consumer_and_token($this->consumer, NULL, 'POST', $url, $params);
			$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, NULL);
			
			$url = $url . '?' . $this->implodeAssoc('=', '&', $params);
			$response = $this->sendRequest($request->get_normalized_http_method(), $url, $request->to_header());
			return $this->parseResponse($response);
			
		}
		
		/**
		 * parse response body and returned body params or null
		 * @param string $response
		 * @return stdClass $vars
		 */
		private function parseResponse($response){
			$parts = explode("\r\n\r\n", $response);
			$vars = null;
			if(is_array($parts) && sizeof($parts)>1){
				parse_str($parts[sizeof($parts)-1],$vars);
			}
			$this->debug($response);
			$this->debug($parts);
			return (object) $vars;	
		}
		
		/**
		 * parse response body and return body json or null
		 * @param string $response
		 * @return stdClass $vars
		 */
		private function parseResponseJson($response){
			$parts = explode("\r\n\r\n", $response);
			$this->debug($response);
			$this->debug($parts);
			$vars = null;
			if(is_array($parts) && sizeof($parts)>1){
				$vars = json_decode($parts[sizeof($parts)-1],true);
			}
			return $vars;	
		}
		
		
		/**
		 * Send request to API
		 * @author http://gdatatips.blogspot.com/2008/11/2-legged-oauth-in-php.html
		 * @param string $http_method
		 * @param string $url
		 * @param string $auth_header
		 * @param array $postData
		 * @return string $response
		 */
		private function sendRequest($http_method, $url, $auth_header=null, $postData=null) {  
		  $this->debug($url);
		  $this->debug($auth_header);
		  $this->debug($postData);
		  
			$curl = curl_init($url);  
		  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
		  curl_setopt($curl, CURLOPT_FAILONERROR, false);  
		  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
		  curl_setopt($curl, CURLOPT_HEADER, true);
		  
		  switch($http_method) {  
		    case 'GET':  
		      if ($auth_header) {  
		        curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));   
		      }  
		      break;  
		    case 'POST':  
		      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',   
		                                                   $auth_header));   
		      curl_setopt($curl, CURLOPT_POST, 1);                                         
		      curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);  
		      break;  
		    case 'PUT':  
		      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',   
		                                                   $auth_header));   
		      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);  
		      curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);  
		      break;  
		    case 'DELETE':  
		      curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));   
		      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);   
		      break;  
		  }  
		  $response = curl_exec($curl);  
		  if (!$response) {  
		    $response = curl_error($curl);  
		  }  
		  curl_close($curl);  
		  return $response;  
		}  
		  
		/** 
		 * Joins key:value pairs by inner_glue and each pair together by outer_glue
		 * @author http://gdatatips.blogspot.com/2008/11/2-legged-oauth-in-php.html 
		 * @param string $inner_glue The HTTP method (GET, POST, PUT, DELETE) 
		 * @param string $outer_glue Full URL of the resource to access 
		 * @param array $array Associative array of query parameters 
		 * @return string Urlencoded string of query parameters 
		 */  
		private function implodeAssoc($inner_glue, $outer_glue, $array) {  
		  $output = array();  
		  foreach($array as $key => $item) {  
		    $output[] = $key . $inner_glue . urlencode($item);  
		  }  
		  return implode($outer_glue, $output);  
		} 		
		
	}


?>