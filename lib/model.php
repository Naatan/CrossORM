<?php

namespace CrossORM;

/**
 * Model class, wraps around the ORM
 */
abstract class Model
{
	
	protected $orm;
	
	protected $table_name 	= null;
	protected $db_id		= null;
	protected $config		= null;
	
	protected $fields		= array();
	
	protected $_validate_on = VALIDATE_ON_ALL;
	
	/**
	 * Constructor
	 * 
	 * @return	$this	
	 */
	public function __construct($id = null, $config = null, $orm = null)
	{
		if ($id == null AND $this->db_id != null)
		{
			$id = $this->db_id;
		}
		
		$this->db_id = $id;
		$this->config = $config;
		
		$table_name = $this->_get_table_name();
		
		if ($orm == null)
		{
			$this->orm = DB::factory($id, $config);
			$this->orm->table($table_name);
		}
			else
		{
			$this->orm = $orm;
		}
		
		$this->fields = Helpers::objectify($this->fields);
		
		return $this;
	}
	
	/**
	 * Method overloading, forward unknown calls to the ORM
	 * 
	 * @param	string			$method			
	 * @param	array			$args
	 * 
	 * @return	mixed						
	 */
	public function __call($method,$args)
	{
		if (in_array($method,array('as_array','as_json')))
		{
			$this->_validate_fields();
		}

		return call_user_func_array(array($this->orm,$method),$args);
	}
	
	/**
	 * Forward __get calls to ORM
	 * 
	 * @param	string			$key
	 * 
	 * @return	mixed							
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
	 * @return	mixed							
	 */
	public function __set($key,$value)
	{
		if (count($this->fields) > 0 AND !isset($this->fields->{$key}))
		{
			throw new Exceptions\Validation('Trying to set field "' . (string) $key . '" which does not exist in model "' . (string) get_class($this) . '"');
		}
		
		if (in_array($this->_validate_on,array(VALIDATE_ON_ALL,VALIDATE_ON_SET)) AND isset($this->fields->{$key}))
		{
			$field = $this->fields->{$key};
			
			$rules  = isset($field->validation) ? $field->validation . ',' : '';
			$rules .= isset($field->type) ? $field->type : '';
			
			Validation::run(isset($field->label) ? $field->label : $key, $value, $rules, $this);
		}
		
		return $this->orm->{$key} = $value;
	}
	
	/**
	 * Forward __isset calls to ORM
	 * 
	 * @param	string			$key
	 * 
	 * @return	bool							
	 */
	public function __isset($key)
	{
		return isset($this->orm{$key});
	}
	
	/**
	 * Instantiate new instance of model
	 * 
	 * @return	object							
	 */
	public static function factory()
	{
		$class_name = get_called_class();
		return new $class_name;
	}
	
	public function find_one()
	{
		$this->_validate_fields();

		$this->orm = $this->orm->find_one();
		return $this;
	}
	
	public function find_many()
	{
		$this->_validate_fields();

		if ( ! $result = $this->orm->find_many())
		{
			return false;
		}
		
		$class 	= get_called_class();
		$rows 	= array();
		
		foreach ($result->get_rows() AS $row)
		{
			$rows[] = new $this($this->db_id, $this->config, $row);
		}
		
		return $rows;
	}

	protected function _validate_fields()
	{
		$fields = $this->orm->fields();

		foreach ($fields AS $key => $value)
		{
			if (in_array($this->_validate_on,array(VALIDATE_ON_ALL,VALIDATE_ON_RUN)) AND isset($this->fields->{$key}))
			{
				$field = $this->fields->{$key};
				
				$rules  = isset($field->validation) ? $field->validation . ',' : '';
				$rules .= isset($field->type) ? $field->type : '';
				
				Validation::run(isset($field->label) ? $field->label : $key, $value, $rules, $this);
			}
		}
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