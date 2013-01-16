<?php 

	
	/**
	 * Helper Class for the FellowshipOne.com API.
	 * @class FellowshipOne
	 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
	 * @copyright 2012 Daniel Boorn
	 * @author Daniel Boorn daniel.boorn@gmail.com
	 * @requires (now optional) PHP PECL OAuth, http://php.net/oauth, packaged with OAuth Adapter when PHP PECL OAuth is not present. PECL OAuth is STRONGLY Recommended for Modularity.
	 *
	 */
	class FellowshipOne{

		const TOKEN_CACHE_FILE = 0;
		const TOKEN_CACHE_SESSION = 1;
		const TOKEN_CACHE_CUSTOM = 2;
		
		private $settings;
		
		public $paths = array(
			'tokenCache'=> 'tokens/',
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
				'contentType' => 'application/vnd.fellowshiponeapi.com.people.people.v2+json',
				'newHousehold' => '/v1/Households/new',
				'createHousehold' => '/v1/Households',
				'householdMemberTypes' => '/v1/People/HouseholdMemberTypes',
				'householdSearch' => '/v1/Households/Search',
				'peopleSearch' => '/v1/People/Search',
				'newPerson' => '/v1/People/new',
				'createPerson' => '/v1/People',
				'editPerson' => '/v1/People/{personID}/edit',
				'updatePerson' =>'/v1/People/{personID}',
				'newAddress' => '/v1/People/{personID}/Addresses/new',
				'createAddress' => '/v1/People/{personID}/Addresses',
				'deleteAddress' => '/v1/People/{personID}/Addresses/{addressID}',
				'attributeGroups' => '/v1/People/AttributeGroups',
				'attributes' => '/v1/People/{personID}/Attributes',
				'newAttribute' => '/v1/People/{personID}/Attributes/new',
				'createAttribute' => '/v1/People/{personID}/Attributes',
				'editAttribute' => '/v1/People/{personID}/Attributes/{attributeID}/edit',
				'updateAttribute' => '/v1/People/{personID}/Attributes/{attributeID}',
				'deleteAttribute' => '/v1/People/{personID}/Attributes/{attributeID}',
				'newCommunication' => '/v1/People/{personID}/Communications/New',
				'createCommunication' => '/v1/People/{personID}/Communications',
				'deleteCommunication' => '/v1/People/{personID}/Communications/{communicationID}',
				'statuses' => '/v1/People/Statuses',
			),
			'addresses' => array(
				'addressTypes' => '/v1/Addresses/AddressTypes',	
			),
			'communications' => array(
				'communicationTypes' => '/v1/Communications/CommunicationTypes',
			),
			'requirements' => array(
				'requirementStatuses' => '/v1/requirements/requirementStatuses',
				'backgroundCheckStatuses' => '/v1/requirements/backgroundCheckStatuses',	
				'peopleRequirements' => '/v1/people/{personID}/requirements',
			),
		);
		
		public $timezone = "-0600";//timezone of f1 api
		
		/**
		 * contruct fellowship one class with settings array that contains
		 * @param unknown_type $settings
		 */
		public function __construct($settings){
			$this->settings = (object) $settings;
			$this->checkOAuth();
		}
		
		protected function checkOAuth(){
			if(!class_exists('OAuth')){
				require('OAuthClient.php');
				require('OAuth.php');
			}
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
		 * get timestamp ajusted to with timezone offset of api (currently f1 does not include timezone in date/time)
		 * @param string $datetime
		 */
		public function getAdjustedTimestamp($datetime){
			return strtotime("{$datetime} {$this->timezone}");
		}
		
		/**
		 * fetch contribution receipt model from F1
		 */
		public function getContributionReceiptModel(){
			$url = $this->settings->baseUrl . $this->paths['giving']['newContributionReceipt'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * create new contribution receipt
		 * @param object $model
		 */
		public function createContributionReceipt($model){
			$url = $this->settings->baseUrl . $this->paths['giving']['createContributionReceipt'] . ".json";
			$model = json_encode($model);
			return $this->fetchJson($url,$model);
		}
		
		/**
		 * fetch address model from F1
		 * @param int $personId
		 */
		public function getAddressModel($personId){
			$url = str_replace('{personID}',$personId, $this->settings->baseUrl . $this->paths['people']['newAddress'] . ".json");
			return $this->fetchJson($url);
		}
		
		/**
		 * create new address record
		 * @param object $model
		 * @param int $personId
		 */
		public function createAddress($model,$personId){
			$url = str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['createAddress'] . ".json");
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST);
		}
		
		/**
		 * delete address record
		 * @param int $personId
		 * @param int $addressId
		 */
		public function deleteAddress($personId,$addressId){
			$url = str_replace("{addressID}",$addressId,str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['deleteAddress'] . ".json"));
			$this->fetchJson($url,null,OAUTH_HTTP_METHOD_DELETE);
		}
		
		/**
		 * fetch person model from F1
		 */
		public function getPersonModel(){
			$url = $this->settings->baseUrl . $this->paths['people']['newPerson'] . ".json";
			return $this->fetchJson($url,null,OAUTH_HTTP_METHOD_GET,$this->paths['people']['contentType']);
		}
		
		/**
		 * create new person record
		 * @param object $model
		 */
		public function createPerson($model){
			$url = $this->settings->baseUrl . $this->paths['people']['createPerson'] . ".json";
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST,$this->paths['people']['contentType']);
		}
		
		
		/**
		 * upate person record
		 * @param object $model
		 */
		public function updatePerson($model){
			$url = str_replace("{personID}", $model['person']['@id'], $this->settings->baseUrl . $this->paths['people']['updatePerson'] . ".json");
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_PUT,$this->paths['people']['contentType']);
		}
		
		/**
		 * fetch existing person model for editing
		 * @param number $personId
		 */
		public function editPerson($personId){
			$url = str_replace("{personID}",$personId,$this->settings->baseUrl . $this->paths['people']['editPerson'] . ".json");
			return $this->fetchJson($url,null,OAUTH_HTTP_METHOD_GET,$this->paths['people']['contentType']);
		}
		
		/**
		 * fetch attributes for a person 
		 */
		public function getPeopleAttributes($personId){
			$url = $this->settings->baseUrl . str_replace("{personID}",$personId,$this->paths['people']['attributes'].".json");
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch people attribute model from f1
		 * @param int $personId
		 */
		public function getPeopleAttributeModel($personId){
			$url = str_replace('{personID}',$personId, $this->settings->baseUrl . $this->paths['people']['newAttribute'] . ".json");
			return $this->fetchJson($url);
		}
		
		/**
		 * create new people attribute model
		 * @param int $personId
		 * @param object $model
		 */
		public function createPeopleAttribute($personId,$model){
			$url = str_replace("{personID}",$personId,$this->settings->baseUrl . $this->paths['people']['createAttribute'] . ".json");
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST);
		}
		
		/**
		 * fetch edit model of people attribute
		 */
		public function editPeopleAttribute($personId,$attributeId){
			$url = str_replace("{personID}",$personId,str_replace("{attributeID}",$attributeId,$this->settings->baseUrl . $this->paths['people']['editAttribute'] . ".json"));
			return $this->fetchJson($url,null,OAUTH_HTTP_METHOD_GET);
		}


		/**
		 * update people attribute
		 */
		public function updatePeopleAttribute($personId,$model){
			$url = str_replace("{personID}",$personId,str_replace("{attributeID}",$model['attribute']['@id'],$this->settings->baseUrl . $this->paths['people']['updateAttribute'] . ".json"));
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_PUT);
		}
		
		/**
		 * delete people attribute
		 * @param int $personId
		 * @param int $attributeId
		 */
		public function deletePeopleAttribute($personId,$attributeId){
			$url = str_replace("{attributeID}",$attributeId,str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['deleteAttribute'] . ".json"));
			$this->fetchJson($url,null,OAUTH_HTTP_METHOD_DELETE);
		}
		
		
		/**
		 * fetch attribute groups for people
		 */
		public function getPeopleAttributeGroups(){
			$url = $this->settings->baseUrl . $this->paths['people']['attributeGroups'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch people statuses from F1
		 */
		public function getPeopleStatuses(){
			$url = $this->settings->baseUrl . $this->paths['people']['statuses'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * get people status by name
		 * @param string $name
		 * @param array|null $statuses
		 */
		public function getPeopleStatusByName($name,$statuses=null){
			$r = $statuses ? $statuses : $this->peopleStatuses;
			foreach($r['statuses']['status'] as $status){
				if(strtolower($status['name']) == strtolower($name)){
					return $status;
				}
			}
			return false;
		}
		
		/**
		 * get attribute by name
		 * @param string $name
		 * @param object|null $attributes
		 */
		public function getAttributeByName($name,$attributes=null){
			$r = $attributes ? $attributes : $this->peopleAttributeGroups;
			foreach($r['attributeGroups']['attributeGroup'] as $group){
				if(isset($group['attribute']) && gettype($group['attribute']) == "array"){
					foreach($group['attribute'] as $attribute){
						if(strtolower($attribute['name']) == strtolower($name)){
							return $attribute;
						}
					}
				}
			}
			return null;
		}
		
		/**
		 * checks f1 person for attribute by attribute name (requires attributes in f1 person object)
		 * @param array $f1Person
		 * @param string $attibuteName
		 * @return string|boolean
		 */
		public function personHasAttribute($f1Person,$attributeName){
			if($f1Person['attributes']){
				foreach($f1Person['attributes']['attribute'] as $attribute){
					if(strtolower($attribute['attributeGroup']['attribute']['name']) == strtolower($attributeName)){
						return $attribute;
					}
				}
			}
			return false;
		}
		
		/**
		 * checks f1 person communications for provided email address (requires communications in f1 person object)
		 * @param array $f1Person
		 * @param string $email
		 * @return boolean
		 */
		public function personHasEmail($f1Person,$email){
			if($f1Person['communications']){
				foreach($f1Person['communications']['communication'] as $item){
					if($item['communicationGeneralType']!="Email") continue;
					if(strtolower($item['communicationValue'])==strtolower($email)){
						return true;
					}
				}
			}
			return false;
		}
		
		/**
		 * get attribute group by attribute name
		 * @param string $name
		 * $param object|null $attributes
		 */
		public function getAttributeGroupByName($name,$attributes=null){
			$r = $attributes ? $attributes : $this->peopleAttributeGroups;
			foreach($r['attributeGroups']['attributeGroup'] as $group){
				if(isset($group['attribute']) && gettype($group['attribute']) == "array"){
					foreach($group['attribute'] as $attribute){
						if(strtolower($attribute['name']) == strtolower($name)){
							return $group;
						}
					}
				}
			}
			return null;
		}
		
		/**
		 * fetch household model from F1
		 */
		public function getHouseholdModel(){
			$url = $this->settings->baseUrl . $this->paths['people']['newHousehold'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * create new household
		 * @param object $model
		 */
		public function createHousehold($model){
			$url = $this->settings->baseUrl . $this->paths['people']['createHousehold'] . ".json";
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST);
		}
		
		/**
		 * fetch people by searching attributes
		 * @param array $attributes
		 */
		public function searchPeople($attributes){
			$url = $this->settings->baseUrl . $this->paths['people']['peopleSearch'] . ".json";
			$url .= "?" . http_build_query($attributes);
			return $this->fetchJson($url,null,OAUTH_HTTP_METHOD_GET,$this->paths['people']['contentType']);	
		}
		
		
		/**
		 * fetch households by name search
		 * @param string $name
		 */
		public function getHouseholdsByName($name){
			$url = $this->settings->baseUrl . $this->paths['people']['householdSearch'] . ".json";
			$url .= "?searchFor=" . urlencode($name);
			return $this->fetchJson($url);	
		}
		
		/**
		 * fetch households by searching attributes
		 * @param array $attributes
		 */
		public function searchHouseholds($attributes){
			$url = $this->settings->baseUrl . $this->paths['people']['householdSearch'] . ".json";
			$url .= "?" . http_build_query($attributes);
			return $this->fetchJson($url,null,OAUTH_HTTP_METHOD_GET);
		}
		
		/**
		 * fetch household member types
		 */
		public function getPeopleHouseholdMemberTypes(){
			$url = $this->settings->baseUrl . $this->paths['people']['householdMemberTypes'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch address types
		 */
		public function getAddressTypes(){
			$url = $this->settings->baseUrl . $this->paths['addresses']['addressTypes'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch address type by attributes
		 * @param array $attributes
		 * @param array|null $types
		 */
		public function getAddressTypeByAttribute($attributes,$types=null){
			if(!$types) $types = $this->addressTypes;
			foreach($types['addressTypes']['addressType'] as $type){
				if(isset($type['@array'])) unset($type['@array']);
				$match = true;
				foreach($attributes as $key=>$value){
					if(isset($type[$key]) && $type[$key]==$value) continue;
					$match = false;
				}
				if($match) return $type;
			}
			return null;
		}
		
		/**
		 * fetch people communications model from F1
		 * @param int $personId
		 */
		public function getPeopleCommunicationModel($personId){
			$url = str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['newCommunication'] . ".json");
			return $this->fetchJson($url);
		}
		
		/**
		 * create new people communication record
		 * @param object $model
		 * @param int $personId
		 */
		public function createPeopleCommunication($model,$personId){
			$url = str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['createCommunication'] . ".json");
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST);
		}
		
		/**
		 * delete people communication record
		 * @param int $personId
		 * @param int $communicationId
		 */
		public function deletePeopleCommunication($personId,$communicationId){
			$url = str_replace("{communicationID}",$communicationId,str_replace('{personID}',$personId,$this->settings->baseUrl . $this->paths['people']['deleteCommunication'] . ".json"));
			$this->fetchJson($url,null,OAUTH_HTTP_METHOD_DELETE);
		}
		
		/**
		 * fetch communication types
		 */
		public function getCommunicationTypes(){
			$url = $this->settings->baseUrl . $this->paths['communications']['communicationTypes'] . ".json";
			return $this->fetchJson($url);
		}
		
		
		/**
		 * fetch communication types by attributes
		 * @param array $attributes
		 * @param array|null $types
		 */
		public function getCommunicationTypesByAttribute($attributes,$types=null){
			if(!$types) $types = $this->communicationTypes;
			foreach($types['communicationTypes']['communicationType'] as $type){
				if(isset($type['@array'])) unset($type['@array']);
				if(isset($type['@generalType'])) unset($type['@generalType']);
				$match = true;
				foreach($attributes as $key=>$value){
					if(isset($type[$key]) && $type[$key]==$value) continue;
					$match = false;
				}
				if($match) return $type;
			}
			return null;
		}
		
		/**
		 * fetch background check statuses
		 */
		public function getBackgroundCheckStatuses(){
			$url = $this->settings->baseUrl . $this->paths['requirements']['backgroundCheckStatuses'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch background check status by name
		 */
		public function getBackgroundCheckStatusByName($name){
			$statuses = $this->backgroundCheckStatuses;
			foreach($statuses['backgroundCheckStatuses']['backgroundCheckStatus'] as $status){
				if(strtolower($status['name']) == strtolower($name)){
					return $status;
				}
			}
			return null;
		}
		
		/**
		 * fetch people requirements
		 */
		public function getPeopleRequirements($personId){
			$url = $this->settings->baseUrl . str_replace("{personID}",$personId,$this->paths['requirements']['peopleRequirements'] . ".json");
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch requirement statuses
		 */
		public function getRequirementStatuses(){
			$url = $this->settings->baseUrl . $this->paths['requirements']['requirementStatuses'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetches requirement status by name
		 */
		public function getRequirementStatusByName($name){
			$statuses = $this->requirementStatuses;
			foreach($statuses['requirementStatuses']['requirementStatus'] as $status){
				if(strtolower($status['name'])==strtolower($name)){
					return $status;
				}
			}
		}
	
		/**
		 * fetch giving funds
		 */
		public function getGivingFunds(){
			$url = $this->settings->baseUrl . $this->paths['giving']['funds'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch giving fund types
		 */
		public function getGivingFundTypes(){
			$url = $this->settings->baseUrl . $this->paths['giving']['fundTypes'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch giving contribution types
		 */
		public function getGivingContributionTypes(){
			$url = $this->settings->baseUrl . $this->paths['giving']['contributionTypes'] . ".json";
			return $this->fetchJson($url);
		}
		
		/**
		 * fetch giving account types
		 */
		public function getGivingAccountTypes(){
			$url = $this->settings->baseUrl . $this->paths['giving']['accountTypes'] . ".json";
			return $this->fetchJson($url);
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
		 * fetches JSON request on F1, parses and returns response
		 * @param string $url
		 * @param string|array $data
		 * @param const $method
		 * @param string $contentType
		 */
		public function fetchJson($url,$data=null,$method=OAUTH_HTTP_METHOD_GET,$contentType="application/json"){
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				$headers = array(
					'Content-Type' => $contentType,
				);
				if($o->fetch($url, $data, $method, $headers)){
					return json_decode($o->getLastResponse(),true);
				}
			}catch(OAuthException $e){
				var_dump($url,$data);
				var_dump($o->getLastResponseInfo());
				die("$e \n\nError: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
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
						call_user_func($custoHandlers['setAccessToken'],$username,$token);
					}else{
						call_user_func($custoHandlers['setAccessToken'],$token);
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
				$_SESSION['F1RequestToken'] = $token;
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
			$requestToken = $_SESSION['F1RequestToken'];
			
			if($requestToken->oauth_token != $_GET['oauth_token']){
				throw new Exception('Returned OAuth Token Does Not Match Request Token');
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

	