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

if(isset($_POST['url']) && isset($_POST['title']) && isset($_POST['message'])) {
 if($user->verifyUserAccount()) {
 	$user->addShare($_POST['url'],$_POST['title'],$_POST['message']);
 	header('Content-type: application/json');
 	echo json_encode(array("responce" => "Successfully shared.", "new_status" => "Successfully shared."));
 } else {
 	exit(header("HTTP/1.0 401 Not Found"));
 }
} else {
	exit(header("HTTP/1.0 406 Not Found"));
}
$dbh->endConnection();
 
