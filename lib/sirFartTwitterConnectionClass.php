<?php
require_once("twitteroauth.php");

class sirFartTwitterConnectionClass {
	private $oauth_token;
	private $oauth_token_secret;
	private $twitterauth_id;
	private $twitter_Connection;
	private $databaseConnection;
	private $twitter_Consumer_Key;
	private $twitter_Consumer_Secret;

	public function __construct($twitterauth_id, $databaseConnection, $twitter_Consumer_Key, $twitter_Consumer_Secret) {
		$this->databaseConnection = $databaseConnection;
		$this->twitterauth_id = $twitterauth_id;
		$this->twitter_Consumer_Key = $twitter_Consumer_Key;
		$this->twitter_Consumer_Secret = $twitter_Consumer_Secret;
		if($this->setOauthTokensFromDatabaseForCurrentUser($this->twitterauth_id)) {
			$this->twitter_Connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->oauth_token, $this->oauth_token_secret);
		}
	}
	public function setOauthTokensFromDatabaseForCurrentUser($twitterauth_id) {
		$this->databaseConnection->queryDatabaseWithBinds("
		select * from twitterauth where twitter_user_id = ? order by id DESC limit 1;", array(1 => $twitterauth_id));
		while($row = $this->databaseConnection->getQueryRow()) {
			$this->oauth_token = $row['token'];
			$this->oauth_token_secret = $row['token_secret'];
			return true;
		}
		return false;
	}
	public function getTwitterAPIcontent($APICall) {
		return $this->twitter_Connection->get($APICall);
	}
	public function getTwitterUserId() {
		return $this->twitterauth_id;
	}
}