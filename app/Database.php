<?php namespace Core;

use PDO;

/**
 * Database class that will hold connection and manage PDO statements
 *
 * @author Samuel Bartík
 * @version 0.1
 * @copyright Copyright (c) 2018, Samuel Bartík
 */
class Database {
	/**
   * @var PDO $conn - Holds current connection.
   */
	private static $conn;

	/**
   * @var array $settings - Holds current connection settings.
   */
	private static $settings = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false,
	);

	/**
	 * @var PDOStatement $lastUsedStatement - Holds a reference to last used statement.
	 */
	private static $lastUsedStatement;

	/**
	 * Connects to the database, if not already.
	 *
	 * @param string $host - Hostname or I.P. address
	 * @param string $dbname - Database name
	 * @param string $user - Username
	 * @param string $password - Password
	 * @param string $dsn - Optional, default mysql
	 */
	public static function connect($host, $dbname, $user, $password, $dsn = 'mysql') {
		if(!isset(self::$conn))
			self::$conn = new PDO("$dsn:host=$host;dbname=$dbname", $user, $password, self::$settings);
	}

	/**
	 * Queries database
	 *
	 * @param string $sql - The SQL used for this query
	 * @param string $params - Oprional, parameters that will replace question marks
	 *
	 * @return array $result - Result set in form of associative array
	 * @return void If connection wasn't set, or if connection is null(closed)
	 */
	public static function query($sql, $params = []){
		if(!isset(self::$conn) || self::$conn == null) return;
		$stmt = null;

		if(isset(self::$lastUsedStatement) && self::$lastUsedStatement != null){
			\ob_start();
			self::$lastUsedStatement->debugDumpParams();
			$lastSQL = \ob_get_clean();
			$lastSQL = str_replace("\n", " ", $lastSQL);
			$lastSQL = preg_match("/SQL: \[.*\] (.*) Params:/", $lastSQL, $matches);
			$lastSQL = $matches[1];

			if($lastSQL === $sql)
				$stmt = self::$lastUsedStatement;
			else
				self::$lastUsedStatement->closeCursor();
		}

		if($stmt == null){
			$stmt = self::$conn->prepare($sql);
			self::$lastUsedStatement = &$stmt;
		}

		$stmt->execute($params);
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}

}
