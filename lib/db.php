<?php

namespace CrossORM;

require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/helpers.php';
require_once dirname(__FILE__) . '/exceptions/exception.php';
require_once dirname(__FILE__) . '/exceptions/acl.php';
require_once dirname(__FILE__) . '/exceptions/validation.php';
require_once dirname(__FILE__) . '/interfaces/orm.php';
require_once dirname(__FILE__) . '/interfaces/model.php';
require_once dirname(__FILE__) . '/builder.php';
require_once dirname(__FILE__) . '/validation.php';
require_once dirname(__FILE__) . '/orm.php';
require_once dirname(__FILE__) . '/model.php';
require_once dirname(__FILE__) . '/resultset.php';
require_once dirname(__FILE__) . '/acl.php';

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
		
		$driver = basename($config['driver']); // no sneaking around
		$path 	= dirname(__FILE__) . '/../drivers/' . $driver . '/';
		$class  = '\\CrossORM\\Drivers\\'.strtoupper($driver).'\\ORM';
		
		if ( ! file_exists($path . 'orm.php') || ! file_exists($path . 'model.php'))
		{
			throw new Exception('Specified DB driver does not exist: '.$driver);
		}
		
		require_once $path . 'orm.php';
		
		if ( ! class_exists($class))
		{
			throw new Exception('Specified DB driver was not found: '.$driver);
		}
		
		return new $class($config, DB_ID_DEFAULT);
		
	}
	
}