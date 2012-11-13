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
$exitString = array("responce" => "", "new_status" => "");
$followerid = $_GET['follower'];
$follow = $_GET['follow'];
header('Content-type: application/json');
if($user->verifyUserAccount()) {
	if($follow == "Follow") {
		$user->addFollower($followerid);
		$exitString['responce'] = "Added follower";
		$exitString['new_status'] = "following";
	}
	if($follow == "unFollow") {
		$user->removeFollower($followerid);
		$exitString['responce'] = "Removed follower";
		$exitString['new_status'] = "notFollowing";
	}	
}
$dbh->endConnection();
echo json_encode($exitString);
