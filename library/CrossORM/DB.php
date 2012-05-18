<?php

namespace CrossORM;

use CrossORM\Exceptions\Exception;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Constants.php';

/**
 * Main DB class, this is where it all starts
 */
class DB
{
	
	protected static $connections = array();
	
	protected static $active_connection;
	
	protected static $default_config;
	
	/**
	 * Factory method, used to get / initiate db instances
	 * 
	 * @param	int|array|null			$id			If this is an array it will be used as the config and $config will be ignored
	 * @param	array|null				$config
	 * 
	 * @return	object	Returns the driver that was initiated based on the config
	 */
	public static function factory($id = null, $config = null)
	{
		
		if (is_array($id))
		{
			$config = $id;
			$id = null;
		}
		
		if ($id == null)
		{
			if ( ! static::$active_connection)
			{
				$id = DB_ID_DEFAULT;
			}
			else
			{
				$id = static::$active_connection;
			}
		}
		
		if ( ! isset(static::$connections[$id]) )
		{
			if (empty($config))
			{
				if (empty(static::$default_config))
				{
					throw new Exception('No configuration specified');
				}
				else
				{
					static::$connections[$id] = static::$default_config;
				}
			} else
			{
				static::$connections[$id] = $config;
			}
		}
		
		return static::driver_init(static::$connections[$id], $id);
		
	}
	
	/**
	 * Initialize a DB driver
	 * 
	 * @param	array			$config			
	 * @param	string			$id
	 * 
	 * @return	object
	 */
	private static function driver_init(array $config, $id = DB_ID_DEFAULT)
	{
		
		if ( ! isset($config['driver']))
		{
			throw new Exception('No DB driver specified');
		}
		
		$driver = basename(ucfirst(strtolower($config['driver']))); // no sneaking around
		$class  = '\\CrossORM\\Drivers\\'.$driver.'\\ORM';
		
		if ( ! class_exists($class))
		{
			throw new Exception('Specified DB driver was not found: '.$driver);
		}
		
		return new $class($config, $id);
		
	}
	
}