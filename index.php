<?php

	/*
	 * Simple example of how to use. Currently only the login and two simple requests are implemented.
	 * However, this should be enouph to jump start your 2nd party code. Will post more as developed.
	 * 
	 * REQUIRES PHP 5 WITH CURL
	 * 
	 */

	require('FellowshipOne.php');
	
	$settings = array(
		'key'=>'yourkeyhere',
		'secret'=>'yoursecrethere',
		'username'=>'yourusernamehere',
		'password'=>'yourpasswordhere',
		'baseUrl'=>'https://yourchurchcodehere.staging.fellowshiponeapi.com',
		'debug'=>false,
	);
	
	$f1 = new FellowshipOne($settings);
	if(($r = $f1->login()) === false){
		die("Failed to loign");
	}
	
	echo "<pre>";
	
	//uncomment any of the items below for demos
	
	//note: you may want to cache your general information to reduce load on F1 API
	//var_dump($f1->givingContributionTypes);
	//var_dump($f1->givingAccountTypes);
	//var_dump($f1->givingFundTypes);
	//var_dump($f1->givingFunds);
	//var_dump($f1->peopleHouseholdMemberTypes);
	
	
	
	/*
	//example of household search
	$householdId = null;
	if(($r = $f1->getHouseholdsByName("Doe")) !== null){
		foreach($r['results']['household'] as $household){
			//perform logic here
			//var_dump($household['@id']);
			$householdId = $household['@id'];
			break;
		}
	}
	*/
	
	//example of finding a certain contribution type id
	/*
	//note: you should store this for future reference to reduce load on F1 API
	$cTypes = $f1->givingContributionTypes;
	$ccTypeId = null;
	foreach($cTypes['contributionTypes']['contributionType'] as $cType){
		if($cType['name']=="Credit Card"){
			$ccTypeId = $cType['@id'];
			break;
		}
	}
	*/
	
	//example of finding a certain giving fund id
	/*
	//note: you should store this for future reference to reduce load on F1 API
	$gFunds = $f1->givingFunds;
	$onlineGivingFundId = null;
	foreach($gFunds['funds']['fund'] as $gFund){
		if($gFund['name'] == "To Be Categorized - Online Giving"){
			$onlineGivingFundId = $gFund['@id'];
			break;
		}
	}
	*/
	
	//example of creating (saving) new contribution receipt (uses examples above)
	/*
	//fetch new contribution receipt model from F1 API
	$model = $f1->contributionReceiptModel;
	
	//set attributes of new contribution receipt
	$model['contributionReceipt']['fund']['@id'] = (int) $onlineGivingFundId;
	$today = new DateTime('now');//set received date to now
	$model['contributionReceipt']['receivedDate'] = $today->format(DATE_ATOM);
	$model['contributionReceipt']['contributionType']['@id'] = (int) $ccTypeId;
	$model['contributionReceipt']['amount'] = (float) 25.25;
	$model['contributionReceipt']['household']['@id'] = (int) $householdId;
	$r = $f1->createContributionReceipt($model);
	
	if($r){
		var_dump($r['contributionReceipt']['@id']);
	}
	*/
	
	//example of create new household
	/*
	$household = array(
		"householdName" => "John Doe",
		"householdSortName" => "Doe",
		"householdFirstName" => "John",
	);
	
	$model = $f1->householdModel;
	//var_dump($model);//see model structor
	$model['household']['householdName'] = "John Doe";
	$model['household']['householdSortName'] = "Doe";
	$model['household']['householdFirstName'] = "John";
	$r = $f1->createHousehold($model);
	if($r){
		var_dump($r['household']['@id']);
	}
	*/
	
	//example of people search
	/*
	$r = $f1->searchPeople(array(//search attributes
			"searchFor"=>"Doe",
			"address"=>"12 Widget Place",
	));
	if($r && $r['results']['@count']>0){
		foreach($r['results']['person'] as $person){
			var_dump($person['firstName']);
		}
	}
	*/
	
	
	
	