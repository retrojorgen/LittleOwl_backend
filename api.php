<?php
require_once("../config.php");
require_once("lib/sirFartDatabaseClass.php");
require_once("lib/sirFartTwitterConnectionClass.php");
require_once("lib/sirFartUserClass.php");
$dbh = new sirFartDatabaseClass(PDO_CONNECTION, DB_USERNAME, DB_PASSWORD);
if(isset($_COOKIE['twitterauth'])) {
	$twitter_Connection = new sirFartTwitterConnectionClass($_COOKIE['twitterauth'], $dbh, CONSUMER_KEY, CONSUMER_SECRET);
	$user = new sirFartUserClass($dbh, $twitter_Connection);

	if($user->verifyUserAccount() && isset($_GET['type'])) {
		switch ($_GET['type']) {
			case 'showshare':
				header('Content-type: application/json');
				if(isset($_GET['id']) && isset($_GET['status'])) {
					echo json_encode($user->getshareLog($_GET['id'], $_GET['status']));
				} elseif (!isset($_GET['id']) && isset($_GET['status'])) {	
					echo json_encode($user->getShareLog(false,$_GET['status']));
				} else {
					echo json_encode($user->getshareLog(false, false));
				}	
			    break;
			case 'showfollowers':
				$followers = $user->getFollowers();
				if(!$followers) {
					header("HTTP/1.0 404 Not Found");
				} else {
					header('Content-type: application/json');
					echo json_encode($followers);
				}
				break;		
			case 'sharelogtodatabase':
				if(isset($_POST['url']) && isset($_POST['title']) && isset($_POST['message'])) {
				 	$user->addShare($_POST['url'],$_POST['title'],$_POST['message']);
				 	header('Content-type: application/json');
				 	echo json_encode(array("responce" => "Successfully shared.", "new_status" => "Successfully shared."));
				}
				else {
					header("HTTP/1.0 406 Not Found");
				}
			 	break;	
			case 'getusercredentials':
				header('Content-type: application/json');
				echo json_encode($user->getUserCredentials());
				break;
			case 'deleteusershare':
				if(isset($_GET['id'])) {					
					if($user->deleteShare($_GET['id'])) {
						header('Content-type: application/json');
					} else {
						header("HTTP/1.0 404 Not Found");
					}
				} else {
					header("HTTP/1.0 406 Not Found");
				}	
				break;
			case 'followertodatabase':
				$followerid = $_GET['follower'];
				$follow = $_GET['follow'];
				header('Content-type: application/json');
					if($follow == "Follow") {
						$user->addFollower($followerid);
						echo json_encode(array("responce" => "Added follower", "new_status" => "following"));
					}
					if($follow == "unfollow") {
						$user->removeFollower($followerid);
						echo json_encode(array("responce" => "Removed follower", "new_status" => "notFollowing"));
					}
				break;				
			default:
			  	header("HTTP/1.0 404 Not Found");
			  	break;
		}
	} else {
		header("HTTP/1.0 401 Not Found");
	}
} else {
	if(isset($_GET['type'])) {
		switch ($_GET['type']) {
			case 'authenticationredirect':
			if(isset($_GET['callbackurl'])) {
				$callbackurl = "location: " . $_GET['callbackurl'];
			} else {
				$callbackurl = "location: process.php";
			}
			header($callbackurl);
			break;
		}		
	}
}
$dbh->endConnection();
