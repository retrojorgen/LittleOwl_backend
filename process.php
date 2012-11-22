<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
session_start();
include_once("../config.php");
require_once("lib/sirFartDatabaseClass.php");
include_once("lib/twitteroauth.php");


if (isset($_REQUEST['oauth_token']) && $_SESSION['token']  !== $_REQUEST['oauth_token']) {

	// if token is old, distroy any session and redirect user to auth.php
	session_destroy();
	$headerRedirectToCallbackUrl = 'location: ' . $_SESSION['callbackUrl'];
	header($headerRedirectToCallbackUrl);
	
}elseif(isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token']) {

	// everything looks good, request access token
	//successful response returns oauth_token, oauth_token_secret, user_id, and screen_name
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['token'] , $_SESSION['token_secret']);
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	if($connection->http_code=='200')
	{
		//redirect user to twitter
		$_SESSION['status'] = 'verified';
		$_SESSION['request_vars'] = $access_token;
		$token = $access_token['oauth_token'];
		$token_secret = $access_token['oauth_token_secret'];
		$user_id = $access_token['user_id'];
		$dbh = new sirFartDatabaseClass(PDO_CONNECTION, DB_USERNAME, DB_PASSWORD);
		$dbh->queryDatabaseWithBinds("INSERT INTO twitterauth VALUES('',:token,:token_secret,:user_id,NOW());",array(':token'=>$token,':token_secret'=>$token_secret,':user_id'=>$user_id));


		setcookie('twitterauth' , $user_id);	
		// unset no longer needed request tokens
		unset($_SESSION['token']);
		unset($_SESSION['token_secret']);
		$headerRedirectToCallbackUrl = 'location: ' . $_SESSION['callbackUrl'];
		header($headerRedirectToCallbackUrl);
	}else{
		die("error, try again later!");
	}
		
}else{

	if(isset($_GET["denied"]))
	{
		header('Location: ./auth.php');
		die();
	}

	//fresh authentication
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	if($_GET['callbackurl']) {
		$callbackUrl = $_GET['callbackurl']
	} else {
		$callbackUrl = OAUTH_CALLBACK;
	}
		$request_token = $connection->getRequestToken(OAUTH_CALLBACK);
	//received token info from twitter
	$_SESSION['token'] 			= $request_token['oauth_token'];
	$_SESSION['token_secret'] 	= $request_token['oauth_token_secret'];
	$_SESSION['callbackUrl'] = $callbackUrl;
	
	// any value other than 200 is failure, so continue only if http code is 200
	if($connection->http_code=='200')
	{
		//redirect user to twitter
		$twitter_url = $connection->getAuthorizeURL($request_token['oauth_token']);
		header('Location: ' . $twitter_url); 
	}else{
		die("error connecting to twitter! try again later!");
	}
}
?>

