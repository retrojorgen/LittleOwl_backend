<?php
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
			header("HTTP/1.0 404 Not Found");
		}
	}
	else {
		header("HTTP/1.0 401 Not Found");
	}
}
$dbh->endConnection();
