<?php 

	/**
	 * F1 Helper Node for the FellowshipOne Helper Class, it's chainable!
	 * @class F1Node
	 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
	 * @copyright 2012 Daniel Boorn
	 * @author Daniel Boorn daniel.boorn@gmail.com
	 * @requires PHP PECL OAuth, http://php.net/oauth
	 *
	 */
	class F1Node{
		public $returnType;
		public $name;
		public $find;
		public $resource;
		public $contents;
		public static $f1;
	
		/**
		 * construct
		 * @param FellowshipOne $f1
		 * @param string $returnType
		 */
		public function __construct($f1,$returnType=""){
			$this->returnType = $returnType;
			self::$f1 = $f1;
		}
	
		/**
		 * search resource by criteria
		 * @param array $criteria
		 * @return F1Node
		 */
		public function with($criteria){
			$fn = "search" . ucfirst($this->resource) . $this->returnType;
			$this->contents = self::$f1->{$fn}($criteria);
			return $this;
		}
		
		/**
		 * returns contents
		 * @param string|null $what
		 * @return mixed
		 */
		public function get($what=null){
			if(!$what) return $this->contents;
			$resultsName = FellowshipOne::$paths[$this->resource]['resultsName'];
			$results = $this->contents['results'][$resultsName];
			if(!$results) return $results;
			switch($what){
				case "all": return $results;
				case "first": return $results[0];
				case "last": return $results[count($results)-1];
				default: return $results;
			}
		}
		
		
		public function edit($modifiers){
			//what am i editing? 
			$items = $this->get('all');
				
		}
	
		/**
		 * returns true if name is f1 resource
		 * @param string $name
		 * @return boolean
		 */
		public function isResource($name){
			foreach(FellowshipOne::$paths as $key=>$value){
				if($key==$name) return true;
			}
			return false;
		}
	
		/**
		 * magic function
		 * @param string $name
		 * @param array $args
		 * @return mixed
		 */
		public function __call($name,$args){
				
			if($this->isResource($name)){
				$this->resource = $name;
				return $this;
			}
				
		}
	}
	
	/**
	 * Helper Class for the FellowshipOne.com API.
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
		
		protected $settings;
		protected $modeCache;
		
		
		public $error = null;
		
		public static $paths = array(
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
				'subFunds'=>'/giving/v1/funds/{fundID}/subfunds',
				'newContributionReceipt'=>'/giving/v1/contributionreceipts/new',
				'createContributionReceipt'=>'/giving/v1/contributionreceipts',
			),
			'people' => array(//!! note the naming convention on the keys (see getModel, newModel, create, update, delete functions) 
				'resultsName' => 'person',//for F1Node,
				'contentType' => 'application/vnd.fellowshiponeapi.com.people.people.v2+',
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
				'editAddress' => '/v1/People/{personID}/Addresses/{addressID}/edit',
				'updateAddress' => '/v1/People/{personID}/Addresses/{addressID}',
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
				'editCommunication' => '/v1/People/{personID}/Communications/{communicationID}/edit',
				'updateCommunication' => '/v1/People/{personID}/Communications/{communicationID}',
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
		public $timezoneName = "Central Standard Time";
		public $timezoneAbr = "CST";
		
		/**
		 * contruct fellowship one class with settings array that contains
		 * @param array|object $settings
		 * @return void
		 */
		public function __construct($settings){
			$this->settings = (object) $settings;
			self::$paths['tokenCache'] = sprintf("%stokens/",APPPATH);
		}
		
		/**
		 * use __get magic method for easy property methods
		 * @param string $property
		 * @return mixed
		 */
		public function __get($property){
			$property = 'get'.ucfirst($property);
			if (method_exists($this, $property)){
				return call_user_func(array($this, $property));
			}		
		}
		
		/**
		 * get api content type xml|json
		 * @returns string xml|json
		 */
		public function getContentType(){
			if(isset($this->settings->contentType) && $this->settings->contentType=="xml"){
				return "xml";
			}
			return "json";
		}
		
		/**
		 * chainable query function
		 * @return F1Node
		 */
		public function find(){
			return new F1Node($this);
		}
		
		/**
		 * reverts response content type to cached version
		 * @return void
		 */
		public function revertMode(){
			if($this->modeCache){
				$this->settings->contentType = $this->modeCache;
			}
		}
		
		/**
		 * sets response content type to json
		 * @return void
		 */
		public function modeJson(){
			$this->modeCache = $this->contentType;
			$this->settings->contentType="json";
		}
		
		/**
		 * sets response content type to xml
		 * @return void
		 */
		public function modeXml(){
			$this->modeCache = $this->contentType;
			$this->settings->contentType="xml";
		}
		
		/**
		 * dump object to output
		 * @param object $object
		 * @return void
		 */
		public function debug($object){
			if($this->settings->debug) var_dump($object);
		}
		
		/**
		 * build api request url
		 * @param string $realm
		 * @param string $method
		 * @param array $args
		 * @return string
		 */
		protected function buildUrl($realm,$method,$args){
			$url = $this->settings->baseUrl . self::$paths[$realm][$method] . ".{$this->contentType}";
			foreach($args as $key=>$value){
				$url = str_replace('{'.$key.'}',$value,$url);
			}
			return $url;
		}
		
		/**
		 * BEGIN: F1 API Resource Functions
		 */

		/**
		 * create new model for saving
		 * @param string $realm
		 * @param string $resource
		 * @param array|null $args
		 * @param string|null $contentType
		 * @return mixed
		 */
		protected function newModel($realm,$resource,$args=null,$contentType=null){
			$url = $this->buildUrl($realm,"new{$resource}",(array)$args);
			return $this->fetch($url,null,OAUTH_HTTP_METHOD_GET,$contentType);
		}
		
		/**
		 * get model(s) for interating
		 * @param string $realm
		 * @param string $resource
		 * @param array|null $args
		 * @param string|null $contentType
		 * @return mixed
		 */
		protected function getModel($realm,$resource,$args=null,$contentType=null){
			$url = $this->buildUrl($realm,$resource,(array)$args);
			return $this->fetch($url,null,OAUTH_HTTP_METHOD_GET,$contentType);
		}
		
		/**
		 * saves new model
		 * @param string $realm
		 * @param string $resource
		 * @param array|string $model (array=json,string=xml)
		 * @param array $args
		 * @param string|null $contentType
		 * @return mixed
		 */
		protected function create($realm,$resource,$model,$args=null,$contentType=null){
			if(!$args) $args = array();
			$url = $this->buildUrl($realm,"create{$resource}",$args);
			$model = is_array($model) ? json_encode($model) : $model;
			return $this->fetch($url,$model,OAUTH_HTTP_METHOD_POST,$contentType);
		}
		
		/**
		 * fetches model for editing
		 * @param string $realm
		 * @param string $resource
		 * @param array $args
		 * @param string|null $contentType
		 * @return mixed
		 */
		protected function edit($realm,$resource,$args,$contentType=null){
			$url = $this->buildUrl($realm,"edit{$resource}",$args);
			return $this->fetch($url,null,OAUTH_HTTP_METHOD_GET,$contentType);
		}
		
		/**
		 * saves editing model
		 * @param string $realm
		 * @param string $resource
		 * @param array|string $model (array=json,string=xml)
		 * @param string|null $contentType
		 * @return mixed
		 */
		protected function update($realm,$resource,$model,$args,$contentType=null){
			$url = $this->buildUrl($realm,"update{$resource}",$args);
			$model = is_array($model) ? json_encode($model) : $model;
			return $this->fetch($url,$model,OAUTH_HTTP_METHOD_PUT,$contentType);
		}
		
		/**
		 * deletes record
		 * @param string $realm
		 * @param string $resource
		 * @param array $args
		 * @return mixed
		 */
		protected function delete($realm,$resource,$args){
			$url = $this->buildUrl($realm,"update{$resource}",$args);
			return $this->fetch($url,null,OAUTH_HTTP_METHOD_DELETE);
		}
		
		
		/**
		 * get timestamp ajusted to with timezone offset of api (currently f1 does not include timezone in date/time)
		 * @param string $datetime
		 * @return string
		 */
		public function getAdjustedTimestamp($datetime){
			return strtotime("{$datetime} {$this->timezone}");
		}
		
		/**
		 * returns adjusted DateTime to API time from timestamp
		 * @param number $timestamp
		 * @param boolean $returnFormatted
		 * @return DateTime $date
		 */
		public function getAdjustedDateTime($timestamp,$returnFormatted=true){
			$date = new DateTime();
			$date->setTimestamp($timestamp);
			$date->setTimezone(new DateTimeZone(timezone_name_from_abbr($this->timezoneAbr)));
			if(!$returnFormatted) return $date;
			//e.g. 2008-08-25T00:00:00
			return $date->format('Y-m-d\TH:i:s');
		}
		
		/**
		 * fetch contribution receipt model from F1
		 * @return mixed
		 */
		public function getContributionReceiptModel(){
			return $this->newModel('giving','ContributionReceipt');
		}
		
		/**
		 * create new contribution receipt
		 * @param object $model
		 * @return mixed
		 */
		public function createContributionReceipt($model){
			return $this->create('giving','ContributionReceipt',$model);
		}
		
		/**
		 * fetch address model from F1
		 * @param int $personId
		 * @return mixed
		 */
		public function getAddressModel($personId){
			return $this->newModel('people','Address',array('personID'=>$personId));
		}
		
		/**
		 * create new address record
		 * @param object $model
		 * @param int $personId
		 * @return mixed
		 */
		public function createAddress($model,$personId){
			return $this->create('people','Address',$model,array('personID'=>$personId));
		}
		
		/**
		 * edit address record
		 * @param int $personId
		 * @param int $addressId
		 * @return mixed
		 */
		public function editAddress($personId,$addressId){
			return $this->edit('people','Address',array('personID'=>$personId,'addressID'=>$addressId));
		}

		/**
		 * update address record
		 * @param array|string (array=json|string=xml)
		 * @param int $personId
		 * @param int $addressId
		 * @return mixed
		 */
		public function updateAddress($model,$personId,$addressId){
			return $this->update('people','Address',$model,array('personID'=>$personId,'addressID'=>$addressId));
		}
		
		
		/**
		 * delete address record
		 * @param int $personId
		 * @param int $addressId
		 * @return mixed
		 */
		public function deleteAddress($personId,$addressId){
			return $this->delete('people','Address',array('personID'=>$personId,'addressID'=>$addressId));
		}
		
		/**
		 * fetch person model from F1
		 * @return mixed
		 */
		public function getPersonModel(){
			return $this->newModel('people','Person',null,self::$paths['people']['contentType'].$this->contentType);
		}
		
		/**
		 * create new person record
		 * @param object $model
		 * @return mixed
		 */
		public function createPerson($model){
			return $this->create('people','Person',$model,array(),self::$paths['people']['contentType'].$this->contentType);
		}
		
		
		/**
		 * upate person record
		 * @param object $model
		 * @param int $personId (optional for xml)
		 * @return mixed
		 */
		public function updatePerson($model,$personId=null){
			return $this->update('people','Person',$model,array('personID'=>$personId ? $personId : $model['person']['@id']),self::$paths['people']['contentType'].$this->contentType);
		}
		
		/**
		 * fetch existing person model for editing
		 * @param number $personId
		 * @return mixed
		 */
		public function editPerson($personId){
			return $this->edit('people','Person',array('personID'=>$personId),self::$paths['people']['contentType'].$this->contentType);
		}
		
		/**
		 * fetch attributes for a person 
		 * @return mixed
		 */
		public function getPeopleAttributes($personId){
			return $this->getModel('people','attributes',array('personID'=>$personId));
		}
		
		/**
		 * fetch people attribute model from f1
		 * @param int $personId
		 * @return mixed
		 */
		public function getPeopleAttributeModel($personId){
			return $this->newModel('people','Attribute',array("personID"=>$personId));
		}
		
		/**
		 * create new people attribute model
		 * @param int $personId
		 * @param object $model
		 * @return mixed
		 */
		public function createPeopleAttribute($personId,$model){
			return $this->create('people','Attribute',$model,array('personID'=>$personId));
		}
		
		/**
		 * fetch edit model of people attribute
		 * @param int $personId
		 * @param int $attributeId
		 * @return mixed
		 */
		public function editPeopleAttribute($personId,$attributeId){
			return $this->edit('people','Attribute',array('personID'=>$personId,'attributeID'=>$attributeId));
		}


		/**
		 * update people attribute
		 * @param int $personId
		 * @param array|string $model (array=json,string=xml)
		 * @param int $attributeId
		 * @return mixed
		 */
		public function updatePeopleAttribute($personId,$model,$attributeId=null){
			return $this->update('people','Attribute',$model,array('personID'=>$personId,'attributeID'=>$attributeId ? $attributeId : $model['attribute']['@id']));
		}
		
		/**
		 * delete people attribute
		 * @param int $personId
		 * @param int $attributeId
		 * @return mixed
		 */
		public function deletePeopleAttribute($personId,$attributeId){
			return $this->delete('people','Attribute',array('personID'=>$personId,'attributeID'=>$attributeId));
		}
		
		
		/**
		 * fetch attribute groups for people
		 * @return mixed
		 */
		public function getPeopleAttributeGroups(){
			return $this->getModel('people','attributeGroups');
		}
		
		/**
		 * fetch people statuses from F1
		 * @return mixed
		 */
		public function getPeopleStatuses(){
			return $this->getModel('people','statuses');
		}
		
		/**
		 * get people status by name
		 * @param string $name
		 * @param array|null $statuses
		 * @return mixed
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
		 * @return mixed
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
		 * @return mixed
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
		 * @return mixed
		 */
		public function getHouseholdModel(){
			return $this->newModel('people','Household');
		}
		
		/**
		 * create new household
		 * @param object $model
		 * @return mixed
		 */
		public function createHousehold($model){
			return $this->create('people','Household',$model);
		}
		
		/**
		 * fetch people by searching attributes
		 * @param array $attributes
		 * @return mixed
		 */
		public function searchPeople($attributes){
			$url = $this->settings->baseUrl . self::$paths['people']['peopleSearch'] . ".{$this->contentType}";
			$url .= "?" . http_build_query($attributes);
			return $this->fetch($url,null,OAUTH_HTTP_METHOD_GET,self::$paths['people']['contentType'].$this->contentType);	
		}
		
		
		/**
		 * find person by attribute comment value
		 * @param string $attrId
		 * @param string $attrComment
		 * @param int $lmit (0=unlimited)
		 * @param int $currentPage
		 * @return array $matches
		 */
		public function findPeopleByAttributeComment($attrId,$attrComment,$limit=0,$currentPage=1){
			$criteria = array('attribute'=>$attrId,'include'=>'attributes','page'=>$currentPage);
			$r = $this->searchPeople($criteria);
			
			$matches = array();
			if((int)$r['results']['@count']>0){
				foreach($r['results']['person'] as $person){
					if($person['attributes']){
						foreach($person['attributes']['attribute'] as $attribute){
							if($attribute['attributeGroup']['attribute']['@id']==$attrId &&  $attribute['comment'] == $attrComment){
								$matches[] = $person;
								if($limit >0 && count($matches)==$limit) return $matches;
							}
						}
					}
				}
				if((int)$r['results']['@additionalPages']>0){
					return array_merge($matches,$this->findPeopleByAttributeComment($attrId,$attrComment,$limit,$currentPage+1));
				}
			}
			return $matches;
		}
		
		/**
		 * fetch households by name search
		 * @param string $name
		 * @return mixed
		 */
		public function getHouseholdsByName($name){
			$url = $this->settings->baseUrl . self::$paths['people']['householdSearch'] . ".{$this->contentType}";
			$url .= "?searchFor=" . urlencode($name);
			return $this->fetch($url);	
		}
		
		/**
		 * fetch households by searching attributes
		 * @param array $attributes
		 * @return mixed
		 */
		public function searchHouseholds($attributes){
			$url = $this->settings->baseUrl . self::$paths['people']['householdSearch'] . ".{$this->contentType}";
			$url .= "?" . http_build_query($attributes);
			return $this->fetch($url,null,OAUTH_HTTP_METHOD_GET);
		}
		
		/**
		 * fetch household member types
		 * @return mixed
		 */
		public function getPeopleHouseholdMemberTypes(){
			return $this->getModel('people','householdMemberTypes');
		}
		
		/**
		 * fetch address types
		 * @return mixed
		 */
		public function getAddressTypes(){
			return $this->getModel('addresses','addressTypes');
		}
		
		/**
		 * fetch address type by attributes
		 * @param array $attributes
		 * @param array|null $types
		 * @return mixed
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
		 * @return mixed
		 */
		public function getPeopleCommunicationModel($personId){
			return $this->newModel('people','Communication',array('personID'=>$personId));
		}
		
		/**
		 * fetch edit model of people communiction record
		 * @param int $commId
		 * @param int $personId
		 * @return mixed
		 */
		public function editPeopleCommunication($commId,$personId){
			return $this->edit('people','Communication',array('communicationID'=>$commId, 'personID'=>$personId));
		}
		
		/**
		 * update people communication record
		 * @param object $model
		 * @param int $personId
		 * @param int $communicationId (optional for xml)
		 * @return mixed
		 */
		public function updatePeopleCommunication($model,$personId,$communicationId){
			return $this->update('people','Communication',$model,array('personID'=>$personId,'communicationID'=>$communicationId));
		}
		
		/**
		 * create new people communication record
		 * @param object $model
		 * @param int $personId
		 * @return mixed
		 */
		public function createPeopleCommunication($model,$personId){
			return $this->create('people','Communication',$model,array('personID'=>$personId));
		}
		
		/**
		 * delete people communication record
		 * @param int $personId
		 * @param int $communicationId
		 * @return mixed
		 */
		public function deletePeopleCommunication($personId,$communicationId){
			return $this->delete('people','Communication',array('personID'=>$personId,'communicationID'=>$communicationId));
		}
		
		/**
		 * fetch communication types
		 * @return mixed
		 */
		public function getCommunicationTypes(){
			return $this->getModel('communications','communicationTypes');
		}
		
		
		/**
		 * fetch communication types by attributes
		 * @param array $attributes
		 * @param array|null $types
		 * @return mixed
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
		 * @return mixed
		 */
		public function getBackgroundCheckStatuses(){
			return $this->getModel('requirements','backgroundCheckStatuses');
		}
		
		/**
		 * fetch background check status by name
		 * @return mixed
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
		 * @return mixed
		 */
		public function getPeopleRequirements($personId){
			return $this->getModel('requirements','peopleRequirements');
		}
		
		/**
		 * fetch requirement statuses
		 * @return mixed
		 */
		public function getRequirementStatuses(){
			return $this->getModel('requirements','requirementStatuses');
		}
		
		/**
		 * fetches requirement status by name
		 * @return mixed
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
		 * @return mixed
		 */
		public function getGivingFunds(){
			return $this->getModel('giving','funds');
		}
		
		/**
		 * fetch giving fund types
		 * @return mixed
		 */
		public function getGivingFundTypes(){
			return $this->getModel('giving','fundTypes');
		}

		/**
		 * fetch giving sub-funds
		 * @param int $fundId
		 * @return $mixed
		 */
		public function getGivingSubFunds($fundId){
			return $this->getModel('giving','subFunds',array('fundID'=>$fundId));
		}
		
		
		/**
		 * fetch giving contribution types
		 * @return mixed
		 */
		public function getGivingContributionTypes(){
			return $this->getModel('giving','contributionTypes');
		}
		
		/**
		 * fetch giving account types
		 * @return mixed
		 */
		public function getGivingAccountTypes(){
			return $this->getModel('giving','accountTypes');
		}
		
		/**
		 * get person information by login credentials
		 * @param string $username
		 * @param string $password
		 * @return array|boolean
		 */
		public function getPersonByCredentials($username,$password){
			
			$token = $this->obtainCredentialsBasedAccessToken($username,$password,true);
			if(!$token) return false;
			$url = $token->headers['Content-Location'].".{$this->contentType}";
			return $this->fetch($url);
		}
		
		
		/**
		 * BEGIN: OAuth Functions
		 */
		
		
		/**
		 * directly set access token. e.g. 1st party token based authentication
		 * @param array $token
		 * @return void
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
		 * @return void
		 */
		public function fetch($url,$data=null,$method=OAUTH_HTTP_METHOD_GET,$contentType=null){
			if(!$contentType) $contentType = "application/$this->contentType";
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				$headers = array(
					'Content-Type' => $contentType,
				);
				if($o->fetch($url, $data, $method, $headers)){
					if(str_replace("json","",$contentType)!=$contentType){
						return json_decode($o->getLastResponse(),true);
					}else{
						return $o->getLastResponse();
					}
				}
			}catch(OAuthException $e){
				$this->error = array(
					'error'=>true,
					'code'=>$e->getCode(),
					'response'=>$e->lastResponse,	
					'data'=>$data,
					'url'=>$url,
				);
				return false;
			}
		}
		
		
		/**
		 * get access token file name from username
		 * @param string $username
		 * @return string
		 */
		protected function getAccessTokenFileName($username){
			$hash = md5($username);
			return self::$paths['tokenCache'] . ".f1_{$hash}.accesstoken";
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
		 * @return void
		 */
		protected function saveFileAccessToken($username,$token){
			$fileName = $this->getAccessTokenFileName($username);
			file_put_contents($fileName,json_encode($token));
		}
		
		/**
		 * save access token to session
		 * @param array $token
		 * @return void
		 */
		protected function saveSessionAccessToken($token){
			$_SESSION['F1AccessToken'] = (object) $token;
		}
		
		/**
		 * save access token by session or file
		 * @param string $username
		 * @param array $token
		 * @param const $cacheType
		 * @return void
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
		 * parse header string to array
		 * @source http://php.net/manual/en/function.http-parse-headers.php#77241
		 * @param string $header
		 * @return array $retVal
		 */
		public static function http_parse_headers( $header ){
			$retVal = array();
			$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
			foreach( $fields as $field ) {
				if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
					$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
					if( isset($retVal[$match[1]]) ) {
						$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
					} else {
						$retVal[$match[1]] = trim($match[2]);
					}
				}
			}
			return $retVal;
		}

		/**
		 * obtain credentials based access token from API
		 * @param string $username
		 * @param string $password
		 * @param boolean $returnHeaders=false
		 * @return array
		 */
		protected function obtainCredentialsBasedAccessToken($username,$password,$returnHeaders=false){
			try{
				$message = urlencode(base64_encode("{$username} {$password}"));
				$url = $this->settings->baseUrl . self::$paths['portalUser']['accessToken'] . "?ec={$message}";
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$token = $o->getAccessToken($url);
				if($returnHeaders) $token['headers'] = self::http_parse_headers($o->getLastResponseHeaders());				
				return (object) $token;				
			}catch(OAuthException $e){
				$this->error = array(
					'error'=>true,
					'code'=>$e->getCode(),
					'response'=>$e->lastResponse,
					'url'=>$url,
				);
				return false;
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
				$url = $this->settings->baseUrl . self::$paths['general']['requestToken'];
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				$this->error = array(
					'error'=>true,
					'code'=>$e->getCode(),
					'response'=>$e->lastResponse,
					'url'=>$url,
				);
				return false;
			}
		}
		
		/**
		 * redirect user for 3rd party authorization with callback url
		 * @param object $token
		 * @param string $callbackUrl
		 * @return mixed
		 */
		protected function redirectUserAuthorization($token,$callbackUrl){
			try{
				$_SESSION['F1RequestToken'] = $token;
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($token->oauth_token, $this->oauth_token_secret);
				$url = $this->settings->baseUrl . self::$paths['portalUser']['userAuthorization'] . "?oauth_token={$token->oauth_token}&oauth_callback={$callbackUrl}";
				@header("Location:{$url}");
				die("<script>window.location='{$url}'</script><meta http-equiv='refresh' content='0;URL=\"{$url}\"'>");//backup redirect
			}catch(OAuthException $e){
				$this->error = array(
					'error'=>true,
					'code'=>$e->getCode(),
					'response'=>$e->lastResponse,
					'url'=>$url,
				);
				return false;
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
				$url = $this->settings->baseUrl . self::$paths['general']['accessToken'];
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($requestToken->oauth_token, $requestToken->oauth_token_secret);
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				$this->error = array(
					'error'=>true,
					'code'=>$e->getCode(),
					'response'=>$e->lastResponse,
					'url'=>$url,
				);
				return false;
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

	