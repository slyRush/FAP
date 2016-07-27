<?php
/**
 * Connection properties
 *
 * @author: http://phpdao.com
 * @date: 27.11.2007
 */

require_once dirname(__DIR__) . '/config.php';

class ConnectionProperty
{
	/*private static $host = 'localhost';
	private static $user = 'root';
	private static $password = 'root';
	private static $database = 'api_application';*/

	private static $host = HOST;
	private static $user = USER;
	private static $password = PASSWORD;
	private static $database = DATABASE;

	public static function getHost()
	{
		return ConnectionProperty::$host;
	}

	public static function getUser()
	{
		return ConnectionProperty::$user;
	}

	public static function getPassword()
	{
		return ConnectionProperty::$password;
	}

	public static function getDatabase(){
		return ConnectionProperty::$database;
	}
}