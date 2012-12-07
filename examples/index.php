<?php


	/*
	 * FellowshipOne Helper Class Examples.
	 * 
	 */

	session_start();
	
	/*
	session_destroy();
	var_dump($_SESSION);
	die('clear session for debug');
	*/
	
	require('../src/FellowshipOne.php');
	
	//find key in F1 Portal under admin > integration > application keys
	$settings = array(
		'key'=>'your key',
		'secret'=>'your secret',
		'baseUrl'=>'https://yourchurchcode.staging.fellowshiponeapi.com',//notice the church code & staging plz!!
		'debug'=>false,
	);
	
	echo "<pre>";//view formatted debug output
	
	$f1 = new FellowshipOne($settings);
	
	//Login Examples -- uncomment one at a time to test
	//require('1stpartylogin.php');
	//require('2ndpartylogin.php');
	//require('3rdpartylogin.php');

	//Resource Examples -- uncomment to test
	//require('resources.php');
	

	
	
	