<?php

namespace CrossORM;

/**
 * Resultset class, used to allow method calling based on a result set and not just on individual results
 */
class Resultset {
	
	private $_results;
	private $_rows;
	
	/**
	 * Constructor, hydrates itself with given rows
	 * 
	 * @param	array			$rows
	 * 
	 * @return	$this							
	 */
	function __construct($rows)
	{
		if ( ! is_array($rows))
		{
			$rows = array();
		}
		
		$this->_rows = $rows;
		
		return $this;
	}
	
	/**
	 * Forward __call requests to each individual row
	 * 
	 * @param	string			$method			
	 * @param	array			$args
	 * 
	 * @return	$this							
	 */
	function __call($method,$args)
	{
		$this->_results = array();
		
		foreach ($this->_rows AS &$row)
		{
			$this->_results[] = call_user_func_array(array($row,$method),$args);
		}
		
		return $this;
	}
	
	function _get_method_results()
	{
		return $this->_results;
	}
	
	/**
	 * Forward __get requests to each individual row and return each result as an array entry
	 * 
	 * @param	string			$key
	 * 
	 * @return	array							
	 */
	function __get($key)
	{
		$result = array();
		
		foreach ($this->rows AS &$row)
		{
			$result[] = $row->{$key};
		}
		
		return $result;
	}
	
	/**
	 * Forward __set requests to each individual row
	 * 
	 * @param	string			$key			
	 * @param	mixed			$value
	 * 
	 * @return	$this							
	 */
	function __set($key, $value)
	{
		foreach ($this->_rows AS &$row)
		{
			if (isset($row->{$key}))
			{
				$row->{$key} = $value;
			}
		}
		
		return $this;
	}
	
	/**
	 * Retreive all rows as an array
	 * 
	 * @return	array							
	 */
	function as_array()
	{
		$array = array();
		
		foreach ($this->_rows AS $row)
		{
			$array[] = $row->as_array();
		}
		
		return $array;
	}
	
	/**
	 * Retreive all rows as json
	 * 
	 * @return	string							
	 */
	function as_json()
	{
		$result = $this->as_array();
		return json_encode($result, JSON_NUMERIC_CHECK);
	}
	
	/**
	 * Retreive all rows in their original format
	 * 
	 * @return	array							
	 */
	function get_rows()
	{
		return $this->_rows;
	}
	
	/**
	 * Count number of rows
	 * 
	 * @return	int							
	 */
	function count()
	{
		return count($this->_rows);
	}
	
}