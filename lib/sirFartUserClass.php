<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
// Stores all information on a user and handles interactions on behalf of a user.
class sirFartUserClass {

	public function __construct($databaseConnection, $twitter_connection) {
		$this->databaseConnection = $databaseConnection;
		$this->twitter_connection = $twitter_connection;
		$this->twitter_user_id = $this->twitter_connection->getTwitterUserId();
	}

	public function verifyUserAccount() {
		return $this->seeIfUserIsCreatedInDatabaseAndSetProperties();
	}

	public function createUserAccount() {
		if(!$this->seeIfUserIsCreatedInDatabaseAndSetProperties()) {
			return $this->createUserInDatabaseAndSetProperties();
		}
		return false;
	}
	public function addShare($url, $title, $message) {
		$this->databaseConnection->queryDatabaseWithBinds("
		INSERT 
		INTO 
		sharelog 
		VALUES('',?,?,?,?,NOW());", 
		array(1 => $this->user_id, 2 => $url, 3 => $title, 4 => $message));
		return true;
	}
	public function removeShare($shareLogItemId) {
		$this->databaseConnection->queryDatabaseWithBinds("
		DELETE from sharelog where id = ?;", 
		array(1 => $shareLogItemId));
		return true;		
	}
	public function updateShare($url, $title, $message) {
		//to be implemented	
	}
	public function getFollowers() {
		$this->databaseConnection->queryDatabaseWithBinds("
		SELECT 
		distinct 
		user.twitter_screen_name,
		user.id,
		user.twitter_userid
		from 
		user 
		where 
		user.id 
		in 
		(
			SELECT 
			followers.followerid 
			from 
			followers 
			where 
			followers.userid = ?
		) 	
		and user.id != ?;", array(1 => $this->user_id, 2 => $this->user_id));
		while($row = $this->databaseConnection->getQueryRow()) {
			$followers[] = array (
				"type" => 'follower',
				"userid" => $row['id'],
				"twitter_screen_name" => $row['twitter_screen_name'],
				);
		}
		if(isset($followers)) { 
			return $followers; 
		}
			return false;
	}
	public function getNotFollowers() {
		$this->databaseConnection->queryDatabaseWithBinds("	select 
		distinct 
			user.twitter_screen_name,
			user.id,
			user.twitter_userid
		from 
		user 
		where 
		user.id 
		NOT IN 
		(
		select followers.followerid 
		from 
		followers 
		where 
		followers.userid = ?);",array(1=>$this->user_id));
		while($row = $this->databaseConnection->getQueryRow()) {
			$followers[] = array (
				"status" => 'notFollower',
				"userid" => $row['id'],
				"twitter_screen_name" => $row['twitter_screen_name'],
				);
		}
		if(isset($followers)) { 
			return $followers; 
		}
			return false;	
	}
	public function getShareLog($id = false, $status = false) {
		if(!$id) {
			if($status == "all") {
				$shareLogQuery = "select sh.id, sh.user_id, user.twitter_screen_name, sh.url, sh.title, sh.message, sh.timestamp from sharelog as sh, user where sh.user_id = user.id order by sh.id DESC limit 20;";
				$this->databaseConnection->queryDatabaseWithoutBinds($shareLogQuery);
				while($row = $this->databaseConnection->getQueryRow()) {	
					$shareLog[] = array (
						"id" => $row['id'],
						"user_id" => $row['user_id'],
						"twitter_screen_name" => $row['twitter_screen_name'],
						"url" => $row['url'],
						"host" => $this->getHostForSharedUrl($row['url']),
						"title" => $row['title'],
						"message" => $row['message'],
						"timestamp" => $row['timestamp']
						);				
				}
				return $shareLog;				
			} else {			
				$shareLogQuery = "select sh.id, sh.user_id, user.twitter_screen_name, sh.url, sh.title, sh.message, sh.timestamp from sharelog as sh, user where sh.user_id = user.id and user.id in (select followerid from followers where followers.userid = ?) order by sh.id DESC limit 20;";
				$shareLogQueryPreparedStatementBindsArray = array(1 => $this->user_id);
			}
		} else {
			if($status == "olderThan") {
				$shareLogQuery = "select sh.id, sh.user_id, user.twitter_screen_name, sh.url, sh.title, sh.message, sh.timestamp from sharelog as sh, user where sh.user_id = user.id and user.id in (select followerid from followers where followers.userid = ?) and sh.id < ? order by sh.id DESC limit 20;";
			}
			if($status == "newerThan") {
				$shareLogQuery = "select sh.id, sh.user_id, user.twitter_screen_name, sh.url, sh.title, sh.message, sh.timestamp from sharelog as sh, user where sh.user_id = user.id and user.id in (select followerid from followers where followers.userid = ?) and sh.id > ? order by sh.id DESC limit 20;";
			}
			$shareLogQueryPreparedStatementBindsArray = array(1=>$this->user_id, 2=>$id);
		}
		$this->databaseConnection->queryDatabaseWithBinds($shareLogQuery,$shareLogQueryPreparedStatementBindsArray);
		while($row = $this->databaseConnection->getQueryRow()) {	
			$shareLog[] = array (
				"id" => $row['id'],
				"user_id" => $row['user_id'],
				"twitter_screen_name" => $row['twitter_screen_name'],
				"url" => $row['url'],
				"host" => $this->getHostForSharedUrl($row['url']),
				"title" => $row['title'],
				"message" => $row['message'],
				"timestamp" => $row['timestamp']
				);
		}
		return $shareLog;	
	}
		
	public function addFollower($follower_user_id) {
		$this->databaseConnection->queryDatabaseWithBinds("
		INSERT INTO 
		followers
		VALUES('',?,?);", 
		array(1 => $this->user_id, 2 => $follower_user_id));
		return true;
	}
	public function removeFollower($follower_user_id) {
		$this->databaseConnection->queryDatabaseWithBinds("
		DELETE
		from 
		followers
		where
		userid = ?
		and
		followerid = ?;", 
		array(1 => $this->user_id, 2=> $follower_user_id));
		return true;
	}
	public function setTwitterCrendentials() {
		$this->twitter_credentials = $this->twitter_connection->getTwitterAPIContent('account/verify_credentials');
		return true;
	}
	public function getUserId() {
		return $this->user_id;
	}

	private function seeIfUserIsCreatedInDatabaseAndSetProperties() {
		$this->databaseConnection->queryDatabaseWithBinds("
			SELECT * from user where twitter_userid = ? order by id DESC limit 1;",
			array(1 => $this->twitter_user_id));
		while($row = $this->databaseConnection->getQueryRow()) {
			 $this->setUserId($row['id']);
			return true;
		}
		return false;
	}
	private function setUserId($user) {
		$this->user_id = $user;
		return true;
	}	
	private function getHostForSharedUrl($url) {
		$urlInfo = parse_url($url);
			if(isset($urlInfo['host'])) {
				return $urlInfo['host'];
			} else {
				return "";
			}
	}
	private function createUserInDatabaseAndSetProperties() {

		$this->databaseConnection->queryDatabaseWithBinds("
		INSERT INTO 
		user
		VALUES('',?,?,?,NOW());", 
		array(1 => $this->twitter_credentials->screen_name, 2 => $this->twitter_credentials->created_at, 3 => $this->twitter_user_id));
		if($this->seeIfUserIsCreatedInDatabaseAndSetProperties()) {
			return true;	
		}
		else {
			return false;
		}
	}
	private $databaseConnection = NULL;
	private $preparedStatement = NULL;
	private $user_id = NULL;
	private $twitter_credentials = NULL;
	private $twitter_connection = NULL;
	private $twitter_user_id = NULL;	
}