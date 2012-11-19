<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
class sirFartDatabaseClass {
	private $databaseConnection = NULL;
	private $preparedStatement = NULL;

	public function __construct($connection, $user, $pass) {
		$this->databaseConnection = new PDO($connection, $user, $pass);
	}

	public function queryDatabaseWithBinds($query,$arrayofAttributesNumbered) {
		$preparedStatement = $this->databaseConnection->prepare($query);
		foreach($arrayofAttributesNumbered as $number => $attribute) {
			$preparedStatement->bindParam($number, $arrayofAttributesNumbered[$number]);
		}
		$preparedStatement->execute();
		$this->preparedStatement = $preparedStatement;
	}
	public function queryDatabaseWithoutBinds($query) {
		$preparedStatement = $this->databaseConnection->prepare($query);
		$preparedStatement->execute();
		$this->preparedStatement = $preparedStatement;
	}	
	public function getQueryRow() {
		return $this->preparedStatement->fetch();
	}
	public function endConnection() {
		$this->preparedStatement = NULL;
		$this->databaseConnection = NULL;
	}
}