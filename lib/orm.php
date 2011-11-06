<?php

namespace CrossORM;

abstract class ORM
{
	
	protected static $_connections;
	
	protected $_conn;
	protected $_config;

	protected $_build;
	
	protected $_state = STATE_FRESH;
	
	protected $_fields = array();
	
	protected $_last_query;
	protected $_last_query_result;
	
	/************************************************** INITIALIZATION */
	
	/**
	 * Initiate a new instance of the ORM, and create the db connection
	 * if it does not already exist
	 * 
	 * @param	array|object			$config
	 * 
	 * @return $this							
	 */
	function __construct($config)
	{
		$this->_config = (object) $config;
		
		$identifier = sha1($this->_config->connection_string);
		
		if (isset(static::$_connections[$identifier]))
		{
			$this->_conn = static::$_connections[$identifier];
		} else
		{
			$this->_conn = $this->connect();
			static::$_connections[$identifier] = $this->_conn;
		}
		
		$this->_build = new Builder;
		
		return $this;
	}
	
	/**
	 * Create a new instance
	 */
	public function create($data=null)
	{
		$this->_build->query_type(INSERT);
		
		$class_name = get_class($this);
		
		$instance = new $this($this->_config);
		$instance->table($this->_build->table());
		$instance->id_column($this->_build->id_column);
		
		if ( !empty($data) )
		{
			$instance->hydrate($data);
			$instance->state(STATE_HYDRATED);
		}
		
		return $instance;
	}
	
	public function hydrate($data=array()) {
		$this->set($data);
		
		return $this;
	}
	
	protected function _create_instance_from_row($row) {
		$result = $this->create($row);
		$this->_build->reset_set();
		
		return $result;
	}
	
	public function state($state = null) {
		if ($state == null)
		{
			return $this->_state;
		}
		
		return $this->_state = $state;
	}
	
	/************************************************** OVERLOADING */
	
	public function __get($key) {
		return $this->get($key);
	}

	public function __set($key, $value) {
		$this->set($key, $value);
	}

	public function __isset($key) {
		return isset($this->_fields[$key]);
	}
	
	/************************************************** BUILDING QUERIES */
	
	/**
	 * Alias of @see table()
	 * 
	 * @param	string			$table_name		
	 * @return	$this
	 */
	function for_table($table_name)
	{
		return $this->table($table_name);
	}
	
	/**
	 * Define table name to base queries on
	 * 
	 * @param	string			$table_name		
	 * @return	$this
	 */
	function table($table_name)
	{
		$this->_build->table($table_name);
		
		return $this;
	}

	/**
	 * Change the id column name
	 * 
	 * @param	string			$id_column
	 * @return	$this							
	 */
	function id_column($id_column)
	{
		$this->_build->id_column($id_column);
		
		return $this;
	}
	
	/**
	 * Alias the table we're selecting from
	 * 
	 * @param	string			$alias			
	 * @return	$this						
	 */
	function table_alias($alias)
	{
		$this->_build->table_alias($alias);
		
		return $this;
	}
	
	/**
	 * Select one or multiple fields
	 *
	 * Any of the following will work as input
	 *
	 * * field1
	 * * array(field1, alias_for_field1)
	 * * array(field1,field2,field3)
	 * * array(array(field1,alias_for_field1),array(field2,alias_for_field2))
	 * 
	 * @param	array|string			$select			
	 * @return	$this							
	 */
	function select($select)
	{
		$this->_build->select($column);
		
		return $this;
	}
	
	/**
	 * Add standard where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where($column_name, $value)
	{
		$this->_build->clause($column_name, EQUAL, $value);
		
		return $this;
	}
	
	/**
	 * Add standard where clause statement, links to @see where()
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_equal($column_name, $value)
	{
		return $this->where($column_name, $value);
	}
	
	/**
	 * Add "is not" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_not_equal($column_name, $value)
	{
		$this->_build->clause($column_name, NOT_EQUAL, $value);
		
		return $this;
	}
	
	/**
	 * Get entry by id
	 * 
	 * @param	int			$id				
	 * @return	$this							
	 */
	function where_id_is($id)
	{
		$this->_build->clause($this->_build->id_column, EQUAL, $id);
		
		return $this;
	}
	
