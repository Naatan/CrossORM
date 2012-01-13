<?php

namespace CrossORM\Interfaces;

interface ORM {
	
	/**
	 * Connect to the database
	 * 
	 * @return	mixed		the returned value is used as the connection for the given database config (not standarized)
	 */
	function connect();
	
	/**
	 * Build the query to be executed (do not execute it), the query should be build
	 * inside the $this->_last_query variable
	 * 
	 * @return	$this							
	 */
	function build();
	
	/**
	 * Run the query, this method can be called without having called build,
	 * so you have to build the query if it has not been done yet.
	 *
	 * The result of the query should also be assigned to $this->_last_query_result
	 * 
	 * @return	mixed							return query result (not standarized)
	 */
	function run();
	
	/**
	 * Get a single row from the result set and move the iterator forward
	 * 
	 * @param	bool			$instantiate	if true, instantiate the row inside the ORM
	 * 
	 * @return	array|object							
	 */
	function _get_row($instantiate = false);
	
	/**
	 * Get all rows from the result set
	 * 
	 * @return	array
	 */
	function _get_rows();
	
	/**
	 * Get the last insert ID
	 * 
	 * @return	int							
	 */
	function insert_id();
	
	/**
	 * Count the number of results
	 * 
	 * @return	int							
	 */
	function count();
	
}
