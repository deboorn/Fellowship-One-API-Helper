<?php


	/*
	 * FellowshipOne Helper Class Examples.
	 */

	ini_set('display_errors','1');

	session_start();
	
	
	/*
	session_destroy();
	var_dump($_SESSION);
	die('clear session for debug');
	*/

	require('lib/com.rapiddigitalllc/FellowshipOne.php');
	
	
	//find key in F1 Portal under admin > integration > application keys
	
	$settings = array(
		'key'=>'you api key',
		'secret'=>'you api secret',
		'baseUrl'=>'https://YOURCHURCHCODE.staging.fellowshiponeapi.com',//notice the church code & staging plz!!
		'debug'=>false,
	);
	
	
	echo "<pre>";//view formatted debug output
	
	$f1 = new FellowshipOne($settings);
	
	//Login Examples -- uncomment one at a time to test
	//require('examples/1stpartylogin.php');
	require('examples/2ndpartylogin.php');
	//require('examples/3rdpartylogin.php');

	//Resource Examples -- uncomment to test
	//require('examples/resources.php');
	

	
	
	