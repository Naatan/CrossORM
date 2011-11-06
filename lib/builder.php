<?php

namespace CrossORM;

class Builder {
	
	private $query_type		= SELECT;
	
	private $affected_fields = array();
	
	public $id_column 		= ID_COLUMN;
	
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
	
	public function get_query_dump()
	{
		return array(
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
	
	public function query_type($type = null)
	{
		if ($type == null)
		{
			return $this->query_type;
		}
		
		return $this->query_type = $type;
	}
	
	public function id_column($field = null)
	{
		if ($field == null)
		{
			return $this->id_column;
		}
		
		return $this->id_column = $field;
	}
	
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
	
	public function _use_field($field)
	{
		return $this->_use_fields(array($field));
	}
	
	public function _use_fields($fields)
	{
		return $this->affected_fields = array_unique(
			array_merge(
				$this->affected_fields,
				$fields
			)
		);
	}
	
	public function clause($column_name = null, $separator, $value)
	{
		if ($column_name == null)
		{
			return $this->clause;
		}
		
		return $this->clauses(array($column_name, $separator, $value));
	}
	
	public function clauses($clauses = null)
	{
		if ($clauses == null)
		{
			return $this->clause;
		}
		
		$this->clause = array_merge($this->clause, $clauses);
		
		foreach ($clauses AS $clause)
		{
			$this->_use_field($clause[0]);
		}
		
		return $this->clause;
	}
	
	public function table($table_name = null)
	{
		if ($table_name == null)
		{
			return $this->table;
		}
		
		return $this->table = $table_name;
	}
	
	public function table_alias($table_name = null)
	{
		if ($table_name == null)
		{
			return $this->table_alias;
		}
		
		return $this->table_alias = $table_name;
	}
	
	public function limit($limit = null)
	{
		if ($limit == null)
		{
			return $this->limit;
		}
		
		return $this->limit = $limit;
	}
	
	public function offset($offset = null)
	{
		if ($offset == null)
		{
			return $this->offset;
		}
		
		return $this->offset = $offset;
	}
	
	public function order_by($column_name = null, $dir = null)
	{
		if ($column_name == null)
		{
			return $this->orderby;
		}
		
		$this->_use_field($column_name);
		
		return $this->orderby[] = array($column_name, $dir);
	}
	
	public function group_by($column_name = null)
	{
		if ($column_name == null)
		{
			return $this->groupby;
		}
		
		$this->_use_field($column_name);
		
		return $this->groupby[] = $column_name;
	}
	
	public function reset_set()
	{
		foreach ($this->set AS $k => $v)
		{
			$this->affected_fields = $this->_array_unset_value($this->affected_fields,$k);
		}
		
		return $this->set = array();
	}
	
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
	
	public function get($key)
	{
		return isset($this->set[$key]) ? $this->set[$key] : UNDEFINED;
	}
	
	private function _array_unset_value($array,$value)
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