<?php

	/**
	 * Examples for 3rd Party (to be used with ../index.php)
	 */

	
	#Example 1: login to F1 API via 3rd party with user credentials and save access token to session
	$callbackUrl = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}";//e.g. url to this page on return from auth
	$r = $f1->login3rdParty($callbackUrl);
	//$r = $f1->login3rdParty($callbackUrl,FellowshipOne::TOKEN_CACHE_SESSION);//produces smae result
	//!FellowshipOne::TOKEN_CACHE_FILE NOT SUPPORTED - Use Session or Custom Handlers
	
	#Example 2: login to F1 API via 3rd party and use custom handlers to get/save access token, lazy alternative to extending class
	/**
	 * Custom Get Access Token Handler
	 * @param string $username
	*/
	/*
	function handleGetAccessToken(){
		var_dump('handle get access token');
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
	function handleSaveAccessToken($token){
		var_dump('handle save access token');
		//save access token here
		//e.g. to save to database
	}
	
	$r = $f1->login3rdParty($callbackUrl,FellowshipOne::TOKEN_CACHE_CUSTOM,array(
		'getAccessToken'=>'handleGetAccessToken',
		'saveAccessToken'=>'handleSaveAccessToken',
	));
	*/
	
	if(!$r){
		die("Login Failed");
	}

