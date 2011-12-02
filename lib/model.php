<?php

namespace CrossORM;

/**
 * Model class, wraps around the ORM
 */
class Model
{
	
	protected $orm;
	
	protected $table_name 	= null;
	protected $db_id		= null;
	
	/**
	 * Constructor
	 * 
	 * @returns	$this	
	 */
	public function __construct($id = null, $config = null)
	{
		if ($id == null AND $this->db_id != null)
		{
			$id = $this->db_id;
		}
		
		$table_name = $this->_get_table_name();
		
		$this->orm = DB::factory($id, $config);
		$this->orm->table($table_name);
		
		return $this;
	}
	
	/**
	 * Method overloading, forward unknown calls to the ORM
	 * 
	 * @param	string			$method			
	 * @param	array			$args
	 * 
	 * @returns	mixed						
	 */
	public function __call($method,$args)
	{
		return call_user_func_array(array($this->orm,$method),$args);
	}
	
	/**
	 * Forward __get calls to ORM
	 * 
	 * @param	string			$key
	 * 
	 * @returns	mixed							
	 */
	public function __get($key)
	{
		return $this->orm->{$key};
	}
	
	/**
	 * Forward __set calls to ORM
	 * 
	 * @param	string			$key			
	 * @param	mixed			$value
	 * 
	 * @returns	mixed							
	 */
	public function __set($key,$value)
	{
		return $this->orm->{$key} = $value;
	}
	
	/**
	 * Forward __isset calls to ORM
	 * 
	 * @param	string			$key
	 * 
	 * @returns	bool							
	 */
	public function __isset($key)
	{
		return isset($this->orm{$key});
	}
	
	/**
	 * Instantiate new instance of model
	 * 
	 * @returns	object							
	 */
	public static function factory()
	{
		$class_name = get_called_class();
		return new $class_name;
	}
	
	/**
	 * Static method to get a table name given a class name.
	 * If the supplied class has a public static property
	 * named $_table, the value of this property will be
	 * returned. If not, the class name will be converted using
	 * the _class_name_to_table_name method method.
	 */
	protected function _get_table_name()
	{
		if ( !empty($this->table_name))
		{
			return $this->table_name;
		}
		
		$class_name = get_class($this);
		return static::_class_name_to_table_name($class_name);
	}
	
	/**
	 * Static method to convert a class name in CapWords
	 * to a table name in lowercase_with_underscores.
	 * For example, CarTyre would be converted to car_tyre.
	 */
	public static function _class_name_to_table_name($class_name)
	{
		$table_name = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', basename($class_name)));
		
		if (substr($table_name,0,6)=='model_')
		{
			$table_name = substr($table_name,6);
		}
		
		if (substr($table_name,-6)=='_model')
		{
			$table_name = substr($table_name,0,-6);
		}
		
		return $table_name;
	}
	
}