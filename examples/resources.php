<?php 
	
	//Resource Examples
	//uncomment any of the items below for demos
	
	//note: you may want to cache your general information to reduce load on F1 API
	//var_dump($f1->givingContributionTypes);
	//var_dump($f1->givingAccountTypes);
	//var_dump($f1->givingFundTypes);
	//var_dump($f1->givingFunds);
	//var_dump($f1->peopleHouseholdMemberTypes);
	
	
	
	#example of household search
	$householdId = null;
	$r = $f1->getHouseholdsByName("Boorn");
	if($r['results']['@totalRecords']>0){
		foreach($r['results']['household'] as $household){
			//perform logic here
			//var_dump($household['@id']);
			$householdId = $household['@id'];
			break;
		}
	}
	
	
	//example of finding a certain contribution type id
	//note: you should store this for future reference to reduce load on F1 API
	$cTypes = $f1->givingContributionTypes;
	$ccTypeId = null;
	foreach($cTypes['contributionTypes']['contributionType'] as $cType){
		if($cType['name']=="Credit Card"){
			$ccTypeId = $cType['@id'];
			break;
		}
	}
	
	
	
	//example of finding a certain giving fund id
	//note: you should store this for future reference to reduce load on F1 API
	$gFunds = $f1->givingFunds;
	$onlineGivingFundId = null;
	foreach($gFunds['funds']['fund'] as $gFund){
		if($gFund['name'] == "Other Contributions"){
			$onlineGivingFundId = $gFund['@id'];
		break;
		}
	}
	
	
	//example of creating (saving) new contribution receipt (uses examples above)
	//fetch new contribution receipt model from F1 API
	$model = $f1->contributionReceiptModel;
	
	//set attributes of new contribution receipt
	//http://developer.fellowshipone.com/docs/giving/v1/ContributionReceipts.help#create
	$model['contributionReceipt']['fund']['@id'] = (int) $onlineGivingFundId;
	$today = new DateTime('now');//set received date to now
	$model['contributionReceipt']['receivedDate'] = $today->format(DATE_ATOM);
	$model['contributionReceipt']['contributionType']['@id'] = (int) $ccTypeId;
	$model['contributionReceipt']['amount'] = (float) 25.25;
	$model['contributionReceipt']['household']['@id'] = (int) $householdId;
		
	$r = $f1->createContributionReceipt($model);
	
	if($r){
		echo "Contribution Receipt ID: {$r['contributionReceipt']['@id']}\n";
	}
	
	//example of create new household
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
		echo "New Household ID: {$r['household']['@id']}\n";
	}
	
	//example of people search
	$r = $f1->searchPeople(array(//search attributes
		"searchFor"=>"Boorn",
		//"address"=>"12 Widget Place",
	));
	
	if($r && $r['results']['@count']>0){
		foreach($r['results']['person'] as $person){
			//var_dump($person);
			echo $person['firstName'];
		}
	}
