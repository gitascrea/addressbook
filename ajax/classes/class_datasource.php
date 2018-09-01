<?php
require_once __DIR__.'/class_base.php';
/**
 * helper class for doing Database Conection
 *
 * @package     Datasource
 * @author      Antonio Scarfone
 * @copyright   2018 Coeln Concept 
 */

/**
 * Class Datasource -> Create Database connection
 */
class Datasource extends Base {
	// Connection variables 
	/**
     * @var $host
     */
	private static $host = "localhost"; // MySQL host name eg. localhost
	/**
     * @var $user
     */
	private static $user = "root"; // MySQL user. eg. root ( if your on localserver)
	/**
     * @var $password
     */
	private static $password = ""; // MySQL user password  (if password is not set for your root user then keep it empty )
	/**
     * @var $database
     */
	private static $database = "test"; // MySQL Database name
	/**
     * @var $table
     */
	private static $table = "users"; // MySQL Table name

	/**
     * @var Database Connection PDO
     */
	private static $db = null;

	/**
     * constructor
     *
     */
    public function __construct() {
		parent::__construct();
		session_start();
		if (!isset($_SESSION['check_table_exists'])) $_SESSION['check_table_exists'] = false;
		self::$db = self::getConnection();
	}

	/**
     * Create Database
     *
     */
    public static function createDatabase($db) {
		$query = "CREATE DATABASE IF NOT EXISTS " . self::$database . " COLLATE utf8_unicode_ci;";
		if (!$result = mysqli_query($db, $query)) {
			exit(mysqli_error($db));
		} else {
			mysqli_select_db($db, self::$database);
		}
	}

	/**
     * Create Table
     *
     */
    public static function createTable($db) {
		$query_check = "SHOW TABLES where Tables_in_" . self::$database . "='" . self::$table . "'";
		$checktable = mysqli_query($db, $query_check);
		$table_exists = mysqli_num_rows($checktable) > 0;
		if (!$table_exists) {
			$query_create = "CREATE TABLE  `".self::$table."` (
								`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
								`first_name` VARCHAR( 40 ) NOT NULL ,
								`last_name` VARCHAR( 40 ) NOT NULL ,
								`email` VARCHAR( 50 ) NOT NULL
							) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
			$createtable = mysqli_query($db, $query_create);
		}
		$_SESSION['check_table_exists'] = true;
	}
	
	/**
     * singleton getter Database connection
     *
     * @return $db connection
     */
    public static function getConnection() {
		session_start();
		if (!isset($_SESSION['check_table_exists'])) $_SESSION['check_table_exists'] = false;
		if (self::$db === null) {
			$db = mysqli_connect(self::$host, self::$user, self::$password) or die("Konnte keine Verbindung zur Datenbank herstellen!");
			// Select MySQL Database 
			$db_selected = mysqli_select_db($db, self::$database);
			if (!$db_selected) {
				//Database seems not to exist, so create it
				self::createDatabase($db);
			}
			//check table if exists, and if not so create
			if (!$_SESSION['check_table_exists']) self::createTable($db);
			return $db;
		} else {
			return self::$db;
		}
	}
	
	
	
}
?>