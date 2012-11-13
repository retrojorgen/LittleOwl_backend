<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
require_once("../config.php");
require_once("lib/sirFartDatabaseClass.php");
require_once("lib/sirFartTwitterConnectionClass.php");
require_once("lib/sirFartUserClass.php");
$dbh = new sirFartDatabaseClass(PDO_CONNECTION, DB_USERNAME, DB_PASSWORD);
$twitter_Connection = new sirFartTwitterConnectionClass($_COOKIE['twitterauth'], $dbh, CONSUMER_KEY, CONSUMER_SECRET);
$user = new sirFartUserClass($dbh, $twitter_Connection);
$exitString = 0;

if($user->verifyUserAccount()) {
	$followers = $user->getFollowers();
	if(!$followers) {
		exit(header("HTTP/1.0 404 Not Found"));
	} else {
		header('Content-type: application/json');
		echo json_encode($followers);
	}
} else {
	exit(header("HTTP/1.0 401 Not Found"));
}
$dbh->endConnection();
