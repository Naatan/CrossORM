<?php

namespace CrossORM;

/**
 * Builder class
 *
 * Prepares the query structure so that the driver can then build it however it wants
 */
class Builder {
	
	private $query_type		= INSERT;
	
	private $affected_fields = array();
	
	private $id_column 		= ID_COLUMN;
	
	private $table 			= '';
	private $table_alias	= '';
	private $joins 			= array();
	private $select 		= array();
	private $set 			= array();
	private $id				= '';
	private $clause 		= array();
	private $orderby 		= array();
	private $groupby 		= array();
	private $offset 		= NULL;
	private $limit			= NULL;
	
	/**
	 * Contructor
	 * 
	 * @returns	void							
	 */
	public function __construct()
	{
		$this->select('*');
	}
	
	/**
	 * Get the current query elements, mostly for debugging purposes
	 * 
	 * @returns	object							
	 */
	public function get_query_dump()
	{
		return (object) array(
			'query_type'		=> $this->query_type,
			'affected_fields'	=> $this->affected_fields,
			'id_column'			=> $this->id_column,
			'tables'			=> $this->tables,
			'select'			=> $this->select,
			'set'				=> $this->set,
			'id'				=> $this->id,
			'clause'			=> $this->clause,
			'orderby'			=> $this->orderby,
			'orderdir'			=> $this->orderdir,
			'groupby'			=> $this->groupby,
			'offset'			=> $this->offset,
			'limit'				=> $this->limit,
		);
	}
	
	/**
	 * Get fields being affected in this query, useful for ACL validation
	 * 
	 * @returns	array							
	 */
	public function get_affected_fields()
	{
		return $this->affected_fields;
	}
	
	/**
	 * Set / get the type of query being executed
	 * 
	 * @param	string|null			$type
	 * 
	 * @returns	string
	 */
	public function query_type($type = null)
	{
		if ($type == null)
		{
			return $this->query_type;
		}
		
		return $this->query_type = $type;
	}
	
	/**
	 * Set / get the id column
	 * 
	 * @param	string|null			$field
	 * 
	 * @returns	string							
	 */
	public function id_column($field = null)
	{
		if ($field == null)
		{
			return $this->id_column;
		}
		
		return $this->id_column = $field;
	}
	
	/**
	 * Set / get the select fields
	 * 
	 * @param	string|array|null			$select
	 * 
	 * @returns	array							
	 */
	public function select($select = null)
	{
		if ($select == null)
		{
			return $this->select;
		}
		
		if ( ! is_array($select))
		{
			$select = array($select);
		}
		
		foreach ($select AS $field)
		{
			if (is_array($field))
			{
				$this->_use_field($field[0]);
			} else 
			{
				$this->_use_field($field);
			}
		}
		
		return $this->select = array_merge($this->select,$select);
	}
	
	/**
	 * Set / get the conditional clause
	 * 
	 * @param	string|null			$column_name	
	 * @param	string				$separator		
	 * @param	string|int			$value
	 * 
	 * @returns	array							
	 */
	public function clause($column_name = null, $separator, $value)
	{
		if ($column_name == null)
		{
			return $this->clause;
		}
		
		if ($column_name == $this->id_column() AND $this->limit() == NULL)
		{
			$this->limit(1);
		}
		
		$this->_use_field($column_name);
		
		return $this->clause[] = array($column_name, $separator, $value);
	}
	
	/**
	 * Set / get conditional clauses
	 * 
	 * @param	array|null			$clauses
	 * 
	 * @returns	array							
	 */
	public function clauses($clauses = null)
	{
		if ($clauses == null)
		{
			return $this->clause;
		}
		
		foreach ($clauses AS $clause)
		{
			$this->clause($clause);
		}
		
		return $this->clause;
	}
	
	/**
	 * Set / get the table to be used
	 * 
	 * @param	string|null			$table_name
	 * 
	 * @returns	string						
	 */
	public function table($table_name = null)
	{
		if ($table_name == null)
		{
			return $this->table;
		}
		
		return $this->table = $table_name;
	}
	
	/**
	 * Set / get the table alias
	 * 
	 * @param	string|null			$table_name
	 * 
	 * @returns	string							
	 */
	public function table_alias($table_name = null)
	{
		if ($table_name == null)
		{
			return $this->table_alias;
		}
		
		return $this->table_alias = $table_name;
	}
	
	/**
	 * Set / get the limit
	 * 
	 * @param	int|null			$limit
	 * 
	 * @returns	int
	 */
	public function limit($limit = null)
	{
		if ($limit == null)
		{
			return $this->limit;
		}
		
		return $this->limit = (int) $limit;
	}
	
	/**
	 * Set / get the offset
	 * 
	 * @param	int|null			$offset			
	 * @returns	int							
	 */
	public function offset($offset = null)
	{
		if ($offset == null)
		{
			return $this->offset;
		}
		
		return $this->offset = (int) $offset;
	}
	
	/**
	 * Set / get an order by clause
	 * 
	 * @param	string|null			$column_name	
	 * @param	string|null			$dir
	 * 
	 * @returns	array							
	 */
	public function order_by($column_name = null, $dir = null)
	{
		if ($column_name == null)
		{
			return $this->orderby;
		}
		
		$this->_use_field($column_name);
		
		return $this->orderby[] = array($column_name, $dir);
	}
	
	/**
	 * Set / get a group by clause
	 * 
	 * @param	string|null			$column_name
	 * 
	 * @returns	array
	 */
	public function group_by($column_name = null)
	{
		if ($column_name == null)
		{
			return $this->groupby;
		}
		
		$this->_use_field($column_name);
		
		return $this->groupby[] = $column_name;
	}
	
	/**
	 * Set fields to be updated / inserted
	 * 
	 * @param	string|array|null			$entries		
	 * @param	string|int|null				$value
	 * 
	 * @returns	array							
	 */
	public function set($entries = null, $value = null)
	{
		if ($entries == null)
		{
			return $this->set;
		}
		
		if ( !is_array($entries))
		{
			$entries = array($entries => $value);
		}
		
		foreach ($entries AS $key => $value)
		{
			$this->set[$key] = $value;
			$this->_use_field($key);
		}
		
		return $this->set;
	}
	
	/**
	 * Get a value that is to be set
	 * 
	 * @param	string			$key
	 * 
	 * @returns	string		Can return the UNDEFINED constant if the key is not being set
	 */
	public function get($key)
	{
		return isset($this->set[$key]) ? $this->set[$key] : UNDEFINED;
	}
	
	/**
	 * Mark a specific field as used
	 * 
	 * @param	string			$field
	 * 
	 * @returns	array							
	 */
	public function _use_field($field)
	{
		return $this->_use_fields(array($field));
	}
	
	/**
	 * Mark specific fields as used
	 * 
	 * @param	array			$fields
	 * 
	 * @returns	array							
	 */
	public function _use_fields($fields)
	{
		return $this->affected_fields = array_unique(
			array_merge(
				$this->affected_fields,
				$fields
			)
		);
	}
	
	/**
	 * Unset array entries by value
	 * 
	 * @param	array				$array	
	 * @param	string|int			$value
	 * 
	 * @returns	array							
	 */
	private function _array_unset_value(array $array,$value)
	{
		if ( !in_array($value,$array))
		{
			return $array;
		}
		
		$numeric = isset($array[0]);
		
		foreach ($array AS $k => $entry)
		{
			if ($entry == $value)
			{
				unset($array[$k]);
			}
		}
		
		if ($numeric)
		{
			$array = array_values($array);
		}
		
		return $array;
	}
	
}