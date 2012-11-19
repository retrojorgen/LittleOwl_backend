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
	header("location: congratulations.html");
}
else {
	if($user->setTwitterCrendentials()) {
		if($user->createUserAccount()) {
			$user->addFollower(1);
			$user->addFollower($user->getUserId());
		}
		else {
			$exitString = 1;
		}
	}
	else {
		$exitString = 2;
	}
}
$dbh->endConnection();
exit($exitString);
