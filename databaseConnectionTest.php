<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);
require_once("../config.php");
require_once("lib/sirFartDatabaseClass.php");
echo "hei";
$dbh = new sirFartDatabaseClass(PDO_CONNECTION, DB_USERNAME, DB_PASSWORD);
$dbh->queryDatabaseWithBinds("select * from sharelog where user_id = ? limit 1;",
	array(1 => "1"));
print_r($dbh->getQueryRow());
$dbh->endConnection();
?>	