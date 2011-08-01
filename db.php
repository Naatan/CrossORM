<?php

namespace CrossORM;

require_once dirname(__FILE__) . '/exception.php';
require_once dirname(__FILE__) . '/vendor/idiorm/idiorm.php';
require_once dirname(__FILE__) . '/vendor/paris/paris.php';

class DB
{
	
	protected static $connections = array();
	
	protected static $active_connection;
	
	protected static $default_config;
	
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
				$id = 'default';
			}
			else
			{
				$id = static::$active_connection;
			}
		}
		
		if ( !  isset(static::$connections[$id]))
		{
			
			if (empty($config))
			{
				if (empty(static::$default_config))
				{
					throw new Exception('No configuration specified');
				}
				else
				{
					$config = static::$default_config;
				}
			}
			
			static::$connections[$id] = static::driver_init($config);
		}
		
		return static::$connections[$id];
		
	}
	
	public static function configure(array $config) {
		static::$default_config = $config;
	}
	
	private static function driver_init(array $config)
	{
		
		if ( ! isset($config['driver']))
		{
			throw new Exception('No DB driver specified');
		}
		
		$driver = basename($config['driver']); // no sneaking around
		$class  = 'CrossORM\\Drivers\\'.$driver;
		
		if ( ! class_exists($class))
		{
			
			$path 	= dirname(__FILE__) . '/drivers/' . $driver . '.php';
			
			if ( ! file_exists($path))
			{
				throw new Exception('Specified DB driver was not found: '.$driver);
			}
			
			require_once $path;
			
			if ( ! class_exists($class))
			{
				throw new Exception('Specified DB driver was not found: '.$driver);
			}
			
		}
		
		return new $class($config);
		
	}
	
}