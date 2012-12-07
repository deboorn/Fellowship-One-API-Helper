<?php

	/**
	 * Examples for 2nd Party Login with Credentials (to be used with ../index.php)
	 */

	$username = 'your username';
	$password = 'your password';
	
	
	#Example 1: login to F1 API via 2nd party with user credentials and save access token to session
	$r = $f1->login2ndParty($username,$password);
	//$r = $f1->login2ndParty($username,$password,FellowshipOne::TOKEN_CACHE_SESSION);//produces smae result
	
	
	#Example 2: login to F1 API via 2nd party with user credentials and save access token to file based on username hash
	//$r = $f1->login2ndPartyCredentialsBased($username,$password,FellowshipOne::TOKEN_CACHE_FILE);
	
	
	
	#Example 3: login to F1 API via 2nd party with user credentials and use custom handlers to get/save access token, lazy alternative to extending class
	/**
	 * Custom Get Access Token Handler
	 * @param string $username
	*/
	/*
	function handleGetAccessToken($username){
		//e.g. to get token from database by username
		//must return object with properties "oauth_token" and "oauth_token_secret"
		return (object) array(
			'oauth_token'=>'oauth token here',
			'oauth_token_secret'=>'oauth token secret here',
		);
	}
	*/
	
	/**
	 * Custom Save Access Token Handler
	 * @param string $username
	 * @param object $token
	*/
	/*
	function handleSaveAccessToken($username,$token){
		//save access token here
		//e.g. to save to database
	}
	
	$r = $f1->login2ndPartyCredentialsBased($username,$password,FellowshipOne::TOKEN_CACHE_CUSTOM,array(
		'getAccessToken'=>'handleGetAccessToken',
		'saveAccessToken'=>'handleSaveAccessToken',
	));
	*/
	

	if(!$r){
		die("Login Failed");
	}

