<?php 

	
	/**
	 * Helper Class for the FellowshipOne.com API
	 * @class FellowshipOne
	 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
	 * @copyright 2012 Daniel Boorn
	 * @author Daniel Boorn daniel.boorn@gmail.com
	 * @requires PHP PECL OAuth, http://php.net/oauth
	 *
	 */
	class FellowshipOne{

		const TOKEN_CACHE_FILE = 0;
		const TOKEN_CACHE_SESSION = 1;
		const TOKEN_CACHE_CUSTOM = 2;
		
		private $settings;
		
		
		public $paths = array(
			'tokenCache'=> 'tokens/',//file path to local folder
			'general' => array(
				'requestToken'=>'/v1/Tokens/RequestToken',
				'accessToken'=>'/v1/Tokens/AccessToken',
			),
			'portalUser' => array(
				'userAuthorization'=>'/v1/PortalUser/Login',
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
		}
		
		
		/**
		 * use __get magic method for easy property methods
		 * @param string $property
		 */
		public function __get($property){
			$property = 'get'.ucfirst($property);
			if (method_exists($this, $property)){
				return call_user_func(array($this, $property));
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
		 * BEGIN: F1 API Resource Functions
		 */
		
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
		 * BEGIN: OAuth Functions
		 */
		
		/**
		 * directly set access token. e.g. 1st party token based authentication
		 * @param array $token
		 */
		public function setAccessToken($token){
			$this->accessToken = (object) $token;
		}
		
		/**
		 * fetches GET JSON request on F1, parses and returns response
		 * @param string $url
		 * @param array $params (assocate array)
		 */
		public function fetchGetJson($url,$params = NULL){
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				if($o->fetch($url,$params)){
					return json_decode($o->getLastResponse(),true);
				}
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
			
		}
		
		/**
		 * fetches POST JSON request on F1, parses and returns response
		 * @param string $url
		 * @param string $data (json data string)
		 */
		public function fetchPostJson($url,$data = NULL){
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				$headers = array(
					'Content-Type' => 'application/json',		
				);
				if($o->fetch($url, $data, OAUTH_HTTP_METHOD_POST, $headers)){
					return json_decode($o->getLastResponse(),true);
				}
			}catch(OAuthException $e){
				var_dump($url,$data);
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
		
		/**
		 * get access token file name from username
		 * @param string $username
		 * @return string
		 */
		protected function getAccessTokenFileName($username){
			$hash = md5($username);
			return "{$this->paths['tokenCache']}.f1_{$hash}.accesstoken";
		}
		
		/**
		 * get access token from file by username
		 * @param string $username
		 * @return array|NULL
		 */
		protected function getFileAccessToken($username){
			$fileName = $this->getAccessTokenFileName($username);
			if(file_exists($fileName)){
				return json_decode(file_get_contents($fileName));
			}
			return null;
		}
		
		
		/**
		 * get access token from session by username
		 * @param string $username
		 * @return array|NULL
		 */
		protected function getSessionAccessToken($username){
			if(isset($_SESSION['F1AccessToken'])){
				//be sure to return object with "oauth_token" and "oauth_token_secret" properties
				return (object) $_SESSION['F1AccessToken'];
			}
			return null;
		}
		
		/**
		 * get cached access token by username
		 * @param string $username
		 * @param const $cacheType
		 * @return array|NULL
		 */
		protected function getAccessToken($username,$cacheType,$custoHandlers){
			switch($cacheType){
				case self::TOKEN_CACHE_FILE:
					$token = $this->getFileAccessToken($username);
					break;
				case self::TOKEN_CACHE_SESSION:
					$token = $this->getSessionAccessToken($username);
					break;
				case self::TOKEN_CACHE_CUSTOM:
					if($username){
						$token = call_user_func($custoHandlers['getAccessToken'],$username);
					}else{
						$token = call_user_func($custoHandlers['getAccessToken']);
					}
			}
			if($token) return $token;
		}
		
		/**
		 * save access token to file by username
		 * @param string $username
		 * @param array $token
		 */
		protected function saveFileAccessToken($username,$token){
			$fileName = $this->getAccessTokenFileName($username);
			file_put_contents($fileName,json_encode($token));
		}
		
		/**
		 * save access token to session
		 * @param array $token
		 */
		protected function saveSessionAccessToken($token){
			$_SESSION['F1AccessToken'] = (object) $token;
		}
		
		/**
		 * save access token by session or file
		 * @param string $username
		 * @param array $token
		 * @param const $cacheType
		 */
		protected function saveAccessToken($username,$token,$cacheType,$custoHandlers){
			
			switch($cacheType){
				case self::TOKEN_CACHE_FILE:
					$this->saveFileAccessToken($username,$token);
					break;
				case self::TOKEN_CACHE_SESSION:
					$this->saveSessionAccessToken($token);
					break;
				case self::TOKEN_CACHE_CUSTOM:
					if($username){
						call_user_func($custoHandlers['getAccessToken'],$username,$token);
					}else{
						call_user_func($custoHandlers['getAccessToken'],$token);
					}
			}
		}
		
		/**
		 * 2nd Party credentials based authentication
		 * @param string $username
		 * @param string $password
		 * @param const $cacheType
		 * @return boolean
		 */
		public function login2ndParty($username,$password,$cacheType=self::TOKEN_CACHE_SESSION,$custoHandlers=NULL){
			$token = $this->getAccessToken($username,$cacheType,$custoHandlers);
			
			$this->debug($token);
			
			if(!$token){
				$token = $this->obtainCredentialsBasedAccessToken($username,$password);
				$this->saveAccessToken($username,$token,$cacheType,$custoHandlers);
			}
			
			
			$this->accessToken = $token;
			
			return true;
		
		}

		/**
		 * obtain credentials based access token from API
		 * @param string $username
		 * @param string $password
		 * @return array
		 */
		protected function obtainCredentialsBasedAccessToken($username,$password){
			try{
				$message = urlencode(base64_encode("{$username} {$password}"));
				$url = "{$this->settings->baseUrl}{$this->paths['portalUser']['accessToken']}?ec={$message}";
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
		/**
		 * BEGIN: 3rd Party OAuth Based Authentication Functions
		 */
		
		/**
		 * obtain request token from API
		 * @return object token
		 */
		protected function obtainRequestToken(){
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$url = "{$this->settings->baseUrl}{$this->paths['general']['requestToken']}";
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
		/**
		 * redirect user for 3rd party authorization with callback url
		 * @param object $token
		 * @param string $callbackUrl
		 */
		protected function redirectUserAuthorization($token,$callbackUrl){
			try{
				$_SESSION['requestToken'] = $token;
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($token->oauth_token, $this->oauth_token_secret);
				$url = "{$this->settings->baseUrl}{$this->paths['portalUser']['userAuthorization']}?oauth_token={$token->oauth_token}&oauth_callback={$callbackUrl}";
				@header("Location:{$url}");
				die("<script>window.location='{$url}'</script><meta http-equiv='refresh' content='0;URL=\"{$url}\"'>");//backup redirect
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
		/**
		 * obtain user authoration access token from API
		 * @throws Exception
		 * @return object token
		 */
		protected function obtainUserAuthorationAccessToken(){
			$requestToken = $_SESSION['requestToken'];
			
			if($requestToken->oauth_token != $_GET['oauth_token']){
				throw new Exception('Returned OAuth Token Doesn Not Match Request Token');
			}
			
			try{
				$url = "{$this->settings->baseUrl}{$this->paths['general']['accessToken']}";
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($requestToken->oauth_token, $requestToken->oauth_token_secret);
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
		/**
		 * login 3rd party oauth based authentication
		 * @param string $callbackUrl
		 * @param const $cacheType
		 * @param array $custoHandlers
		 * @return boolean
		 */
		public function login3rdParty($callbackUrl,$cacheType=self::TOKEN_CACHE_SESSION,$custoHandlers=NULL){
			
			if($cacheType==self::TOKEN_CACHE_FILE){
				throw Exception("Cache Type: " . self::TOKEN_CACHE_FILE . " is not supported on 3rd party. Use Session or Custom");
			}

			//fetch cached token (if any)
			$token = $this->getAccessToken(NULL,$cacheType,$custoHandlers);
			if($token){
				$this->accessToken = $token;
				return true;
			}
			
			//else handle callback (if any)
			if(isset($_GET['oauth_token'])){
				$token = $this->obtainUserAuthorationAccessToken();
				$this->saveAccessToken(NULL,$token,$cacheType,$custoHandlers);
				$this->accessToken = $token;
				return true;
			}else{//else start user authorization
				$token = $this->obtainRequestToken();
				$this->redirectUserAuthorization($token,$callbackUrl);
			}
				
		}
		
	}

	