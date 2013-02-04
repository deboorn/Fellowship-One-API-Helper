<?php 
	
	//Resource Examples with Chaining
	
	/**
	 * Warning: Please be conscious of your API consumption when using chaining methods!
	 * Fn: F1Node->with() still respects results paging
	 * 
	 */
	
	$criteria = array('communication'=>'daniel.boorn@gmail.com');

	#all records
	//$people = $f1->find()->people()->with($criteria)->get('all');
	//var_dump($people);
	
	#first record
	//$person = $f1->find()->people()->with($criteria)->get('first');
	//var_dump($person);
	
	#last record
	//$person = $f1->find()->people()->with($criteria)->get('last');
	//var_dump($person);
	
	//more coming soon
	
	
	
	
