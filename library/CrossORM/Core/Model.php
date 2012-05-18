<?php

namespace CrossORM\Core;

use \CrossORM\DB,
	\CrossORM\Exceptions,
	\CrossORM\Helpers,
	\CrossORM\Security\Validation;

/**
 * Model class, wraps around the ORM
 */
abstract class Model
{
	
	protected $orm;
	
	protected $table_name 	= null;
	protected $db_id		= null;
	protected $config		= null;
	
	protected $id_field 	= 'id';
	protected $fields		= array();
	
	protected $_validate_on = \CrossORM\VALIDATE_ON_ALL;

	protected $dynamic_methods 	= array();

	private $_dynamic_methods 	= array(
		'where_%field%'					=> array('where_equal', 	'%field%', '%value%'),
		'where_%field%_not'				=> array('where_not_equal', '%field%', '%value%'),
		'where_%field%_gt'				=> array('where_gt', 		'%field%', '%value%'),
		'where_%field%_gte'				=> array('where_gte', 		'%field%', '%value%'),
		'where_%field%_lt'				=> array('where_lt', 		'%field%', '%value%'),
		'where_%field%_lte' 			=> array('where_lte', 		'%field%', '%value%'),
		'where_%field%_is_null'			=> array('where_null', 		'%field%', '%value%'),
		'where_%field%_is_not_null' 	=> array('where_not_null', 	'%field%', '%value%'),
		'where_%field%_in' 				=> array('where_in', 		'%field%', '%value%'),
		'where_%field%_like'			=> array('where_like', 		'%field%', '%value%'),
		'where_%field%_not_like' 		=> array('where_not_like', 	'%field%', '%value%'),

		'order_by_%field%' 				=> array('order_by_desc', 	'%field%'),
		'order_%field%_desc' 			=> array('order_by_desd', 	'%field%'),
		'order_%field%_asc'				=> array('order_by_asc', 	'%field%'),

		'group_by_%field%'				=> array('group_by', 		'%field%')
	);

	private $_method_hooks  	= array();
	
	/**
	 * Constructor
	 * 
	 * @return	$this	
	 */
	public function __construct($id = NULL, $config = NULL, $orm = NULL)
	{

		// Detect DB ID to use
		if ($id == NULL AND $this->db_id != NULL)
		{
			$id = $this->db_id;
		}
		
		$this->db_id 	= $id;
		$this->config 	= $config;
		$table_name 	= $this->_get_table_name();
		
		// Initiate ORM
		if ($orm == NULL)
		{
			$this->orm 	= DB::factory($id, $config);
			$this->orm->table($table_name);
		}
			else
		{
			$this->orm 	= $orm;
		}

		$this->prepare_fields();

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
		if (isset($this->_method_hooks[$method]))
		{
			
			$hook 		= $this->_method_hooks[$method];
			$method 	= array_shift($hook->args);

			$replace 	= array(
				'%field%'	=> $hook->field,
				'%value%'	=> count($args) > 0 ? $args[0] : NULL,
				'%values%'	=> $args
			);

			for ($i=0; $i < count($args); $i++)
			{ 
				$replace['%value'.($i+1).'%'] = $args[$i];
			}

			foreach ($hook->args AS &$argument)
			{
				$argument = isset($replace[$argument]) ? $replace[$argument] : NULL;
			}

			$result = call_user_func_array(array($this,$method),$hook->args);

		}
		else
		{

			if (in_array($method,array('run','save')))
			{
				$this->_validate_fields();
			}

			$result = call_user_func_array(array($this->orm,$method),$args);

		}
		
		if ($result instanceof ORM)
		{
			$this->orm = $result;
			return $this;
		}
			else
		{
			return $result;
		}
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
		
		if (in_array($this->_validate_on, array(\CrossORM\VALIDATE_ON_ALL, \CrossORM\VALIDATE_ON_SET)) AND isset($this->fields->{$key}))
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

	private function prepare_fields() 
	{
		$this->fields 		= Helpers::objectify($this->fields);
		$dynamic_methods 	= array_merge($this->_dynamic_methods,$this->dynamic_methods);

		foreach ($this->fields AS $field => $data)
		{
			foreach ($dynamic_methods AS $method => $hook)
			{
				$method 						= str_replace('%field%', $field, $method);
				$this->_method_hooks[$method] 	= (object) array('field' => $field, 'args' => $hook);
			}
		}
	}

	public function find_one()
	{
		$this->orm = $this->orm->find_one();
		return $this;
	}
	
	public function find_many()
	{
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
			if (in_array($this->_validate_on,array(\CrossORM\VALIDATE_ON_ALL,\CrossORM\VALIDATE_ON_RUN)) AND isset($this->fields->{$key}))
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