	/**
	 * Add "like" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_like($column_name, $value)
	{
		$this->_build->clause($column_name, LIKE, $value);
		
		return $this;
	}
	
	/**
	 * Add "not like" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_not_like($column_name, $value)
	{
		$this->_build->clause($column_name, NOT_LIKE, $value);
		
		return $this;
	}
	
	/**
	 * Add "greater than" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_gt($column_name, $value)
	{
		$this->_build->clause($column_name, GREATER_THAN, $value);
		
		return $this;
	}
	
	/**
	 * Add "less than" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_lt($column_name, $value)
	{
		$this->_build->clause($column_name, LESS_THAN, $value);
		
		return $this;
	}
	
	/**
	 * Add "greater than or equal" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_gte($column_name, $value)
	{
		$this->_build->clause($column_name, GREATER_THAN_EQUAL, $value);
		
		return $this;
	}
	
	/**
	 * Add "less than or equal" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_lte($column_name, $value)
	{
		$this->_build->clause($column_name, LESS_THAN_EQUAL, $value);
		
		return $this;
	}
	
	/**
	 * Add "IN" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_in($column_name, $values)
	{
		$this->_build->clause($column_name, IN, $value);
		
		return $this;
	}
	
	/**
	 * Add "NOT IN" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_not_in($column_name, $values)
	{
		$this->_build->clause($column_name, NOT_IN, $value);
		
		return $this;
	}
	
	/**
	 * Add "IS NULL" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_null($column_name)
	{
		$this->_build->clause($column_name, IS_NULL, $value);
		
		return $this;
	}
	
	/**
	 * Add "IS NOT NULL" where clause statement
	 * 
	 * @param	string					$column_name	
	 * @param	string|int|float			$value
	 * @return	$this
	 */
	function where_not_null($column_name)
	{
		$this->_build->clause($column_name, IS_NOT_NULL, $value);
		
		return $this;
	}
	
	/**
	 * Limit results
	 * 
	 * @param	int			$limit			
	 * @return	$this							
	 */
	function limit($limit)
	{
		$this->_build->limit($limit);
		
		return $this;
	}
	
	/**
	 * Offset search entries
	 * 
	 * @param	int			$offset			
	 * @return	$this							
	 */
	function offset($offset)
	{
		$this->_build->offset($offset);
		
		return $this;
	}
	
	/**
	 * Order field descending
	 * 
	 * @param	string			$column_name	
	 * @return	$this							
	 */
	function order_by_desc($column_name)
	{
		$this->_build->order_by($column_name, DESC);
	}
	
	/**
	 * Order field ascending
	 * 
	 * @param	string			$column_name	
	 * @return	$this							
	 */
	function order_by_asc($column_name)
	{
		$this->_build->order_by($column_name, ASC);
	}
	
	/**
	 * Group results
	 * 
	 * @param	string			$column_name	
	 * @return	$this							
	 */
	function group_by($column_name)
	{
		$this->_build->group_by($column_name);
	}

	/**
	 * Set a field value
	 * 
	 * @param	string|array			$key			
	 * @param	string|int|float|null	$value			
	 * @return	$this					
	 */
	function set($key, $value = null)
	{
		if (!is_array($key))
		{
			$key = array($key => $value);
		}
		
		foreach ($key AS $k => $v)
		{
			$this->_fields[$k] = $v;
		}
		
		$this->_build->set($key, $value);
		
		return $this;
	}
	
	/************************************************** EXECUTION */
	
	public function save()
	{
		if ($this->_build->query_type() != INSERT)
		{
			$this->_build->query_type(UPDATE);
		}
		
		return $this->build()->run();
	}
	
	public function delete()
	{
		$this->_build->query_type(DELETE);
		
		return $this->build()->run();
	}
	
	/************************************************** RESULTS */
	
	public function get($key) {
		return isset($this->_fields[$key]) ? $this->_fields[$key] : null;
	}
	
	
	function as_array()
	{
		if ( $this->state() == STATE_FRESH)
		{
			return $this->_get_rows();
		} else
		{
			return $this->_fields;
		}
	}

	function find_one()
	{
		$this->_build->limit(1);
		
		return $this->_get_row(true);
	}

	function find_many()
	{
		return $this->_get_rows(true);
	}
	
	function id()
	{
		if ( $this->state() == STATE_FRESH)
		{
			return $this->find_one()->{$this->_build->id_column()};
		} else
		{
			return $this->_fields->{$this->_build->id_column()};
		}
	}
	
}